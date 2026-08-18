<?php

namespace App\Http\Controllers;

use App\Models\{Application,Company,Conversation,Job,Payment,Rating,Review,WalletTransaction};
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplacePageController extends Controller
{
    public function companies(Request $request): View
    {
        $companies = Company::withCount(['jobs' => fn ($query) => $query->where('status', 'published')])
            ->where('verification_status', 'verified')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->latest()->paginate(12)->withQueryString();
        return view('companies.index', compact('companies'));
    }

    public function company(Company $company): View
    {
        $company->load(['user', 'jobs' => fn ($query) => $query->where('status', 'published')->latest()->limit(4)])->loadCount('jobs');
        return view('companies.show', compact('company'));
    }

    public function wallet(Request $request): View
    {
        $wallet = $request->user()->wallet()->with(['transactions' => fn ($query) => $query->latest()->limit(10)])->firstOrFail();
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)->latest()->paginate(10);
        return view('wallet.index', compact('wallet', 'transactions'));
    }

    public function attendance(Request $request): View
    {
        $workerId = $request->user()->worker->id;
        $applications = Application::with(['job.company', 'attendances' => fn ($query) => $query->latest('work_date')])
            ->where('worker_id', $workerId)->whereIn('status', ['accepted', 'completed'])->latest()->paginate(8);
        return view('attendance.index', compact('applications'));
    }

    public function notifications(Request $request): View
    {
        return view('notifications.index', ['notifications' => $request->user()->notifications()->paginate(15)]);
    }

    public function messages(Request $request): View
    {
        $user = $request->user();
        $conversations = Conversation::with(['company.user', 'worker.user', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->when($user->hasRole('worker'), fn ($query) => $query->where('worker_id', $user->worker->id))
            ->when($user->hasRole('company'), fn ($query) => $query->where('company_id', $user->company->id))
            ->latest('last_message_at')->paginate(12);
        $selected = null;
        if ($request->filled('conversation')) {
            $selected = Conversation::with(['company.user', 'worker.user', 'application.job', 'messages.sender'])->findOrFail($request->integer('conversation'));
            $allowed = ($user->hasRole('worker') && $selected->worker_id === $user->worker->id)
                || ($user->hasRole('company') && $selected->company_id === $user->company->id)
                || $user->hasRole('admin');
            abort_unless($allowed, 403);
            $selected->messages()->whereNull('read_at')->where('sender_id', '!=', $user->id)->update(['read_at' => now()]);
        }
        return view('messages.index', compact('conversations', 'selected'));
    }

    public function settings(Request $request): View { return view('settings.index', ['user' => $request->user()]); }
    public function help(): View { return view('help.index'); }

    public function reviews(Request $request): View
    {
        $reviews = Rating::with(['review', 'reviewer', 'application.job'])->where('reviewee_id', $request->user()->id)->latest()->paginate(10);
        $average = round((float) Rating::where('reviewee_id', $request->user()->id)->avg('score'), 1);
        return view('reviews.index', compact('reviews', 'average'));
    }

    public function payment(Request $request, Payment $payment): View
    {
        $payment->load(['application.job.company', 'application.worker.user']);
        $user = $request->user();
        abort_unless($user->hasRole('admin') || $payment->application->worker->user_id === $user->id || $payment->application->job->company->user_id === $user->id, 403);
        return view('payments.show', compact('payment'));
    }
}
