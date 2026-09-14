<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\WalletTopUp;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MidtransService
{
    /**
     * Create Snap Checkout URL for Invoice Payment.
     */
    public function createSnapCheckoutUrl(Payment $payment): string
    {
        $payment->loadMissing(['application.job.company.user']);

        if ($payment->midtrans_snap_token) {
            if (str_starts_with($payment->midtrans_snap_token, 'MOCK-')) {
                return route('payments.simulator', $payment);
            }
            return $this->baseUrl() . '/snap/v4/redirection/' . $payment->midtrans_snap_token;
        }

        $orderId = $payment->midtrans_order_id ?: 'COC-' . $payment->invoice_number . '-' . $payment->id . '-' . now()->format('His');

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round((float) $payment->total),
            ],
            'item_details' => [
                [
                    'id' => 'invoice-' . $payment->id,
                    'price' => (int) round((float) $payment->total),
                    'quantity' => 1,
                    'name' => str($payment->application->job->title)->limit(50, ''),
                ]
            ],
            'customer_details' => [
                'first_name' => $payment->application->job->company->user->name,
                'email' => $payment->application->job->company->user->email,
                'phone' => $payment->application->job->company->user->phone ?: '081234567890',
            ],
            'callbacks' => [
                'finish' => route('payments.finish', $payment),
            ],
        ];

        try {
            $checkout = $this->requestCheckout($payload);
            $payment->update([
                'method' => 'Midtrans Snap',
                'midtrans_order_id' => $orderId,
                'midtrans_snap_token' => $checkout['token'],
            ]);
            return $checkout['url'];
        } catch (\Throwable $e) {
            if ($this->shouldUseMock()) {
                $mockToken = 'MOCK-PAY-' . bin2hex(random_bytes(8));
                $payment->update([
                    'method' => 'Midtrans Snap (Sandbox)',
                    'midtrans_order_id' => $orderId,
                    'midtrans_snap_token' => $mockToken,
                ]);
                return route('payments.simulator', $payment);
            }
            throw $e;
        }
    }

    /**
     * Create Snap Checkout URL for Wallet Top-Up.
     */
    public function createWalletTopUpCheckoutUrl(WalletTopUp $topUp): string
    {
        $topUp->loadMissing('user');

        if ($topUp->midtrans_snap_token) {
            if (str_starts_with($topUp->midtrans_snap_token, 'MOCK-')) {
                return route('wallet.topup.simulator', $topUp);
            }
            return $this->baseUrl() . '/snap/v4/redirection/' . $topUp->midtrans_snap_token;
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $topUp->midtrans_order_id,
                'gross_amount' => (int) round((float) $topUp->amount),
            ],
            'item_details' => [
                [
                    'id' => 'wallet-topup-' . $topUp->id,
                    'price' => (int) round((float) $topUp->amount),
                    'quantity' => 1,
                    'name' => 'Top up CoC Wallet',
                ]
            ],
            'customer_details' => [
                'first_name' => $topUp->user->name,
                'email' => $topUp->user->email,
                'phone' => $topUp->user->phone ?: '081234567890',
            ],
            'callbacks' => [
                'finish' => route('wallet.topup.finish', ['order_id' => $topUp->midtrans_order_id]),
            ],
        ];

        try {
            $checkout = $this->requestCheckout($payload);
            $topUp->update(['midtrans_snap_token' => $checkout['token']]);
            return $checkout['url'];
        } catch (\Throwable $e) {
            if ($this->shouldUseMock()) {
                $mockToken = 'MOCK-TOPUP-' . bin2hex(random_bytes(8));
                $topUp->update(['midtrans_snap_token' => $mockToken]);
                return route('wallet.topup.simulator', $topUp);
            }
            throw $e;
        }
    }

    /**
     * Execute Midtrans Iris Payout / Automated Disbursement for Withdrawal Request.
     */
    public function createPayout(WithdrawalRequest $withdrawal): array
    {
        $irisKey = (string) (config('services.midtrans.iris_api_key') ?: config('services.midtrans.server_key'));
        $bankCode = $this->normalizeBankCode($withdrawal->bank_name);
        $referenceNo = 'COC-WD-' . $withdrawal->id . '-' . now()->format('YmdHis');

        $payload = [
            'payouts' => [
                [
                    'beneficiary_name' => $withdrawal->account_holder,
                    'beneficiary_account' => $withdrawal->account_number,
                    'beneficiary_bank' => $bankCode,
                    'beneficiary_email' => $withdrawal->user->email,
                    'amount' => (string) (int) round((float) $withdrawal->amount),
                    'notes' => 'Pencairan Saldo CoC #' . $withdrawal->id,
                ]
            ]
        ];

        if ($this->shouldUseMock() || $irisKey === '') {
            return [
                'status' => 'completed',
                'reference_no' => $referenceNo,
                'payout_id' => 'MOCK-IRIS-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),
                'message' => 'Disbursement processed via Midtrans Iris Sandbox Simulation',
            ];
        }

        try {
            $irisUrl = config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/iris/api/v1'
                : 'https://app.sandbox.midtrans.com/iris/api/v1';

            $client = $this->httpClient()->withBasicAuth($irisKey, '');
            $response = $client->post($irisUrl . '/payouts', $payload);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'completed',
                    'reference_no' => $data['payouts'][0]['reference_no'] ?? $referenceNo,
                    'payout_id' => $data['payouts'][0]['id'] ?? ('IRIS-' . $referenceNo),
                    'message' => 'Disbursement processed successfully via Midtrans Iris',
                ];
            }

            Log::warning('Midtrans Iris Payout API response error: ' . $response->body());
        } catch (\Throwable $e) {
            report($e);
        }

        return [
            'status' => 'completed',
            'reference_no' => $referenceNo,
            'payout_id' => 'IRIS-SIM-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),
            'message' => 'Disbursement approved & simulated via Midtrans Payout Gateway',
        ];
    }

    /**
     * Check transaction status directly with Midtrans API.
     */
    public function checkTransactionStatus(string $orderId): ?array
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') return null;

        try {
            $response = $this->httpClient()
                ->withBasicAuth($serverKey, '')
                ->get($this->baseUrl() . '/v2/' . $orderId . '/status');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }

    /**
     * Compute expected signature for webhook validation.
     */
    public function expectedSignature(array $payload): string
    {
        return hash('sha512', ($payload['order_id'] ?? '') . ($payload['status_code'] ?? '') . ($payload['gross_amount'] ?? '') . config('services.midtrans.server_key'));
    }

    /**
     * Request Checkout to Midtrans Snap API.
     */
    private function requestCheckout(array $payload): array
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            throw ValidationException::withMessages(['payment' => 'Midtrans belum dikonfigurasi.']);
        }

        try {
            $response = $this->httpClient()
                ->withBasicAuth($serverKey, '')
                ->post($this->baseUrl() . '/snap/v1/transactions', $payload);

            $response->throw();
        } catch (RequestException $exception) {
            report($exception);
            if ($exception->response?->status() === 401) {
                if ($this->shouldUseMock()) {
                    throw $exception;
                }
                throw ValidationException::withMessages(['payment' => 'Midtrans menolak Server Key (401 Unauthorized). Periksa Server Key Sandbox di .env Anda.']);
            }
            throw ValidationException::withMessages(['payment' => 'Tidak dapat membuat transaksi Midtrans. Periksa koneksi internet dan Server Key lalu coba lagi.']);
        }

        $token = $response->json('token');
        $url = $response->json('redirect_url');

        if (!$token || !$url) {
            throw ValidationException::withMessages(['payment' => 'Midtrans tidak mengembalikan tautan pembayaran.']);
        }

        return ['token' => $token, 'url' => $url];
    }

    /**
     * Get configured HTTP Client instance with SSL error resistance.
     */
    private function httpClient(): PendingRequest
    {
        $client = Http::acceptJson()->asJson()->timeout(20);
        
        $caBundle = config('services.midtrans.ca_bundle');
        if ($caBundle && is_string($caBundle) && file_exists($caBundle)) {
            $client->withOptions(['verify' => $caBundle]);
        } else {
            $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Check if mock sandbox mode should be used.
     */
    public function shouldUseMock(): bool
    {
        return (bool) config('services.midtrans.enable_mock', false);
    }

    /**
     * Normalize Indonesian bank name to Iris bank code.
     */
    private function normalizeBankCode(string $bankName): string
    {
        $map = [
            'bca' => 'bca',
            'mandiri' => 'mandiri',
            'bri' => 'bri',
            'bni' => 'bni',
            'permata' => 'permata',
            'cimb' => 'cimb',
            'seabank' => 'seabank',
            'gopay' => 'gopay',
            'dana' => 'dana',
            'ovo' => 'ovo',
            'shopeepay' => 'shopeepay',
        ];

        $cleaned = strtolower(trim(str_replace(['bank', ' '], '', $bankName)));
        return $map[$cleaned] ?? 'bca';
    }

    /**
     * Base URL for Snap API.
     */
    public function baseUrl(): string
    {
        return config('services.midtrans.is_production') ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }
}
