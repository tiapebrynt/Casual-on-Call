<?php

namespace App\Http\Controllers;

use App\Models\{Application, Job, Payment};
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function exportJobs(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['admin', 'company']), 403);
        
        $jobs = Job::with(['company', 'category'])
            ->withCount('applications')
            ->when($user->hasRole('company'), fn($q) => $q->where('company_id', $user->company->id))
            ->latest()
            ->get();

        $filename = 'Laporan-Lowongan-CoC-' . now()->format('Ymd-His') . '.csv';
        
        return response()->streamDownload(function () use ($jobs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['No', 'Judul Lowongan', 'Perusahaan', 'Kategori', 'Tipe Pembayaran', 'Upah (IDR)', 'Kuota', 'Pelamar', 'Status', 'Tanggal Mulai', 'Tanggal Selesai', 'Batas Lamaran']);
            
            foreach ($jobs as $i => $job) {
                fputcsv($handle, [
                    $i + 1,
                    $job->title,
                    $job->company->name,
                    $job->category->name,
                    $job->payment_type === 'project' ? 'Borongan / Proyek' : 'Harian',
                    $job->daily_rate,
                    $job->vacancies,
                    $job->applications_count,
                    strtoupper($job->status),
                    $job->starts_at->format('d/m/Y H:i'),
                    $job->ends_at->format('d/m/Y H:i'),
                    $job->application_deadline->format('d/m/Y H:i'),
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportApplications(Request $request): StreamedResponse
    {
        $user = $request->user();
        $isCompany = $user->hasRole('company');
        $isWorker = $user->hasRole('worker');
        
        $query = Application::with(['job.company', 'worker.user'])->latest();
        if ($isCompany) {
            $query->whereHas('job', fn($q) => $q->where('company_id', $user->company->id));
        } elseif ($isWorker) {
            $query->where('worker_id', $user->worker->id);
        }
        $applications = $query->get();

        $filename = 'Laporan-Pelamar-CoC-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($applications) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['No', 'Judul Pekerjaan', 'Perusahaan', 'Nama Pelamar', 'Email', 'No Telepon', 'Kota', 'Status', 'CV Terlampir', 'Tanggal Melamar', 'Tanggal Respon']);
            
            foreach ($applications as $i => $app) {
                fputcsv($handle, [
                    $i + 1,
                    $app->job->title,
                    $app->job->company->name,
                    $app->worker->user->name,
                    $app->worker->user->email,
                    $app->worker->user->phone ?? '-',
                    $app->worker->city ?? '-',
                    strtoupper($app->status),
                    ($app->cv_path || $app->worker->cv_path) ? 'Ya' : 'Tidak',
                    $app->created_at->format('d/m/Y H:i'),
                    $app->responded_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPayments(Request $request): StreamedResponse
    {
        $user = $request->user();
        $query = Payment::with(['application.job.company', 'application.worker.user'])->latest();
        
        if ($user->hasRole('company')) {
            $query->whereHas('application.job', fn($q) => $q->where('company_id', $user->company->id));
        } elseif ($user->hasRole('worker')) {
            $query->whereHas('application', fn($q) => $q->where('worker_id', $user->worker->id));
        }
        $payments = $query->get();

        $filename = 'Laporan-Keuangan-Payroll-CoC-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['No', 'Nomor Invoice', 'Posisi Pekerjaan', 'Perusahaan', 'Pekerja', 'Subtotal (IDR)', 'Biaya Layanan (IDR)', 'Total Gaji (IDR)', 'Metode Pembayaran', 'Kode Referensi', 'Status', 'Waktu Pelunasan']);
            
            foreach ($payments as $i => $payment) {
                fputcsv($handle, [
                    $i + 1,
                    $payment->invoice_number,
                    $payment->application->job->title,
                    $payment->application->job->company->name,
                    $payment->application->worker->user->name,
                    $payment->subtotal,
                    $payment->platform_fee,
                    $payment->total,
                    $payment->method ?? '-',
                    $payment->transaction_reference ?? '-',
                    strtoupper($payment->status),
                    $payment->paid_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
