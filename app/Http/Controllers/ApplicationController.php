<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $service) {}

    public function index(Request $request): View
    {
        $query = Application::with(['job.company', 'worker.user', 'payment']);
        if ($request->user()->hasRole('worker')) $query->where('worker_id', $request->user()->worker->id);
        elseif ($request->user()->hasRole('company')) $query->whereHas('job', fn ($job) => $job->where('company_id', $request->user()->company->id));
        $query->when($request->filled('status'), fn ($applications) => $applications->where('status', $request->string('status')->toString()));
        return view('applications.index', ['applications' => $query->latest()->paginate(10)]);
    }

    public function myJobs(Request $request): View
    {
        $worker = $request->user()->worker;
        $jobs = Application::query()
            ->with(['job.company', 'job.category', 'attendances'])
            ->where('worker_id', $worker->id)
            ->whereIn('status', ['accepted', 'completed'])
            ->whereHas('job', fn ($query) => $query->whereIn('status', ['published', 'closed', 'completed']))
            ->orderByRaw("CASE WHEN status = 'accepted' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(8);

        $activeJob = (clone $jobs->getCollection())
            ->first(fn (Application $application) => $application->status === 'accepted');
        $completedCount = Application::where('worker_id', $worker->id)->where('status', 'completed')->count();
        $totalEarnings = Application::where('worker_id', $worker->id)
            ->where('status', 'completed')
            ->with('payment:id,application_id,total,status')
            ->get()
            ->sum(fn (Application $application) => $application->payment?->status === 'paid' ? (float) $application->payment->total : 0);

        $activeDays = $activeJob ? max(0, (int) now()->startOfDay()->diffInDays($activeJob->job->starts_at->startOfDay(), false)) : 0;
        return view('jobs.my-jobs', compact('jobs', 'activeJob', 'completedCount', 'totalEarnings', 'activeDays'));
    }

    public function store(Request $request, Job $job): RedirectResponse
    {
        $request->validate(['cover_letter' => ['nullable', 'string', 'max:3000']]);
        abort_unless($request->user()->hasRole('worker'), 403);
        $application = $this->service->apply($job, $request->user()->worker, $request->input('cover_letter'));
        return redirect()->route('applications.sent', $application);
    }

    public function create(Request $request, Job $job): View
    {
        abort_unless($request->user()->hasRole('worker'), 403);
        abort_unless($job->status === 'published' && $job->application_deadline->isFuture(), 404);

        $job->load(['company', 'category', 'requirements']);
        $alreadyApplied = Application::where('job_id', $job->id)
            ->where('worker_id', $request->user()->worker->id)
            ->exists();

        return view('applications.create', compact('job', 'alreadyApplied'));
    }

    public function sent(Request $request, Application $application): View
    {
        abort_unless($request->user()->hasRole('worker') && $application->worker_id === $request->user()->worker->id, 403);
        $application->load(['job.company']);
        return view('applications.sent', compact('application'));
    }

    public function downloadCv(Request $request, Application $application): StreamedResponse
    {
        $application->loadMissing(['job', 'worker.user']);
        $user = $request->user();
        $allowed = $user->hasRole('admin') || ($user->hasRole('company') && $application->job->company_id === $user->company->id) || ($user->hasRole('worker') && $application->worker_id === $user->worker->id);
        abort_unless($allowed, 403);
        $path = $application->worker->cv_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path, 'CV-'.$application->worker->user->name.'.'.pathinfo($path, PATHINFO_EXTENSION));
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:accepted,rejected,cancelled,completed']]);
        $user = $request->user();
        abort_unless($user->hasRole('admin') || ($user->hasRole('company') && $application->job->company_id === $user->company->id) || ($user->hasRole('worker') && $data['status'] === 'cancelled' && $application->worker_id === $user->worker->id), 403);
        $this->service->changeStatus($application, $data['status']);
        return back()->with('success', 'Status lamaran diperbarui.');
    }

    public function destroy(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin') || $application->worker_id === $request->user()->worker?->id, 403);
        $application->delete();
        return back()->with('success', 'Lamaran dihapus.');
    }
}
