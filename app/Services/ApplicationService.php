<?php

namespace App\Services;

use App\Models\{Application, Attendance, Job, Payment, Worker};
use App\Notifications\WorkflowNotification;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function apply(Job $job, Worker $worker, ?string $letter): Application
    {
        return DB::transaction(function () use ($job, $worker, $letter): Application {
            $job = Job::query()->lockForUpdate()->findOrFail($job->id);
            if ($job->status !== 'published' || $job->application_deadline->isPast()) {
                throw ValidationException::withMessages(['job' => 'Lowongan ini sudah tidak menerima lamaran.']);
            }

            $application = Application::withTrashed()->where('job_id', $job->id)->where('worker_id', $worker->id)->first();
            if ($application && !$application->trashed()) {
                throw ValidationException::withMessages(['job' => 'Kamu sudah melamar pekerjaan ini.']);
            }
            if ($application) {
                $application->restore();
                $application->update(['cover_letter' => $letter, 'status' => 'pending', 'responded_at' => null]);
                return $application;
            }
            return Application::create(['job_id' => $job->id, 'worker_id' => $worker->id, 'cover_letter' => $letter, 'status' => 'pending']);
        });
    }

    public function changeStatus(Application $application, string $status): Application
    {
        return DB::transaction(function () use ($application, $status): Application {
            $application = Application::with(['job.company.user', 'worker.user', 'attendances', 'payment'])->lockForUpdate()->findOrFail($application->id);
            $allowed = [
                'pending' => ['accepted', 'rejected', 'cancelled'],
                'accepted' => ['completed', 'cancelled'],
                'rejected' => [], 'cancelled' => [], 'completed' => [],
            ];
            if (!in_array($status, $allowed[$application->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => "Status {$application->status} tidak dapat diubah menjadi {$status}."]);
            }

            $application->update(['status' => $status, 'responded_at' => now()]);

            if ($status === 'accepted') {
                $period = CarbonPeriod::create($application->job->starts_at->toDateString(), $application->job->ends_at->toDateString());
                foreach ($period as $date) {
                    Attendance::firstOrCreate(['application_id' => $application->id, 'work_date' => $date->toDateString()], ['status' => 'scheduled']);
                }
                $application->worker->user->notify(new WorkflowNotification('Lamaran diterima', 'Kamu diterima untuk '.$application->job->title.'. Jadwal kini tersedia di My Jobs.', route('jobs.my')));
            }

            if ($status === 'rejected') {
                $quote = 'Kegagalan hari ini bukan akhir dari perjalananmu. Tetap percaya, terus belajar, dan buka kesempatan yang lebih baik berikutnya.';
                $application->worker->user->notify(new WorkflowNotification('Pembaruan lamaran', 'Lamaran untuk '.$application->job->title.' belum berhasil. “'.$quote.'”', route('applications.index')));
            }

            if ($status === 'completed' && !$application->payment) {
                $days = $application->job->duration_days;
                $subtotal = (float) $application->job->daily_rate * $days;
                Payment::create([
                    'application_id' => $application->id,
                    'invoice_number' => 'INV-'.now()->format('Ymd').'-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT),
                    'subtotal' => $subtotal,
                    'platform_fee' => 0,
                    'total' => $subtotal,
                    'status' => 'pending',
                ]);
                $application->worker->user->wallet()->increment('pending_balance', $subtotal);
                $application->worker->user->notify(new WorkflowNotification('Pekerjaan selesai', 'Invoice '.$application->job->title.' telah dibuat dan menunggu pembayaran perusahaan.', route('wallet.index')));
            }

            return $application->refresh();
        });
    }
}
