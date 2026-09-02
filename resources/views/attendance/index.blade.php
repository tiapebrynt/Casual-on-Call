@extends('layouts.app')
@section('title', 'Presensi & Kehadiran Shift')
@section('content')
@php
    $todayAttendance = $applications->getCollection()->flatMap->attendances->first(fn($item) => $item->work_date->isToday());
    $active = $todayAttendance?->application ?? $applications->firstWhere('status', 'accepted');
@endphp
<section class="mx-auto max-w-[1200px] px-4 py-10 sm:px-6 lg:px-10 lg:py-14">
    <div>
        <span class="eyebrow">ATTENDANCE SYSTEM</span>
        <h1 class="mt-2 font-display text-3xl font-bold lg:text-4xl">Presensi & Kehadiran Shift</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Check-in dan check-out shift kerja casual sesuai jadwal yang telah disepakati.</p>
    </div>

    @if($active)
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.5fr_1fr]">
            <div class="rounded-[28px] bg-gradient-to-br from-secondary via-[#321e25] to-[#4f2933] p-6 text-white sm:p-9 shadow-xl">
                <span class="rounded-full bg-[#007d7d] px-3.5 py-1 text-xs font-bold">
                    {{ $todayAttendance ? 'SHIFT HARI INI' : 'SHIFT TERJADWAL' }}
                </span>
                
                <p class="mt-6 text-white/60 text-xs sm:text-sm">
                    {{ $todayAttendance?->work_date->format('d F Y') ?? $active->job->starts_at->format('d F Y') }}
                </p>
                
                <h2 class="mt-1 font-display text-2xl sm:text-3xl font-bold">{{ $active->job->title }}</h2>
                <p class="mt-1 text-white/70 text-xs sm:text-sm">{{ $active->job->company->name }} &middot; {{ $active->job->location }}</p>

                <div class="mt-8 rounded-2xl bg-white/10 p-4 sm:p-5 border border-white/10 backdrop-blur">
                    <p class="text-xs uppercase tracking-wider text-white/60">Catatan Waktu Kerja</p>
                    <strong class="mt-2 block font-display text-2xl sm:text-3xl font-bold">
                        {{ $todayAttendance?->clock_in_at?->format('H:i') ?? '--:--' }} &ndash; {{ $todayAttendance?->clock_out_at?->format('H:i') ?? '--:--' }}
                    </strong>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if($todayAttendance && !$todayAttendance->clock_in_at)
                        <form method="POST" action="{{ route('attendance.clock-in', $todayAttendance) }}">
                            @csrf
                            <button class="btn-primary !bg-[#007d7d] hover:!bg-[#006262]" type="submit">
                                <x-icon name="check_circle" class="size-4" />
                                <span>Check-In Sekarang</span>
                            </button>
                        </form>
                    @elseif($todayAttendance && !$todayAttendance->clock_out_at)
                        <form method="POST" action="{{ route('attendance.clock-out', $todayAttendance) }}">
                            @csrf
                            <button class="btn-primary" type="submit">
                                <x-icon name="check" class="size-4" />
                                <span>Check-Out Sekarang</span>
                            </button>
                        </form>
                    @elseif($todayAttendance)
                        <span class="rounded-xl bg-white/20 px-5 py-2.5 font-bold text-white text-xs inline-flex items-center gap-1.5">
                            <x-icon name="check_circle" class="size-4 text-[#5df2d6]" />
                            <span>Shift Selesai</span>
                        </span>
                    @else
                        <span class="rounded-xl bg-white/10 px-5 py-2.5 text-xs text-white/80">
                            Shift berikutnya terjadwal pada {{ $active->job->starts_at->format('d M Y') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="card !p-6 bg-white border border-black/5 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-display text-lg font-bold text-secondary">Rincian Shift</h3>
                    <dl class="mt-5 space-y-4 text-xs sm:text-sm">
                        <div>
                            <dt class="text-on-surface-variant">Jadwal Jam Kerja</dt>
                            <dd class="mt-0.5 font-bold text-secondary">
                                {{ $active->job->starts_at->format('d M Y, H:i') }} &ndash; {{ $active->job->ends_at->format('d M Y, H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant">Lokasi Tugas</dt>
                            <dd class="mt-0.5 font-bold text-secondary">{{ $active->job->location }}</dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant">Total Durasi Shift</dt>
                            <dd class="mt-0.5 font-bold text-secondary">{{ $active->attendances->count() }} hari terjadwal</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 pt-4 border-t border-black/5">
                    <a href="{{ route('jobs.show', $active->job) }}" class="btn-ghost compact w-full justify-center text-xs">
                        <span>Lihat Deskripsi Pekerjaan &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="card mt-8 py-16 text-center">
            <div class="mx-auto grid size-16 place-items-center rounded-full bg-primary-soft text-primary">
                <x-icon name="clock" class="size-8" />
            </div>
            <h2 class="mt-4 font-display text-2xl font-bold">Tidak ada shift aktif</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Jadwal presensi akan otomatis muncul setelah lamaranmu diterima oleh perusahaan.</p>
            <a href="{{ route('jobs.index') }}" class="btn-primary compact mt-6">Cari Lowongan</a>
        </div>
    @endif

    <div class="mt-12">
        <h2 class="font-display text-2xl font-bold">Riwayat Kehadiran Shift</h2>
        
        <div class="mt-5 space-y-3">
            @forelse($applications as $application)
                @foreach($application->attendances as $attendance)
                    <div class="flex flex-col justify-between gap-3 rounded-2xl bg-white p-5 border border-black/5 shadow-xs sm:flex-row sm:items-center">
                        <div>
                            <b class="text-sm font-bold text-secondary">{{ $application->job->title }}</b>
                            <p class="mt-0.5 text-xs text-on-surface-variant">
                                {{ $attendance->work_date->format('d M Y') }} &middot; {{ $application->job->company->name }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            @if($attendance->status === 'completed')
                                <span class="badge-success">Selesai</span>
                            @elseif($attendance->status === 'present')
                                <span class="badge-warning">Sedang Shift</span>
                            @else
                                <span class="badge-neutral">Terjadwal</span>
                            @endif
                            <span class="font-mono text-secondary font-medium">
                                {{ $attendance->clock_in_at?->format('H:i') ?: '--:--' }} &ndash; {{ $attendance->clock_out_at?->format('H:i') ?: '--:--' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="card text-center text-on-surface-variant py-10">Belum ada riwayat kehadiran.</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $applications->links() }}
        </div>
    </div>
</section>
@endsection


