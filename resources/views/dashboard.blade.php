@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<section class="bg-gradient-to-br from-[#fff7f8] via-surface to-[#eaffff] border-b border-black/5">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10 lg:py-12">
        <div class="flex flex-col justify-between gap-6 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="badge uppercase tracking-wider !text-xs font-bold !bg-primary-soft !text-primary">
                        {{ strtoupper($user->getRoleNames()->first() ?? 'MEMBER') }} PORTAL
                    </span>
                    <span class="text-xs text-on-surface-variant">&bull; {{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <h1 class="mt-1.5 font-display text-2xl sm:text-3xl font-bold tracking-tight text-secondary">
                    Selamat Datang, {{ explode(' ', $user->name)[0] }}! 👋
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-on-surface-variant">
                    @if($user->hasRole('company'))
                        Kelola seluruh proses rekrutmen pekerja harian, kehadiran shift, dan payroll perusahaanmu.
                    @elseif($user->hasRole('worker'))
                        Pantau jadwal shift kerja, presensi harian, pendapatan gaji, dan reputasi akunmu.
                    @else
                        Pantau seluruh aktivitas transaksi, moderasi lowongan, dan verifikasi anggota platform.
                    @endif
                </p>
            </div>

            <!-- ACTION BUTTONS & EXPORT -->
            <div class="flex flex-wrap items-center gap-2.5">
                @if($user->hasRole('company'))
                    <a href="{{ route('jobs.manage') }}" class="btn-primary compact">
                        <x-icon name="plus" class="size-4" />
                        <span>Posting Lowongan</span>
                    </a>
                @elseif($user->hasRole('worker'))
                    <a href="{{ route('jobs.index') }}" class="btn-primary compact">
                        <x-icon name="search" class="size-4" />
                        <span>Cari Lowongan</span>
                    </a>
                @endif

                <!-- EXPORT LAPORAN BUTTON / DROPDOWN -->
                <div class="relative inline-block" id="export-dropdown-wrapper">
                    <button type="button" onclick="document.getElementById('export-menu').classList.toggle('hidden')" class="btn-ghost compact !bg-white !border !border-black/10 shadow-sm" title="Export Laporan ke CSV">
                        <x-icon name="description" class="size-4 text-primary" />
                        <span>Export Laporan</span>
                        <x-icon name="keyboard_arrow_down" class="size-3.5" />
                    </button>
                    <div id="export-menu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-white p-2 shadow-2xl border border-black/10 z-40">
                        <p class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant border-b border-black/5">Pilihan Format CSV / Excel</p>
                        @if($user->hasRole('worker'))
                            <a href="{{ route('reports.export.applications') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-surface-low text-secondary">
                                <x-icon name="description" class="size-4 text-emerald-600" />
                                <span>Export Riwayat Lamaran</span>
                            </a>
                            <a href="{{ route('reports.export.payments') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-surface-low text-secondary">
                                <x-icon name="wallet" class="size-4 text-amber-600" />
                                <span>Export Riwayat Penghasilan</span>
                            </a>
                            <a href="{{ route('reports.export.attendance') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-surface-low text-secondary">
                                <x-icon name="calendar" class="size-4 text-blue-600" />
                                <span>Export Riwayat Presensi</span>
                            </a>
                        @else
                            @if($user->hasAnyRole(['admin', 'company']))
                                <a href="{{ route('reports.export.jobs') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-surface-low text-secondary">
                                    <x-icon name="work" class="size-4 text-primary" />
                                    <span>Export Data Lowongan</span>
                                </a>
                            @endif
                            <a href="{{ route('reports.export.applications') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-surface-low text-secondary">
                                <x-icon name="description" class="size-4 text-emerald-600" />
                                <span>Export Data Pelamar</span>
                            </a>
                            <a href="{{ route('reports.export.payments') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-surface-low text-secondary">
                                <x-icon name="wallet" class="size-4 text-amber-600" />
                                <span>Export Keuangan & Payroll</span>
                            </a>
                            <a href="{{ route('reports.export.attendance') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl hover:bg-surface-low text-secondary">
                                <x-icon name="calendar" class="size-4 text-blue-600" />
                                <span>Export Rekap Presensi</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- STATS OVERVIEW CARDS -->
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($stats as $label => $value)
                <div class="card !p-5 bg-white border border-black/5 shadow-sm hover:shadow-md transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">{{ $label }}</p>
                    <strong class="mt-2 block font-display text-2xl sm:text-3xl font-bold text-secondary">
                        {{ $value }}
                    </strong>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-[11px] text-primary font-semibold flex items-center gap-1">
                            <x-icon name="bolt" class="size-3.5" />
                            <span>Real-Time</span>
                        </span>
                        <div class="size-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-10">
    @if($user->hasRole('company'))
        <!-- COMPANY DASHBOARD BODY -->
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- RECENT APPLICANTS (2 COLS) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-xl font-bold text-secondary">Pelamar Masuk Terbaru</h2>
                        <p class="text-xs text-on-surface-variant">Kandidat casual worker yang baru saja melamar lowonganmu.</p>
                    </div>
                    <a href="{{ route('applications.index') }}" class="text-xs font-bold text-primary hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="card !p-0 bg-white border border-black/5 shadow-sm overflow-hidden divide-y divide-black/5">
                    @forelse($recentApplications as $application)
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-surface-low/50 transition">
                            <div class="flex items-start gap-3.5">
                                <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-primary-soft font-display text-lg font-bold text-primary">
                                    {{ strtoupper(substr($application->worker->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-display text-base font-bold text-secondary">{{ $application->worker->user->name }}</h3>
                                    <p class="text-xs text-on-surface-variant font-medium">
                                        Melamar untuk: <b class="text-secondary">{{ $application->job->title }}</b>
                                    </p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                        <span class="badge !py-0.5 !text-[11px]">{{ $application->job->category->name }}</span>
                                        <span class="text-on-surface-variant">{{ $application->created_at->diffForHumans() }}</span>
                                        @if($application->cv_path || $application->worker->cv_path)
                                            <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                                <x-icon name="check_circle" class="size-3" /> CV Terlampir
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center">
                                @if($application->cv_path || $application->worker->cv_path)
                                    <a href="{{ route('applications.cv.view', $application) }}" target="_blank" class="btn-ghost compact !py-1.5 !text-xs !bg-blue-50 !text-blue-700 font-bold hover:!bg-blue-100" title="Buka & Baca CV di Tab Baru">
                                        <x-icon name="visibility" class="size-3.5" />
                                        <span>Lihat CV</span>
                                    </a>
                                    <a href="{{ route('applications.cv.download', $application) }}" class="btn-ghost compact !py-1.5 !text-xs !bg-primary-soft !text-primary" title="Unduh CV">
                                        <x-icon name="description" class="size-3.5" />
                                    </a>
                                @endif
                                <a href="{{ route('applications.index') }}" class="btn-primary compact !py-1.5 !text-xs">
                                    <span>Review</span>
                                    <x-icon name="arrow_forward" class="size-3.5" />
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center p-6">
                            <x-icon name="description" class="size-10 text-neutral mx-auto" />
                            <h3 class="mt-3 text-sm font-bold text-secondary">Belum ada pelamar baru</h3>
                            <p class="mt-1 text-xs text-on-surface-variant">Pelamar yang melamar lowonganmu akan otomatis muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- LOWONGAN SAYA -->
                <div class="pt-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-display text-xl font-bold text-secondary">Status Lowongan Aktif</h2>
                            <p class="text-xs text-on-surface-variant">Pantau kuota dan masa tayang lowongan pekerjaan.</p>
                        </div>
                        <a href="{{ route('jobs.manage') }}" class="text-xs font-bold text-primary hover:underline">
                            Kelola Semua Lowongan &rarr;
                        </a>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse($companyJobs as $job)
                            <div class="card !p-4 bg-white border border-black/5 shadow-sm hover:border-primary/30 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="badge !py-0.5 !text-[11px]">{{ $job->category->name }}</span>
                                    <span class="badge !text-[10px] {{ $job->status === 'published' ? '!bg-emerald-50 !text-emerald-700' : ($job->status === 'expired' ? '!bg-amber-50 !text-amber-700' : '!bg-neutral-100') }}">
                                        {{ strtoupper($job->status) }}
                                    </span>
                                </div>
                                <h3 class="mt-2 font-display text-base font-bold text-secondary truncate">{{ $job->title }}</h3>
                                <p class="mt-1 text-xs text-primary font-bold">
                                    Rp{{ number_format($job->daily_rate, 0, ',', '.') }} / {{ $job->payment_type === 'project' ? 'proyek' : 'hari' }}
                                </p>
                                <div class="mt-3 flex items-center justify-between pt-3 border-t border-black/5 text-xs text-on-surface-variant">
                                    <span>{{ $job->applications_count }} Pelamar Masuk</span>
                                    <a href="{{ route('jobs.show', $job) }}" class="font-bold text-primary hover:underline">Lihat &rarr;</a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 card !p-8 text-center bg-white border border-black/5">
                                <p class="text-xs text-on-surface-variant">Belum ada lowongan yang diposting.</p>
                                <a href="{{ route('jobs.manage') }}" class="btn-primary compact mt-3">Posting Lowongan Pertama</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- COMPANY SIDEBAR WIDGETS (1 COL) -->
            <div class="space-y-6">
                <!-- WALLET CARD -->
                <div class="card !p-6 bg-gradient-to-br from-[#1b2533] to-[#2d3748] text-white shadow-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase tracking-wider text-slate-300 font-semibold">CoC Company Wallet</span>
                        <x-icon name="wallet" class="size-6 text-primary-soft" />
                    </div>
                    <strong class="mt-4 block font-display text-3xl font-bold tracking-tight text-white">
                        Rp{{ number_format($user->wallet?->balance ?? 0, 0, ',', '.') }}
                    </strong>
                    <p class="mt-1 text-xs text-slate-300">Saldo siap digunakan untuk pembayaran gaji instan casual worker.</p>
                    <div class="mt-5 pt-4 border-t border-white/10 flex items-center gap-3">
                        <a href="{{ route('wallet.index') }}" class="btn-primary compact flex-1 text-center justify-center !text-xs">
                            Isi Saldo / Transaksi
                        </a>
                    </div>
                </div>

                <!-- TODAY ATTENDANCES PREVIEW -->
                <div class="card !p-5 bg-white border border-black/5 shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-black/5">
                        <h3 class="font-display text-sm font-bold text-secondary flex items-center gap-2">
                            <x-icon name="bolt" class="size-4 text-amber-500" />
                            <span>Shift Berjalan Hari Ini</span>
                        </h3>
                        <span class="text-[11px] text-on-surface-variant">{{ now()->format('d M') }}</span>
                    </div>

                    <div class="mt-3 space-y-3">
                        @forelse($todayAttendances as $attendance)
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-surface-low">
                                <div>
                                    <b class="text-secondary block">{{ $attendance->application->worker->user->name }}</b>
                                    <span class="text-on-surface-variant text-[11px]">{{ $attendance->application->job->title }}</span>
                                </div>
                                <span class="badge !py-0.5 !text-[10px] {{ $attendance->status === 'present' ? '!bg-emerald-50 !text-emerald-700' : '!bg-sky-50 !text-sky-700' }}">
                                    {{ strtoupper($attendance->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-on-surface-variant py-4 text-center">Tidak ada jadwal shift presensi hari ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- QUICK NAVIGATION LINKS -->
                <div class="card !p-5 bg-white border border-black/5 shadow-sm">
                    <h3 class="font-display text-sm font-bold text-secondary mb-3">Pintasan Menu Perusahaan</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('jobs.manage') }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="work" class="size-4" />
                            <span>Kelola Lowongan</span>
                        </a>
                        <a href="{{ route('applications.index') }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="description" class="size-4" />
                            <span>Daftar Pelamar</span>
                        </a>
                        <a href="{{ route('reviews.index') }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="star" class="size-4" />
                            <span>Reviews & Rating</span>
                        </a>
                        <a href="{{ route('companies.show', $company) }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="storefront" class="size-4" />
                            <span>Profil Publik</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @elseif($user->hasRole('worker'))
        <!-- WORKER DASHBOARD BODY -->
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- MAIN SECTION (2 COLS) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- ACTIVE UPCOMING JOB HIGHLIGHT -->
                @if($upcomingJob)
                    <div class="rounded-3xl border border-primary/20 bg-gradient-to-br from-primary-soft/40 via-white to-surface p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="badge !bg-primary !text-white !font-bold">SHIFT AKTIF BERIKUTNYA</span>
                                <h2 class="mt-3 font-display text-2xl font-bold text-secondary">{{ $upcomingJob->job->title }}</h2>
                                <p class="mt-1 text-xs text-on-surface-variant font-medium">
                                    {{ $upcomingJob->job->company->name }} &bull; {{ $upcomingJob->job->location }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-on-surface-variant">Upah Disepakati</span>
                                <strong class="block font-display text-xl font-bold text-primary">
                                    Rp{{ number_format($upcomingJob->job->daily_rate, 0, ',', '.') }}
                                </strong>
                                <span class="text-[11px] text-on-surface-variant">/{{ $upcomingJob->job->payment_type === 'project' ? 'proyek' : 'hari' }}</span>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-white border border-black/5 text-xs">
                            <div>
                                <span class="text-on-surface-variant block text-[11px]">Mulai Shift</span>
                                <b class="text-secondary">{{ $upcomingJob->job->starts_at->translatedFormat('d M Y, H:i') }}</b>
                            </div>
                            <div>
                                <span class="text-on-surface-variant block text-[11px]">Selesai Shift</span>
                                <b class="text-secondary">{{ $upcomingJob->job->ends_at->translatedFormat('d M Y, H:i') }}</b>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="text-on-surface-variant block text-[11px]">Presensi</span>
                                <b class="text-emerald-700 font-bold">Siap Bertugas</b>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between pt-4 border-t border-black/10">
                            <a href="{{ route('jobs.my') }}" class="btn-primary compact !text-xs">
                                <span>Lihat Jadwal Lengkap</span>
                                <x-icon name="arrow_forward" class="size-3.5" />
                            </a>
                            <a href="{{ route('attendance.index') }}" class="btn-ghost compact !text-xs !bg-white">
                                <x-icon name="bolt" class="size-3.5 text-amber-500" />
                                <span>Check-in Presensi</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- RECENT APPLICATIONS PIPELINE -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-display text-xl font-bold text-secondary">Riwayat Lamaran Pekerjaan</h2>
                            <p class="text-xs text-on-surface-variant">Status proses verifikasi lamaran kerjamu oleh perusahaan.</p>
                        </div>
                        <a href="{{ route('applications.index') }}" class="text-xs font-bold text-primary hover:underline">
                            Semua Lamaran &rarr;
                        </a>
                    </div>

                    <div class="card !p-0 bg-white border border-black/5 shadow-sm overflow-hidden divide-y divide-black/5">
                        @forelse($recentApplications as $app)
                            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-surface-low/50 transition">
                                <div class="flex items-start gap-3.5">
                                    <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-surface-low font-display text-base font-bold text-primary">
                                        {{ strtoupper(substr($app->job->company->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h3 class="font-display text-base font-bold text-secondary">{{ $app->job->title }}</h3>
                                        <p class="text-xs text-on-surface-variant">{{ $app->job->company->name }} &bull; {{ $app->job->location }}</p>
                                        <div class="mt-1.5 flex items-center gap-2 text-[11px] text-on-surface-variant">
                                            <span>Rp{{ number_format($app->job->daily_rate, 0, ',', '.') }}</span>
                                            <span>&bull;</span>
                                            <span>Dilamar {{ $app->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5 self-end sm:self-center">
                                    <span class="badge !py-1 !text-xs {{ $app->status === 'accepted' ? '!bg-emerald-50 !text-emerald-700' : ($app->status === 'rejected' ? '!bg-rose-50 !text-rose-700' : ($app->status === 'completed' ? '!bg-blue-50 !text-blue-700' : '!bg-amber-50 !text-amber-700')) }}">
                                        {{ strtoupper($app->status) }}
                                    </span>
                                    <a href="{{ route('jobs.show', $app->job) }}" class="btn-ghost compact !py-1.5 !text-xs">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center p-6">
                                <x-icon name="work" class="size-10 text-neutral mx-auto" />
                                <h3 class="mt-3 text-sm font-bold text-secondary">Belum ada lamaran</h3>
                                <p class="mt-1 text-xs text-on-surface-variant">Mulai jelajahi dan lamar lowongan harian yang tersedia sekarang.</p>
                                <a href="{{ route('jobs.index') }}" class="btn-primary compact mt-4">Jelajahi Lowongan</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- WORKER SIDEBAR WIDGETS (1 COL) -->
            <div class="space-y-6">
                <!-- WALLET CARD FOR WORKER -->
                <div class="card !p-6 bg-gradient-to-br from-[#1b2533] to-[#2d3748] text-white shadow-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase tracking-wider text-slate-300 font-semibold">Dompet Saldo Worker</span>
                        <x-icon name="wallet" class="size-6 text-primary-soft" />
                    </div>
                    <strong class="mt-4 block font-display text-3xl font-bold tracking-tight text-white">
                        Rp{{ number_format($user->wallet?->balance ?? 0, 0, ',', '.') }}
                    </strong>
                    <p class="mt-1 text-xs text-slate-300">Gaji dari pekerjaan yang selesai otomatis masuk ke sini.</p>
                    <div class="mt-5 pt-4 border-t border-white/10 flex items-center gap-3">
                        <a href="{{ route('wallet.index') }}" class="btn-primary compact flex-1 text-center justify-center !text-xs">
                            Tarik Dana / Riwayat
                        </a>
                    </div>
                </div>

                <!-- CV PROFILE STATUS WIDGET -->
                <div class="card !p-5 bg-white border border-black/5 shadow-sm">
                    <div class="flex items-center gap-3 pb-3 border-b border-black/5">
                        <div class="grid size-10 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                            <x-icon name="description" class="size-5" />
                        </div>
                        <div>
                            <h3 class="font-display text-sm font-bold text-secondary">Kelengkapan Profil & CV</h3>
                            <p class="text-xs text-on-surface-variant">CV siap dilampirkan otomatis</p>
                        </div>
                    </div>
                    <div class="mt-3 text-xs space-y-2">
                        <p class="text-on-surface-variant leading-relaxed">
                            Pastikan profil dan keahlianmu selalu terupdate agar perusahaan lebih cepat menerima lamaranmu.
                        </p>
                        <a href="{{ route('profile.edit') }}" class="btn-ghost compact !py-1.5 !text-xs !bg-surface-low w-full justify-center">
                            Edit Profil & Kelola CV
                        </a>
                    </div>
                </div>

                <!-- SHORTCUTS -->
                <div class="card !p-5 bg-white border border-black/5 shadow-sm">
                    <h3 class="font-display text-sm font-bold text-secondary mb-3">Pintasan Pekerja</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('jobs.my') }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="bookmark" class="size-4" />
                            <span>Pekerjaan Saya</span>
                        </a>
                        <a href="{{ route('attendance.index') }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="bolt" class="size-4" />
                            <span>Presensi Shift</span>
                        </a>
                        <a href="{{ route('reviews.index') }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="star" class="size-4" />
                            <span>Reviews Saya</span>
                        </a>
                        <a href="{{ route('messages.index') }}" class="p-3 rounded-2xl bg-surface-low hover:bg-primary-soft hover:text-primary transition text-xs font-semibold flex flex-col gap-1.5">
                            <x-icon name="messages" class="size-4" />
                            <span>Pesan Masuk</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- ADMIN DASHBOARD BODY -->
        <div class="space-y-6">
            <div class="card !p-6 bg-white border border-black/5 shadow-sm">
                <h2 class="font-display text-xl font-bold text-secondary">Aktivitas Platform Terbaru</h2>
                <p class="text-xs text-on-surface-variant">Ringkasan transaksi dan perputaran kerja di Casual on Call.</p>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-bold text-secondary mb-3">Lowongan Terbaru</h3>
                        <div class="divide-y divide-black/5 border border-black/5 rounded-2xl overflow-hidden">
                            @foreach($recentJobs as $job)
                                <div class="p-3 flex items-center justify-between text-xs hover:bg-surface-low">
                                    <div>
                                        <b class="text-secondary block">{{ $job->title }}</b>
                                        <span class="text-on-surface-variant text-[11px]">{{ $job->company->name }}</span>
                                    </div>
                                    <span class="badge !py-0.5 !text-[10px]">{{ strtoupper($job->status) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-secondary mb-3">Pembayaran Terakhir</h3>
                        <div class="divide-y divide-black/5 border border-black/5 rounded-2xl overflow-hidden">
                            @foreach($recentPayments as $payment)
                                <div class="p-3 flex items-center justify-between text-xs hover:bg-surface-low">
                                    <div>
                                        <b class="text-secondary block">{{ $payment->invoice_number }}</b>
                                        <span class="text-on-surface-variant text-[11px]">Rp{{ number_format($payment->total, 0, ',', '.') }}</span>
                                    </div>
                                    <span class="badge !py-0.5 !text-[10px] !bg-emerald-50 !text-emerald-700">LUNAS</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>

<!-- Close export menu on outside click -->
<script>
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('export-dropdown-wrapper');
    const menu = document.getElementById('export-menu');
    if (wrapper && menu && !wrapper.contains(e.target)) {
        menu.classList.add('hidden');
    }
});
</script>
@endsection