<?php

namespace App\Http\Controllers;

use App\Models\{Application, Attendance, Conversation, Message, Payment, Rating, Review, User, Wallet, WalletTransaction};
use App\Notifications\WorkflowNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class WorkflowController extends Controller
{
    public function clockIn(Request $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validate(['latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180']]);
        $this->authorizeAttendance($request, $attendance);
        DB::transaction(function () use ($attendance, $data): void {
            $attendance = Attendance::lockForUpdate()->findOrFail($attendance->id);
            if ($attendance->clock_in_at) throw ValidationException::withMessages(['attendance' => 'Kamu sudah check-in untuk shift ini.']);
            if (!$attendance->work_date->isToday()) throw ValidationException::withMessages(['attendance' => 'Check-in hanya tersedia pada tanggal shift.']);
            $attendance->update(['clock_in_at' => now(), 'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null, 'status' => 'present']);
        });
        return back()->with('success', 'Check-in berhasil dicatat.');
    }

    public function clockOut(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorizeAttendance($request, $attendance);
        DB::transaction(function () use ($attendance): void {
            $attendance = Attendance::lockForUpdate()->findOrFail($attendance->id);
            if (!$attendance->clock_in_at) throw ValidationException::withMessages(['attendance' => 'Lakukan check-in terlebih dahulu.']);
            if ($attendance->clock_out_at) throw ValidationException::withMessages(['attendance' => 'Kamu sudah check-out untuk shift ini.']);
            $attendance->update(['clock_out_at' => now(), 'status' => 'completed']);
        });
        return back()->with('success', 'Check-out berhasil dicatat.');
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:50000']]);
        DB::transaction(function () use ($request, $data): void {
            $wallet = Wallet::where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            $amount = (float) $data['amount'];
            if ((float) $wallet->balance < $amount) throw ValidationException::withMessages(['amount' => 'Saldo wallet tidak mencukupi.']);
            $wallet->decrement('balance', $amount);
            $wallet->refresh();
            WalletTransaction::create(['wallet_id' => $wallet->id, 'type' => 'debit', 'amount' => $amount, 'balance_after' => $wallet->balance, 'reference' => 'WD-'.now()->format('YmdHis').'-'.$wallet->id, 'description' => 'Penarikan saldo ke rekening terdaftar']);
        });
        return back()->with('success', 'Permintaan penarikan saldo berhasil diproses.');
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
            $senderRole = $isWorker ? 'Worker' : 'Perusahaan';
            $targetUser->notify(new WorkflowNotification(
                'Ulasan baru diterima',
                "Kamu menerima ulasan {$data['score']} bintang dari {$user->name} untuk {$application->job->title}.",
                route('reviews.index')
            ));
        }

        return back()->with('success', 'Rating dan ulasan berhasil disimpan.');
    }

    public function pay(Request $request, Payment $payment): RedirectResponse
    {
        $payment->loadMissing(['application.job.company', 'application.worker.user']);
        abort_unless($request->user()->hasRole('company') && $payment->application->job->company->user_id === $request->user()->id, 403);
        
        $data = $request->validate([
            'method' => ['required', 'in:bank_transfer,e_wallet,cash,casual_wallet']
        ]);

        DB::transaction(function () use ($payment, $data, $request): void {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'paid') {
                throw ValidationException::withMessages(['payment' => 'Invoice ini sudah dibayar.']);
            }

            if ($data['method'] === 'casual_wallet') {
                $companyWallet = Wallet::where('user_id', $request->user()->id)->lockForUpdate()->first();
                if (!$companyWallet || (float) $companyWallet->balance < (float) $payment->total) {
                    $curr = number_format($companyWallet?->balance ?? 0, 0, ',', '.');
                    throw ValidationException::withMessages([
                        'method' => "Saldo CoC Wallet perusahaan tidak mencukupi (Rp{$curr}). Silakan gunakan Transfer Bank atau E-Wallet."
                    ]);
                }
                $companyWallet->decrement('balance', $payment->total);
                $companyWallet->refresh();
                WalletTransaction::create([
                    'wallet_id' => $companyWallet->id,
                    'payment_id' => $payment->id,
                    'type' => 'debit',
                    'amount' => $payment->total,
                    'balance_after' => $companyWallet->balance,
                    'reference' => 'PAY-W-'.now()->format('YmdHis').'-'.$payment->id,
                    'description' => 'Pembayaran gaji '.$payment->application->job->title.' ke '.$payment->application->worker->user->name
                ]);
            }

            $methodName = match ($data['method']) {
                'bank_transfer' => 'Transfer Bank (VA)',
                'e_wallet' => 'E-Wallet / QRIS',
                'cash' => 'Tunai (Cash)',
                'casual_wallet' => 'CoC Wallet',
                default => 'Transfer Bank'
            };

            $ref = 'PAY-'.strtoupper($data['method']).'-'.now()->format('YmdHis').'-'.$payment->id;
            $payment->update([
                'status' => 'paid',
                'method' => $methodName,
                'transaction_reference' => $ref,
                'paid_at' => now(),
            ]);

            $workerUser = $payment->application->worker->user;
            $workerWallet = Wallet::firstOrCreate(
                ['user_id' => $workerUser->id],
                ['balance' => 0, 'pending_balance' => 0]
            );
            $workerWallet->increment('balance', $payment->total);
            $workerWallet->decrement('pending_balance', min((float) $workerWallet->pending_balance, (float) $payment->total));
            $workerWallet->refresh();

            WalletTransaction::create([
                'wallet_id' => $workerWallet->id,
                'payment_id' => $payment->id,
                'type' => 'credit',
                'amount' => $payment->total,
                'balance_after' => $workerWallet->balance,
                'reference' => $ref,
                'description' => 'Gaji diterima: '.$payment->application->job->title
            ]);

            $workerUser->notify(new WorkflowNotification(
                'Pembayaran gaji diterima',
                'Penghasilan sebesar Rp'.number_format($payment->total, 0, ',', '.').' dari '.$payment->application->job->title.' telah masuk ke wallet.',
                route('payments.show', $payment)
            ));
        });

        return back()->with('success', 'Pembayaran berhasil dan saldo worker telah diperbarui.');
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
