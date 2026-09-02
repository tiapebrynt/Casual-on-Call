@extends('layouts.app')
@section('title', $job->title)
@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-[#fff7f8] via-surface to-[#eaffff]">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-10 lg:py-14">
        <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
            <x-icon name="arrow_back" class="size-4" />
            <span>Kembali ke daftar lowongan</span>
        </a>

        <div class="mt-6 flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge">{{ $job->category->name }}</span>
                    <span class="badge {{ $job->payment_type === 'project' ? '!bg-amber-50 !text-amber-700' : '' }}">
                        {{ $job->payment_type === 'project' ? 'Proyek / Borongan' : 'Harian' }}
                    </span>
                    @if($job->status === 'expired')
                        <span class="badge-warning">Expired</span>
                    @endif
                </div>

                <h1 class="mt-3 max-w-4xl font-display text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                    {{ $job->title }}
                </h1>

                <a href="{{ route('companies.show', $job->company) }}" class="mt-2 inline-flex items-center gap-1.5 text-sm sm:text-base text-on-surface-variant hover:text-primary transition-colors">
                    <x-icon name="building" class="size-4" />
                    <span>{{ $job->company->name }}</span>
                    <span>&middot;</span>
                    <x-icon name="location_on" class="size-4" />
                    <span>{{ $job->location }}</span>
                </a>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm border border-black/5 text-left md:text-right shrink-0">
                <span class="text-xs text-on-surface-variant block uppercase tracking-wider">Besaran Upah</span>
                <b class="font-display text-2xl sm:text-3xl font-bold text-primary block mt-1">
                    Rp{{ number_format($job->daily_rate, 0, ',', '.') }}
                </b>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    / {{ $job->payment_type === 'project' ? 'proyek' : 'hari' }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-10">
    <article class="card !p-6 sm:!p-8 bg-white border border-black/5 shadow-sm space-y-8">
        <div>
            <h2 class="font-display text-xl font-bold text-secondary">Deskripsi Pekerjaan</h2>
            <p class="mt-4 whitespace-pre-line text-sm leading-7 text-on-surface-variant">{{ $job->description }}</p>
        </div>

        <div class="border-t border-black/10 pt-8">
            <h2 class="font-display text-xl font-bold text-secondary">Persyaratan & Kualifikasi</h2>
            <ul class="mt-4 space-y-3">
                @forelse($job->requirements as $requirement)
                    <li class="flex items-start gap-3 text-sm text-secondary">
                        <span class="grid size-5 shrink-0 place-items-center rounded-full bg-primary-soft text-xs font-bold text-primary">✓</span>
                        <span>{{ $requirement->requirement }}</span>
                    </li>
                @empty
                    <li class="text-sm text-on-surface-variant italic">Tidak ada persyaratan khusus.</li>
                @endforelse
            </ul>
        </div>
    </article>

    <aside class="space-y-6">
        <div class="card !p-6 bg-white border border-black/5 shadow-sm sticky top-28">
            <h3 class="font-display text-lg font-bold text-secondary">Ringkasan Pekerjaan</h3>
            
            <dl class="mt-5 space-y-4 text-xs sm:text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-on-surface-variant">Periode Kerja</dt>
                    <dd class="text-right font-bold text-secondary">
                        {{ $job->starts_at->format('d M') }} &ndash; {{ $job->ends_at->format('d M Y') }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-on-surface-variant">Durasi</dt>
                    <dd class="font-bold text-secondary">{{ $job->duration_days }} hari</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-on-surface-variant">Kebutuhan Posisi</dt>
                    <dd class="font-bold text-secondary">{{ $job->vacancies }} orang</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-on-surface-variant">Total Pelamar</dt>
                    <dd class="font-bold text-primary">{{ $job->applications_count ?? $job->applications()->count() }} orang</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-on-surface-variant">Batas Lamaran</dt>
                    <dd class="font-bold text-secondary">{{ $job->application_deadline->format('d M Y, H:i') }}</dd>
                </div>
            </dl>

            @auth
                @if(auth()->user()->hasRole('worker'))
                    <a href="{{ route('applications.create', $job) }}" class="btn-primary mt-6 w-full justify-center">
                        <x-icon name="send" class="size-4" />
                        <span>Lamar Sekarang</span>
                    </a>
                    <p class="mt-2 text-center text-xs text-on-surface-variant">Formulir pendaftaran singkat & gratis.</p>
                @elseif(auth()->user()->hasRole('company') && auth()->user()->company?->id === $job->company_id)
                    <a href="{{ route('applications.index') }}" class="btn-primary mt-6 w-full justify-center">
                        <span>Lihat Daftar Pelamar</span>
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-primary mt-6 w-full justify-center">
                    <span>Masuk untuk Melamar</span>
                </a>
            @endauth
        </div>
    </aside>
</section>
@endsection


