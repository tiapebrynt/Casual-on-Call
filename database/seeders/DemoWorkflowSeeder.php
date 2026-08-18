<?php

namespace Database\Seeders;

use App\Models\{Application,Attendance,Job,Payment,WalletTransaction,Worker};
use Illuminate\Database\Seeder;

class DemoWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $worker = Worker::with('user.wallet')->firstOrFail();
        $jobs = Job::take(2)->get();
        $accepted = Application::firstOrCreate(
            ['job_id' => $jobs[0]->id, 'worker_id' => $worker->id],
            ['cover_letter' => 'Saya siap bekerja sesuai jadwal.', 'status' => 'accepted', 'responded_at' => now()]
        );
        if ($accepted->status === 'pending') $accepted->update(['status' => 'accepted', 'responded_at' => now()]);
        Attendance::firstOrCreate(['application_id' => $accepted->id, 'work_date' => now()->addDays(10)->toDateString()], ['status' => 'scheduled']);
        $completed = Application::firstOrCreate(
            ['job_id' => $jobs[1]->id, 'worker_id' => $worker->id],
            ['cover_letter' => 'Lamaran event crew.', 'status' => 'completed', 'responded_at' => now()->subDays(3)]
        );
        $completed->update(['status' => 'completed']);
        $payment = Payment::firstOrCreate(['application_id' => $completed->id], [
            'invoice_number' => 'INV-DEMO-001', 'subtotal' => 1050000, 'platform_fee' => 52500,
            'total' => 1102500, 'status' => 'paid', 'method' => 'Bank Transfer',
            'transaction_reference' => 'TRX-DEMO-001', 'paid_at' => now()->subDay(),
        ]);
        $wallet = $worker->user->wallet;
        WalletTransaction::firstOrCreate(['reference' => 'WT-DEMO-001'], [
            'wallet_id' => $wallet->id, 'payment_id' => $payment->id, 'type' => 'credit',
            'amount' => 1050000, 'balance_after' => $wallet->balance,
            'description' => 'Pembayaran Event Crew Festival Musik',
        ]);
    }
}
