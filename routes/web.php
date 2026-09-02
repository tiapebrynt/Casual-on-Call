<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\MarketplacePageController;
use App\Http\Controllers\WorkerProfileController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job:slug}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/companies', [MarketplacePageController::class, 'companies'])->name('companies.index');
Route::get('/companies/{company:slug}', [MarketplacePageController::class, 'company'])->name('companies.show');
Route::get('/help', [MarketplacePageController::class, 'help'])->name('help.index');
Route::redirect('/join', '/register')->name('join');
Route::redirect('/register/worker', '/register?role=worker')->name('register.worker');
Route::redirect('/register/company', '/register?role=company')->name('register.company');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::view('/verify-email', 'auth.verify-email')->name('verification.notice');
    Route::get('/manage/jobs', [JobController::class, 'manage'])->name('jobs.manage');
    Route::post('/manage/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::put('/manage/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::delete('/manage/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}/cv', [ApplicationController::class, 'downloadCv'])->name('applications.cv.download');
    Route::get('/applications/{application}/sent', [ApplicationController::class, 'sent'])->name('applications.sent');
    Route::get('/jobs/{job:slug}/apply', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])->name('applications.store');
    Route::patch('/applications/{application}', [ApplicationController::class, 'update'])->name('applications.update');
    Route::post('/applications/{application}/review', [WorkflowController::class, 'storeReview'])->name('applications.review');
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::get('/wallet', [MarketplacePageController::class, 'wallet'])->name('wallet.index');
    Route::get('/notifications', [MarketplacePageController::class, 'notifications'])->name('notifications.index');
    Route::get('/messages', [MarketplacePageController::class, 'messages'])->name('messages.index');
    Route::get('/settings', [MarketplacePageController::class, 'settings'])->name('settings.index');
    Route::get('/reviews', [MarketplacePageController::class, 'reviews'])->name('reviews.index');
    Route::get('/payments/{payment}', [MarketplacePageController::class, 'payment'])->name('payments.show');
    Route::post('/payments/{payment}/pay', [WorkflowController::class, 'pay'])->name('payments.pay');
    Route::post('/wallet/withdraw', [WorkflowController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/notifications/read-all', [WorkflowController::class, 'readAllNotifications'])->name('notifications.read-all');
    Route::put('/settings/account', [WorkflowController::class, 'updateAccount'])->name('settings.account');
    Route::put('/settings/password', [WorkflowController::class, 'updatePassword'])->name('settings.password');
    Route::post('/applications/{application}/conversation', [WorkflowController::class, 'startConversation'])->name('conversations.start');
    Route::post('/messages/{conversation}', [WorkflowController::class, 'sendMessage'])->name('messages.store');

    Route::middleware('role:worker')->group(function (): void {
        Route::get('/my-jobs', [ApplicationController::class, 'myJobs'])->name('jobs.my');
        Route::get('/attendance', [MarketplacePageController::class, 'attendance'])->name('attendance.index');
        Route::post('/attendance/{attendance}/clock-in', [WorkflowController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/{attendance}/clock-out', [WorkflowController::class, 'clockOut'])->name('attendance.clock-out');
        Route::get('/profile', [WorkerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [WorkerProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/cv', [WorkerProfileController::class, 'download'])->name('profile.cv.download');
        Route::delete('/profile/cv', [WorkerProfileController::class, 'destroyCv'])->name('profile.cv.destroy');
    });
});
