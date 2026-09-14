<?php

namespace App\Http\Controllers;

use App\Models\{Payment, User, Wallet, WalletTopUp, WalletTransaction};
use App\Notifications\WorkflowNotification;
use App\Services\{MidtransService, PaymentService};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans, PaymentService $payments): JsonResponse
    {
        $payload = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
            'payment_type' => ['nullable', 'string'],
            'fraud_status' => ['nullable', 'string'],
        ]);

        $isValidSignature = hash_equals($midtrans->expectedSignature($payload), $payload['signature_key'])
            || ($payload['signature_key'] === 'mock-signature' && $midtrans->shouldUseMock());

        if (!$isValidSignature) {
            Log::warning('Midtrans signature verification failed', ['payload' => $payload]);
            abort(403, 'Invalid signature key');
        }

        $status = $payload['transaction_status'];
        $payment = Payment::where('midtrans_order_id', $payload['order_id'])->first();

        if (!$payment) {
            $this->handleWalletTopUp($payload, $status);
            return response()->json(['status_code' => '200', 'message' => 'Success']);
        }

        $grossAmount = (int) round((float) $payload['gross_amount']);
        $paymentTotal = (int) round((float) $payment->total);

        abort_unless($grossAmount === $paymentTotal, 422, 'Amount mismatch');

        if (in_array($status, ['settlement', 'capture'], true) && ($status !== 'capture' || ($payload['fraud_status'] ?? 'accept') === 'accept')) {
            $payment->update(['midtrans_transaction_id' => $payload['transaction_id'] ?? null]);
            $payments->markPaid($payment, 'Midtrans ' . ($payload['payment_type'] ?? 'Snap'), $payload['transaction_id'] ?? $payload['order_id']);
        } elseif (in_array($status, ['deny', 'cancel', 'expire'], true) && $payment->status !== 'paid') {
            $payment->update([
                'status' => $status === 'expire' ? 'expired' : 'failed',
                'midtrans_order_id' => null,
                'midtrans_snap_token' => null,
            ]);
        }

        return response()->json(['status_code' => '200', 'message' => 'Success']);
    }

    private function handleWalletTopUp(array $payload, string $status): void
    {
        $topUp = WalletTopUp::with('user')->where('midtrans_order_id', $payload['order_id'])->firstOrFail();
        $grossAmount = (int) round((float) $payload['gross_amount']);
        $topUpAmount = (int) round((float) $topUp->amount);

        abort_unless($grossAmount === $topUpAmount, 422, 'Amount mismatch');

        if (in_array($status, ['settlement', 'capture', 'success'], true) && ($status !== 'capture' || ($payload['fraud_status'] ?? 'accept') === 'accept')) {
            DB::transaction(function () use ($topUp, $payload): void {
                $topUp = WalletTopUp::lockForUpdate()->findOrFail($topUp->id);
                if ($topUp->status === 'paid') return;

                $wallet = Wallet::lockForUpdate()->findOrFail($topUp->wallet_id);
                $wallet->increment('balance', $topUp->amount);
                $wallet->refresh();

                $reference = $payload['transaction_id'] ?? $topUp->midtrans_order_id;
                WalletTransaction::firstOrCreate(['reference' => $reference], [
                    'wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'amount' => $topUp->amount,
                    'balance_after' => $wallet->balance,
                    'description' => 'Top up CoC Wallet via Midtrans (' . strtoupper($topUp->gateway_method) . ')',
                ]);

                $topUp->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
                ]);

                $user = User::find($topUp->user_id);
                if ($user) {
                    $user->notify(new WorkflowNotification(
                        'Top Up Saldo Berhasil',
                        'Pengisian saldo dompet sebesar Rp' . number_format($topUp->amount, 0, ',', '.') . ' via Midtrans telah berhasil dan saldo Anda sudah bertambah.',
                        route('wallet.index')
                    ));
                }
            });
        } elseif (in_array($status, ['deny', 'cancel', 'expire'], true)) {
            $topUp->update(['status' => $status === 'expire' ? 'expired' : 'failed']);
        }
    }
}
