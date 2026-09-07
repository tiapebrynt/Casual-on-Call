<?php

namespace App\Http\Controllers;

use App\Models\{Application, Attendance, Company, Job, Payment, Rating, User, Worker};
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            $stats = [
                'Total Pengguna' => User::count(),
                'Perusahaan Terdaftar' => Company::count(),
                'Worker Terverifikasi' => Worker::where('verification_status', 'verified')->count(),
                'Total Lowongan' => Job::count(),
                'Volume Transaksi' => 'Rp' . number_format(Payment::where('status', 'paid')->sum('total'), 0, ',', '.'),
            ];

            $recentJobs = Job::with(['company', 'category'])->latest()->limit(5)->get();
            $recentApplications = Application::with(['job.company', 'worker.user'])->latest()->limit(5)->get();
            $recentPayments = Payment::with(['application.job.company', 'application.worker.user'])->latest()->limit(5)->get();

            return view('dashboard', compact('user', 'stats', 'recentJobs', 'recentApplications', 'recentPayments'));
        }

        if ($user->hasRole('company')) {
            $company = $user->company;
            $companyId = $company->id;

            $totalJobs = Job::where('company_id', $companyId)->count();
            $activeJobs = Job::where('company_id', $companyId)->where('status', 'published')->count();
            $totalApplicants = Application::whereHas('job', fn($q) => $q->where('company_id', $companyId))->count();
            $pendingApplicants = Application::whereHas('job', fn($q) => $q->where('company_id', $companyId))->where('status', 'pending')->count();
            $totalPayroll = Payment::whereHas('application.job', fn($q) => $q->where('company_id', $companyId))->where('status', 'paid')->sum('total');
            $walletBalance = $user->wallet?->balance ?? 0;

            $stats = [
                'Lowongan Aktif' => $activeJobs . ' / ' . $totalJobs . ' Lowongan',
                'Pelamar Masuk' => $totalApplicants . ' (' . $pendingApplicants . ' baru)',
                'Total Gaji Dibayarkan' => 'Rp' . number_format($totalPayroll, 0, ',', '.'),
                'Saldo CoC Wallet' => 'Rp' . number_format($walletBalance, 0, ',', '.'),
            ];

            $recentApplications = Application::with(['job.category', 'worker.user'])
                ->whereHas('job', fn($q) => $q->where('company_id', $companyId))
                ->latest()
                ->limit(6)
                ->get();

            $companyJobs = Job::where('company_id', $companyId)
                ->with(['category'])
                ->withCount('applications')
                ->latest()
                ->limit(5)
                ->get();

            $todayAttendances = Attendance::whereHas('application.job', fn($q) => $q->where('company_id', $companyId))
                ->whereDate('work_date', now()->toDateString())
                ->with(['application.worker.user', 'application.job'])
                ->latest()
                ->limit(4)
                ->get();

            return view('dashboard', compact('user', 'company', 'stats', 'recentApplications', 'companyJobs', 'todayAttendances'));
        }

        // WORKER DASHBOARD
        $worker = $user->worker;
        if (!$worker) {
            $worker = $user->worker()->create([
                'city' => 'Jakarta',
                'verification_status' => 'verified',
            ]);
        }

        $workerId = $worker->id;
        $totalApplied = Application::where('worker_id', $workerId)->count();
        $acceptedCount = Application::where('worker_id', $workerId)->where('status', 'accepted')->count();
        $completedCount = Application::where('worker_id', $workerId)->where('status', 'completed')->count();
        $walletBalance = $user->wallet?->balance ?? 0;

        // Average rating received by worker
        $avgRating = Rating::where('reviewee_id', $user->id)->avg('score') ?: 5.0;
        $totalReviews = Rating::where('reviewee_id', $user->id)->count();

        $stats = [
            'Saldo Dompet Saya' => 'Rp' . number_format($walletBalance, 0, ',', '.'),
            'Shift Aktif' => $acceptedCount . ' Jadwal Kerja',
            'Pekerjaan Selesai' => $completedCount . ' Sukses',
            'Reputasi Rating' => number_format($avgRating, 1) . ' ⭐ (' . $totalReviews . ' ulasan)',
        ];

        // Active upcoming job
        $upcomingJob = Application::where('worker_id', $workerId)
            ->where('status', 'accepted')
            ->with(['job.company', 'job.category', 'attendances'])
            ->first();

        $recentApplications = Application::where('worker_id', $workerId)
            ->with(['job.company', 'job.category', 'payment'])
            ->latest()
            ->limit(6)
            ->get();

        $todayAttendance = Attendance::whereHas('application', fn($q) => $q->where('worker_id', $workerId))
            ->whereDate('work_date', now()->toDateString())
            ->with('application.job.company')
            ->first();

        return view('dashboard', compact('user', 'worker', 'stats', 'upcomingJob', 'recentApplications', 'todayAttendance'));
    }
}
