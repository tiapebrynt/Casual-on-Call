<?php

namespace App\Http\Controllers;

use App\Models\{Application, Attendance, Job, Payment};
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

        if ($isWorker) {
            $filename = 'Riwayat-Lamaran-Saya-CoC-' . now()->format('Ymd-His') . '.csv';
            return response()->streamDownload(function () use ($applications) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($handle, ['No', 'Posisi Pekerjaan', 'Perusahaan', 'Lokasi Kerja', 'Tipe Upah', 'Besaran Upah (IDR)', 'Status Lamaran', 'Tanggal Melamar', 'Tanggal Direspon']);
                
                foreach ($applications as $i => $app) {
                    fputcsv($handle, [
                        $i + 1,
                        $app->job->title,
                        $app->job->company->name,
                        $app->job->location,
                        $app->job->payment_type === 'project' ? 'Borongan / Proyek' : 'Harian',
                        $app->job->daily_rate,
                        strtoupper($app->status),
                        $app->created_at->format('d/m/Y H:i'),
                        $app->responded_at?->format('d/m/Y H:i') ?? 'Menunggu Respon',
                    ]);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Default / Company / Admin
        $filename = 'Laporan-Pelamar-Masuk-CoC-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($applications) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['No', 'Judul Lowongan', 'Nama Pelamar', 'Email Pelamar', 'No Telepon', 'Kota Domisili', 'Status Lamaran', 'CV Terlampir', 'Tanggal Melamar', 'Tanggal Respon']);
            
            foreach ($applications as $i => $app) {
                fputcsv($handle, [
                    $i + 1,
                    $app->job->title,
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
        $isCompany = $user->hasRole('company');
        $isWorker = $user->hasRole('worker');

        $query = Payment::with(['application.job.company', 'application.worker.user'])->latest();
        
        if ($isCompany) {
            $query->whereHas('application.job', fn($q) => $q->where('company_id', $user->company->id));
        } elseif ($isWorker) {
            $query->whereHas('application', fn($q) => $q->where('worker_id', $user->worker->id));
        }
        $payments = $query->get();

        if ($isWorker) {
            $filename = 'Riwayat-Penghasilan-Gaji-CoC-' . now()->format('Ymd-His') . '.csv';
            return response()->streamDownload(function () use ($payments) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($handle, ['No', 'Nomor Invoice', 'Posisi Pekerjaan', 'Pemberi Kerja / Perusahaan', 'Total Diterima (IDR)', 'Metode Pembayaran', 'Kode Referensi', 'Status Pembayaran', 'Waktu Pelunasan']);
                
                foreach ($payments as $i => $payment) {
                    fputcsv($handle, [
                        $i + 1,
                        $payment->invoice_number,
                        $payment->application->job->title,
                        $payment->application->job->company->name,
                        $payment->total,
                        $payment->method ?? '-',
                        $payment->transaction_reference ?? '-',
                        strtoupper($payment->status),
                        $payment->paid_at?->format('d/m/Y H:i') ?? 'Belum Lunas',
                    ]);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Company / Admin
        $filename = 'Laporan-Payroll-Perusahaan-CoC-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['No', 'Nomor Invoice', 'Posisi Pekerjaan', 'Nama Pekerja', 'Subtotal (IDR)', 'Biaya Layanan (IDR)', 'Total Dibayarkan (IDR)', 'Metode Pembayaran', 'Kode Referensi', 'Status', 'Waktu Pelunasan']);
            
            foreach ($payments as $i => $payment) {
                fputcsv($handle, [
                    $i + 1,
                    $payment->invoice_number,
                    $payment->application->job->title,
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

    public function exportAttendance(Request $request): StreamedResponse
    {
        $user = $request->user();
        $isCompany = $user->hasRole('company');
        $isWorker = $user->hasRole('worker');

        $query = Attendance::with(['application.job.company', 'application.worker.user'])->latest('work_date');

        if ($isCompany) {
            $query->whereHas('application.job', fn($q) => $q->where('company_id', $user->company->id));
        } elseif ($isWorker) {
            $query->whereHas('application', fn($q) => $q->where('worker_id', $user->worker->id));
        }
        $attendances = $query->get();

        if ($isWorker) {
            $filename = 'Riwayat-Presensi-Shift-CoC-' . now()->format('Ymd-His') . '.csv';
            return response()->streamDownload(function () use ($attendances) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($handle, ['No', 'Posisi Pekerjaan', 'Perusahaan', 'Lokasi', 'Tanggal Shift', 'Jam Masuk (Clock-In)', 'Jam Pulang (Clock-Out)', 'Status Kehadiran']);
                
                foreach ($attendances as $i => $att) {
                    fputcsv($handle, [
                        $i + 1,
                        $att->application->job->title,
                        $att->application->job->company->name,
                        $att->application->job->location,
                        $att->work_date->format('d/m/Y'),
                        $att->clock_in_at?->format('H:i') ?? '--:--',
                        $att->clock_out_at?->format('H:i') ?? '--:--',
                        strtoupper($att->status),
                    ]);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Company / Admin
        $filename = 'Laporan-Presensi-Pekerja-CoC-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['No', 'Nama Pekerja', 'Posisi Pekerjaan', 'Tanggal Shift', 'Jam Masuk (Clock-In)', 'Jam Pulang (Clock-Out)', 'Status Kehadiran']);
            
            foreach ($attendances as $i => $att) {
                fputcsv($handle, [
                    $i + 1,
                    $att->application->worker->user->name,
                    $att->application->job->title,
                    $att->work_date->format('d/m/Y'),
                    $att->clock_in_at?->format('H:i') ?? '--:--',
                    $att->clock_out_at?->format('H:i') ?? '--:--',
                    strtoupper($att->status),
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}