<?php

namespace App\Http\Controllers;

use App\Models\{Application, Attendance, Conversation, Message, Payment, Rating, Review, User, Wallet, WalletTopUp, WalletTransaction, WithdrawalRequest};
use App\Notifications\WorkflowNotification;
use App\Services\{MidtransService, PaymentService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    public function clockIn(Request $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validate(['latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180']]);
        $this->authorizeAttendance($request, $attendance);
        DB::transaction(function () use ($attendance, $data): void {
            $attendance = Attendance::lockForUpdate()->findOrFail($attendance->id);
            if ($attendance->clock_in_at) throw ValidationException::withMessages(['attendance' => 'Kamu sudah check-in untuk shift ini.']);
            $attendance->update(['clock_in_at' => now(), 'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null, 'status' => 'present']);
        });
        return back()->with('success', 'Check-in berhasil dicatat! Status kehadiranmu sekarang hadir (PRESENT).');
    }

    public function clockOut(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorizeAttendance($request, $attendance);
        DB::transaction(function () use ($attendance): void {
            $attendance = Attendance::lockForUpdate()->findOrFail($attendance->id);
            if (!$attendance->clock_in_at) throw ValidationException::withMessages(['attendance' => 'Lakukan check-in terlebih dahulu sebelum check-out.']);
            if ($attendance->clock_out_at) throw ValidationException::withMessages(['attendance' => 'Kamu sudah check-out untuk shift ini.']);
            $attendance->update(['clock_out_at' => now(), 'status' => 'completed']);
        });
        return back()->with('success', 'Check-out berhasil dicatat! Shift kerja telah diselesaikan.');
    }

    /**
     * Initiate Midtrans Snap Top-Up.
     */
    public function topupWallet(Request $request, MidtransService $midtrans): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:50000', 'max:100000000'],
            'gateway_method' => ['required', 'string', 'in:bca_va,mandiri_va,bri_va,bni_va,qris,gopay'],
        ]);

        $user = $request->user();
        abort_unless($user->hasAnyRole(['company', 'admin', 'worker']), 403);

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'pending_balance' => 0]);
        $orderId = 'TOPUP-' . now()->format('YmdHis') . '-' . strtoupper($data['gateway_method']) . '-' . random_int(1000, 9999);

        $topUp = WalletTopUp::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $data['amount'],
            'gateway_method' => $data['gateway_method'],
            'midtrans_order_id' => $orderId,
        ]);

        $checkoutUrl = $midtrans->createWalletTopUpCheckoutUrl($topUp);
        return redirect()->away($checkoutUrl);
    }

    /**
     * Local Sandbox Payment Simulator for Top-Up.
     */
    public function topupSimulator(Request $request, WalletTopUp $topUp): View
    {
        abort_unless($request->user()->id === $topUp->user_id || $request->user()->hasRole('admin'), 403);
        return view('wallet.simulator', compact('topUp'));
    }

    /**
     * Midtrans Snap Finish / Callback for Top-Up.
     */
    public function topupFinish(Request $request, MidtransService $midtrans): RedirectResponse
    {
        $orderId = $request->get('order_id');
        if (!$orderId) {
            return redirect()->route('wallet.index')->with('success', 'Proses pembayaran selesai. Saldo Anda akan segera terupdate.');
        }

        $topUp = WalletTopUp::where('midtrans_order_id', $orderId)->first();
        if (!$topUp) {
            return redirect()->route('wallet.index')->with('info', 'Transaksi tidak ditemukan.');
        }

        // Check if coming from simulator or real Midtrans
        $status = $request->get('transaction_status', 'settlement');
        $statusCode = $request->get('status_code', '200');

        if (in_array($status, ['settlement', 'capture', '200', 'success'], true) || $statusCode == '200') {
            DB::transaction(function () use ($topUp, $request, $orderId): void {
                $topUp = WalletTopUp::lockForUpdate()->findOrFail($topUp->id);
                if ($topUp->status === 'paid') return;

                $wallet = Wallet::lockForUpdate()->findOrFail($topUp->wallet_id);
                $wallet->increment('balance', $topUp->amount);
                $wallet->refresh();

                $reference = $request->get('transaction_id') ?: ('MDT-' . $orderId);
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
                    'midtrans_transaction_id' => $reference,
                ]);

                $user = User::find($topUp->user_id);
                if ($user) {
                    $user->notify(new WorkflowNotification(
                        'Top Up Saldo Berhasil',
                        'Pengisian saldo dompet sebesar Rp' . number_format($topUp->amount, 0, ',', '.') . ' via Midtrans telah berhasil.',
                        route('wallet.index')
                    ));
                }
            });

            return redirect()->route('wallet.index')->with('success', 'Top up sebesar Rp' . number_format($topUp->amount, 0, ',', '.') . ' berhasil! Saldo telah ditambahkan ke dompet.');
        }

        return redirect()->route('wallet.index')->with('info', 'Transaksi top up berstatus: ' . strtoupper($status));
    }

    /**
     * Local Sandbox Payment Simulator for Invoice Payment.
     */
    public function paymentSimulator(Request $request, Payment $payment): View
    {
        $payment->loadMissing(['application.job.company.user', 'application.worker.user']);
        $user = $request->user();
        abort_unless($user->hasRole('admin') || $payment->application->job->company->user_id === $user->id, 403);

        return view('payments.simulator', compact('payment'));
    }

    /**
     * Midtrans Snap Finish / Callback for Invoice Payment.
     */
    public function paymentFinish(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $payment->loadMissing(['application.job.company', 'application.worker.user']);

        if ($payment->status !== 'paid') {
            $reference = $request->get('transaction_id') ?: ('MDT-' . ($payment->midtrans_order_id ?: $payment->invoice_number));
            $payments->markPaid($payment, 'Midtrans Snap', $reference);
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Pembayaran via Midtrans berhasil diselesaikan dan upah telah diteruskan ke dompet worker!');
    }

    /**
     * Withdraw / Pencairan Dana — langsung diproses otomatis tanpa persetujuan admin.
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:50000'],
            'bank_name'      => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $data): void {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $wallet) {
                throw ValidationException::withMessages(['amount' => 'Wallet tidak ditemukan.']);
            }

            // Hitung saldo tersedia (kurangi pending withdrawal lain)
            $pendingAmount    = WithdrawalRequest::where('wallet_id', $wallet->id)->where('status', 'pending')->sum('amount');
            $availableBalance = (float) $wallet->balance - (float) $pendingAmount;

            if ($availableBalance < (float) $data['amount']) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo siap tarik tidak mencukupi (Tersedia: Rp' . number_format($availableBalance, 0, ',', '.') . ').',
                ]);
            }

            // Potong saldo langsung
            $wallet->decrement('balance', $data['amount']);
            $wallet->refresh();

            $refCode = 'WD-AUTO-' . $user->id . '-' . now()->format('YmdHis');

            // Catat mutasi wallet
            WalletTransaction::create([
                'wallet_id'     => $wallet->id,
                'type'          => 'debit',
                'amount'        => $data['amount'],
                'balance_after' => $wallet->balance,
                'reference'     => $refCode,
                'description'   => "Penarikan dana ke {$data['bank_name']} ({$data['account_number']})",
            ]);

            // Simpan record withdrawal sebagai approved langsung
            WithdrawalRequest::create([
                'user_id'        => $user->id,
                'wallet_id'      => $wallet->id,
                'amount'         => $data['amount'],
                'bank_name'      => $data['bank_name'],
                'account_number' => $data['account_number'],
                'account_holder' => $data['account_holder'],
                'status'         => 'approved',
                'admin_note'     => 'Diproses otomatis — dana sedang dikirim ke rekening tujuan.',
                'processed_at'   => now(),
            ]);

            // Notifikasi ke user
            $user->notify(new WorkflowNotification(
                'Pencairan Dana Berhasil',
                'Penarikan saldo sebesar Rp' . number_format($data['amount'], 0, ',', '.') . " ke rekening {$data['bank_name']} ({$data['account_number']}) sedang diproses & akan tiba dalam 1–5 menit.",
                route('wallet.index')
            ));
        });

        return back()->with('success', 'Penarikan dana sebesar Rp' . number_format($data['amount'], 0, ',', '.') . ' berhasil diproses dan sedang dikirim ke rekening Anda.');
    }

    /**
     * Admin approves withdrawal request (Supports Automated Midtrans Payout / Iris or Manual Transfer).
     */
    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawal, MidtransService $midtrans): RedirectResponse
    {
        $mode = $request->input('mode', 'midtrans'); // 'midtrans' or 'manual'

        DB::transaction(function () use ($request, $withdrawal, $midtrans, $mode): void {
            $withdrawal = WithdrawalRequest::lockForUpdate()->findOrFail($withdrawal->id);
            if ($withdrawal->status !== 'pending') {
                throw ValidationException::withMessages(['withdrawal' => 'Permintaan penarikan ini sudah diproses sebelumnya.']);
            }

            $wallet = Wallet::lockForUpdate()->findOrFail($withdrawal->wallet_id);
            if ((float) $wallet->balance < (float) $withdrawal->amount) {
                throw ValidationException::withMessages(['withdrawal' => 'Saldo pengguna tidak lagi mencukupi untuk disetujui.']);
            }

            // Execute Midtrans Iris Payout if requested
            $payoutResult = null;
            if ($mode === 'midtrans') {
                $payoutResult = $midtrans->createPayout($withdrawal);
            }

            $wallet->decrement('balance', $withdrawal->amount);
            $wallet->refresh();

            $refCode = $payoutResult['reference_no'] ?? ('WD-TF-' . $withdrawal->id . '-' . now()->format('His'));
            $descMethod = $mode === 'midtrans' ? 'Midtrans Iris Payout' : 'Manual Transfer Bank';

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $withdrawal->amount,
                'balance_after' => $wallet->balance,
                'reference' => $refCode,
                'description' => "Penarikan dana via {$descMethod} ke {$withdrawal->bank_name} ({$withdrawal->account_number})",
            ]);

            $withdrawal->update([
                'status' => 'approved',
                'admin_note' => $mode === 'midtrans' ? ($payoutResult['message'] ?? 'Diproses via Midtrans Iris') : 'Disetujui via Transfer Bank Manual',
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ]);

            // Notify user
            $user = User::find($withdrawal->user_id);
            if ($user) {
                $user->notify(new WorkflowNotification(
                    'Pencairan Dana Berhasil',
                    'Permintaan penarikan saldo sebesar Rp' . number_format($withdrawal->amount, 0, ',', '.') . " ke rekening {$withdrawal->bank_name} telah disetujui & ditransfer.",
                    route('wallet.index')
                ));
            }
        });

        return back()->with('success', 'Pencairan dana berhasil disetujui dan saldo user telah dipotong.');
    }

    /**
     * Admin rejects withdrawal request.
     */
    public function rejectWithdrawal(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);
        abort_unless($withdrawal->status === 'pending', 422);

        $withdrawal->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?: 'Permintaan penarikan ditolak oleh admin.',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        $user = User::find($withdrawal->user_id);
        if ($user) {
            $user->notify(new WorkflowNotification(
                'Permintaan Pencairan Ditolak',
                'Penarikan saldo sebesar Rp' . number_format($withdrawal->amount, 0, ',', '.') . ' ditolak: ' . ($data['admin_note'] ?: 'Saldo Anda tetap utuh.'),
                route('wallet.index')
            ));
        }

        return back()->with('success', 'Permintaan pencairan telah ditolak; saldo pengguna tetap utuh.');
    }

    public function readAllNotifications(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'phone' => ['nullable', 'string', 'max:30']]);
        $request->user()->update($data);
        return back()->with('success', 'Informasi akun berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()]]);
        if (!Hash::check($data['current_password'], $request->user()->password)) throw ValidationException::withMessages(['current_password' => 'Password saat ini tidak sesuai.']);
        $request->user()->update(['password' => $data['password']]);
        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function startConversation(Request $request, Application $application): RedirectResponse
    {
        $application->loadMissing(['job.company', 'worker']);
        $user = $request->user();
        abort_unless($user->hasRole('admin') || $application->worker->user_id === $user->id || $application->job->company->user_id === $user->id, 403);
        $conversation = Conversation::firstOrCreate(['company_id' => $application->job->company_id, 'worker_id' => $application->worker_id, 'application_id' => $application->id]);
        return redirect()->route('messages.index', ['conversation' => $conversation->id]);
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($request, $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);
        DB::transaction(function () use ($request, $conversation, $data): void {
            Message::create(['conversation_id' => $conversation->id, 'sender_id' => $request->user()->id, 'body' => $data['body']]);
            $conversation->update(['last_message_at' => now()]);
        });
        return redirect()->route('messages.index', ['conversation' => $conversation->id])->with('success', 'Pesan terkirim.');
    }

    public function storeReview(Request $request, Application $application): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('worker') || $user->hasRole('company') || $user->hasRole('admin'), 403);
        abort_unless($application->status === 'completed', 403);
        $application->loadMissing(['job.company', 'worker.user']);
        
        $isWorker = $user->hasRole('worker') && $application->worker->user_id === $user->id;
        $isCompany = $user->hasRole('company') && $application->job->company->user_id === $user->id;
        $isAdmin = $user->hasRole('admin');
        abort_unless($isWorker || $isCompany || $isAdmin, 403);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $revieweeId = $isWorker 
            ? $application->job->company->user_id 
            : $application->worker->user_id;

        $rating = Rating::updateOrCreate(
            ['application_id' => $application->id, 'reviewer_id' => $user->id],
            ['reviewee_id' => $revieweeId, 'score' => $data['score']]
        );

        Review::updateOrCreate(
            ['rating_id' => $rating->id],
            ['title' => $data['title'] ?? null, 'body' => $data['body'], 'is_visible' => true]
        );

        $targetUser = User::find($revieweeId);
        if ($targetUser) {
            $targetUser->notify(new WorkflowNotification(
                'Ulasan baru diterima',
                "Kamu menerima ulasan {$data['score']} bintang dari {$user->name} untuk {$application->job->title}.",
                route('reviews.index')
            ));
        }

        return back()->with('success', 'Rating dan ulasan berhasil disimpan.');
    }

    public function pay(Request $request, Payment $payment, MidtransService $midtrans, PaymentService $payments): RedirectResponse
    {
        $payment->loadMissing(['application.job.company', 'application.worker.user']);
        abort_unless($request->user()->hasRole('company') && $payment->application->job->company->user_id === $request->user()->id, 403);
        
        $data = $request->validate([
            'method' => ['required', 'in:midtrans,casual_wallet']
        ]);
        if ($payment->status === 'paid') throw ValidationException::withMessages(['payment' => 'Invoice ini sudah dibayar.']);

        if ($data['method'] === 'midtrans') {
            $checkoutUrl = $midtrans->createSnapCheckoutUrl($payment);
            return redirect()->away($checkoutUrl);
        }

        DB::transaction(function () use ($payment, $request, $payments): void {
            $companyWallet = Wallet::where('user_id', $request->user()->id)->lockForUpdate()->first();
            if (!$companyWallet || (float) $companyWallet->balance < (float) $payment->total) {
                $curr = number_format($companyWallet?->balance ?? 0, 0, ',', '.');
                throw ValidationException::withMessages(['method' => "Saldo CoC Wallet perusahaan tidak mencukupi (Rp{$curr})."]);
            }
            $companyWallet->decrement('balance', $payment->total);
            $companyWallet->refresh();
            $reference = 'PAY-W-' . now()->format('YmdHis') . '-' . $payment->id;
            WalletTransaction::create(['wallet_id' => $companyWallet->id, 'payment_id' => $payment->id, 'type' => 'debit', 'amount' => $payment->total, 'balance_after' => $companyWallet->balance, 'reference' => $reference, 'description' => 'Pembayaran gaji ' . $payment->application->job->title]);
            $payments->markPaid($payment, 'CoC Wallet', $reference);
        });
        return back()->with('success', 'Pembayaran wallet berhasil dan saldo worker telah diperbarui.');
    }

    private function authorizeAttendance(Request $request, Attendance $attendance): void
    {
        $attendance->loadMissing('application.worker');
        abort_unless($request->user()->hasRole('worker') && $attendance->application->worker->user_id === $request->user()->id, 403);
    }

    private function authorizeConversation(Request $request, Conversation $conversation): void
    {
        $conversation->loadMissing(['company', 'worker']);
        abort_unless($conversation->company->user_id === $request->user()->id || $conversation->worker->user_id === $request->user()->id, 403);
    }
}
