<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MidtransService
{
    public function createSnapToken(Payment $payment): string
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            throw ValidationException::withMessages(['payment' => 'Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY terlebih dahulu.']);
        }
        if ($payment->midtrans_snap_token) return $payment->midtrans_snap_token;

        $baseUrl = config('services.midtrans.is_production') ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
        $orderId = $payment->midtrans_order_id ?: 'COC-'.$payment->invoice_number.'-'.$payment->id.'-'.now()->format('His');
        $payment->loadMissing(['application.job.company.user']);
        try {
            $response = Http::acceptJson()->asJson()->withBasicAuth($serverKey, '')->timeout(20)
                ->post($baseUrl.'/snap/v1/transactions', [
                    'transaction_details' => ['order_id' => $orderId, 'gross_amount' => (int) round((float) $payment->total)],
                    'item_details' => [[
                        'id' => 'invoice-'.$payment->id, 'price' => (int) round((float) $payment->total),
                        'quantity' => 1, 'name' => str($payment->application->job->title)->limit(50, ''),
                    ]],
                    'customer_details' => [
                        'first_name' => $payment->application->job->company->user->name,
                        'email' => $payment->application->job->company->user->email,
                        'phone' => $payment->application->job->company->user->phone,
                    ],
                ]);
            $response->throw();
        } catch (RequestException $exception) {
            report($exception);
            throw ValidationException::withMessages(['payment' => 'Tidak dapat membuat transaksi Midtrans. Periksa Server Key dan koneksi internet, lalu coba lagi.']);
        }
        $token = $response->json('token');
        if (!$token) throw ValidationException::withMessages(['payment' => 'Midtrans tidak mengembalikan token pembayaran.']);
        $payment->update(['method' => 'Midtrans Snap', 'midtrans_order_id' => $orderId, 'midtrans_snap_token' => $token]);
        return $token;
    }

    public function expectedSignature(array $payload): string
    {
        return hash('sha512', ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').config('services.midtrans.server_key'));
    }
}
