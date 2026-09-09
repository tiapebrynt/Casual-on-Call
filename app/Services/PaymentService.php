<?php

namespace App\Services;

use App\Models\{Payment, Wallet, WalletTransaction};
use App\Notifications\WorkflowNotification;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /** Releases a confirmed payment to the worker exactly once. */
    public function markPaid(Payment $payment, string $method, string $reference): Payment
    {
        return DB::transaction(function () use ($payment, $method, $reference): Payment {
            $payment = Payment::with(['application.job', 'application.worker.user'])->lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'paid') return $payment;
            $payment->update(['status' => 'paid', 'method' => $method, 'transaction_reference' => $reference, 'paid_at' => now()]);
            $worker = $payment->application->worker->user;
            $wallet = Wallet::firstOrCreate(['user_id' => $worker->id], ['balance' => 0, 'pending_balance' => 0]);
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            $wallet->increment('balance', $payment->total);
            $wallet->decrement('pending_balance', min((float) $wallet->pending_balance, (float) $payment->total));
            $wallet->refresh();
            WalletTransaction::firstOrCreate(['payment_id' => $payment->id, 'type' => 'credit'], [
                'wallet_id' => $wallet->id, 'amount' => $payment->total, 'balance_after' => $wallet->balance,
                'reference' => $reference, 'description' => 'Gaji diterima: '.$payment->application->job->title,
            ]);
            $worker->notify(new WorkflowNotification('Pembayaran gaji diterima', 'Penghasilan sebesar Rp'.number_format($payment->total, 0, ',', '.').' dari '.$payment->application->job->title.' telah masuk ke wallet.', route('payments.show', $payment)));
            return $payment->refresh();
        });
    }
}
