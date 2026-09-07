@extends('layouts.app')
@section('title', 'Presensi & Kehadiran Shift')
@section('content')
@php
    $allAttendances = $applications->getCollection()->flatMap->attendances;
    // 1. Shift in progress (present)
    // 2. Shift today
    // 3. Next scheduled shift
    // 4. Any first attendance
    $targetAttendance = $allAttendances->firstWhere('status', 'present')
        ?? $allAttendances->first(fn($item) => $item->work_date->isToday())
        ?? $allAttendances->firstWhere('status', 'scheduled')
        ?? $allAttendances->first();
    $active = $targetAttendance?->application ?? $applications->firstWhere('status', 'accepted') ?? $applications->first();
@endphp

<section class="mx-auto max-w-[1200px] px-4 py-10 sm:px-6 lg:px-10 lg:py-14">


    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="eyebrow">ATTENDANCE SYSTEM</span>
            <h1 class="mt-2 font-display text-3xl font-bold lg:text-4xl">Presensi & Kehadiran Shift</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Check-in dan check-out shift kerja casual sesuai jadwal yang telah disepakati.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.export.attendance') }}" class="btn-ghost compact !bg-white !border !border-black/10 shadow-sm" title="Export Presensi">
                <x-icon name="description" class="size-4 text-primary" />
                <span>Export Presensi</span>
            </a>
        </div>
    </div>

    @if($active && $targetAttendance)
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.5fr_1fr]">
            <div class="rounded-[28px] bg-gradient-to-br from-secondary via-[#321e25] to-[#4f2933] p-6 text-white sm:p-9 shadow-xl">
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-3.5 py-1 text-xs font-bold {{ $targetAttendance->status === 'present' ? 'bg-amber-500 text-white' : ($targetAttendance->status === 'completed' ? 'bg-emerald-600 text-white' : 'bg-[#007d7d] text-white') }}">
                        {{ $targetAttendance->status === 'present' ? 'SEDANG BERTUGAS' : ($targetAttendance->status === 'completed' ? 'SHIFT SELESAI' : 'SHIFT AKTIF TERDEKAT') }}
                    </span>
                    @if($targetAttendance->work_date->isToday())
                        <span class="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-semibold text-white">Hari Ini</span>
                    @endif
                </div>
                
                <p class="mt-4 text-white/70 text-xs sm:text-sm font-mono">
                    {{ $targetAttendance->work_date->format('l, d F Y') }}
                </p>
                
                <h2 class="mt-1 font-display text-2xl sm:text-3xl font-bold">{{ $active->job->title }}</h2>
                <p class="mt-1 text-white/80 text-xs sm:text-sm">{{ $active->job->company->name }} &middot; {{ $active->job->location }}</p>

                <div class="mt-6 rounded-2xl bg-white/10 p-4 sm:p-5 border border-white/10 backdrop-blur">
                    <p class="text-xs uppercase tracking-wider text-white/60">Catatan Waktu Kerja Shift Ini</p>
                    <div class="mt-2 flex items-center gap-4">
                        <div>
                            <span class="text-[11px] text-white/60 block">Masuk (Clock-In)</span>
                            <strong class="font-display text-xl sm:text-2xl font-bold">
                                {{ $targetAttendance->clock_in_at?->format('H:i') ?? '--:--' }}
                            </strong>
                        </div>
                        <span class="text-white/40 text-xl font-bold">&ndash;</span>
                        <div>
                            <span class="text-[11px] text-white/60 block">Selesai (Clock-Out)</span>
                            <strong class="font-display text-xl sm:text-2xl font-bold">
                                {{ $targetAttendance->clock_out_at?->format('H:i') ?? '--:--' }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    @if(!$targetAttendance->clock_in_at)
                        <form method="POST" action="{{ route('attendance.clock-in', $targetAttendance) }}" class="attendance-form inline">
                            @csrf
                            <input type="hidden" name="latitude" class="geo-lat" value="">
                            <input type="hidden" name="longitude" class="geo-lng" value="">
                            <button class="btn-primary !bg-[#007d7d] hover:!bg-[#006262] shadow-lg" type="submit">
                                <x-icon name="check_circle" class="size-4" />
                                <span>Check-In Sekarang</span>
                            </button>
                        </form>
                    @elseif(!$targetAttendance->clock_out_at)
                        <form method="POST" action="{{ route('attendance.clock-out', $targetAttendance) }}" class="inline">
                            @csrf
                            <button class="btn-primary !bg-amber-500 hover:!bg-amber-600 shadow-lg text-white" type="submit">
                                <x-icon name="check" class="size-4" />
                                <span>Check-Out Sekarang</span>
                            </button>
                        </form>
                    @else
                        <span class="rounded-xl bg-white/20 px-5 py-2.5 font-bold text-white text-xs inline-flex items-center gap-2">
                            <x-icon name="check_circle" class="size-4 text-[#5df2d6]" />
                            <span>Shift Ini Sudah Selesai</span>
                        </span>
                    @endif
                    <a href="{{ route('jobs.show', $active->job) }}" class="btn-ghost !text-white !border-white/20 compact text-xs">
                        Lihat Detail Tugas
                    </a>
                </div>
            </div>

            <div class="card !p-6 bg-white border border-black/5 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-display text-lg font-bold text-secondary">Rincian Shift & Penugasan</h3>
                    <dl class="mt-5 space-y-4 text-xs sm:text-sm">
                        <div>
                            <dt class="text-on-surface-variant">Jadwal Jam Kerja Pekerjaan</dt>
                            <dd class="mt-0.5 font-bold text-secondary">
                                {{ $active->job->starts_at->format('d M Y, H:i') }} &ndash; {{ $active->job->ends_at->format('d M Y, H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant">Lokasi Penugasan</dt>
                            <dd class="mt-0.5 font-bold text-secondary">{{ $active->job->location }}</dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant">Upah Disepakati</dt>
                            <dd class="mt-0.5 font-bold text-primary font-mono text-base">
                                Rp{{ number_format($active->job->daily_rate, 0, ',', '.') }} / {{ $active->job->payment_type === 'project' ? 'proyek' : 'hari' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant">Total Durasi Shift</dt>
                            <dd class="mt-0.5 font-bold text-secondary">{{ $active->attendances->count() }} hari shift terjadwal</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 pt-4 border-t border-black/5 flex items-center justify-between">
                    <span class="text-xs text-on-surface-variant">Kontak PIC Perusahaan:</span>
                    <a href="{{ route('messages.index') }}" class="text-xs font-bold text-primary hover:underline">Chat via CoC</a>
                </div>
            </div>
        </div>
    @else
        <div class="card mt-8 py-16 text-center">
            <div class="mx-auto grid size-16 place-items-center rounded-full bg-primary-soft text-primary">
                <x-icon name="schedule" class="size-8" />
            </div>
            <h2 class="mt-4 font-display text-2xl font-bold">Tidak ada jadwal shift aktif</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Jadwal presensi akan otomatis aktif setelah lamaranmu diterima oleh perusahaan pemberi kerja.</p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="{{ route('jobs.index') }}" class="btn-primary compact">Cari Lowongan</a>
                <a href="{{ route('applications.index') }}" class="btn-ghost compact">Status Lamaran</a>
            </div>
        </div>
    @endif

    <!-- RIWAYAT SEMUA SHIFT DENGAN QUICK-ACTION -->
    <div class="mt-12">
        <div class="flex items-center justify-between pb-3 border-b border-black/5">
            <div>
                <h2 class="font-display text-2xl font-bold text-secondary">Semua Jadwal Shift & Presensi</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Daftar lengkap kehadiran per shift. Kamu bisa check-in atau check-out langsung dari baris shift.</p>
            </div>
            <span class="text-xs font-bold text-primary bg-primary-soft px-3 py-1 rounded-full">
                {{ $allAttendances->count() }} Total Shift
            </span>
        </div>
        
        <div class="mt-5 space-y-3">
            @forelse($applications as $application)
                @foreach($application->attendances as $attendance)
                    <div class="flex flex-col justify-between gap-4 rounded-2xl bg-white p-5 border border-black/5 shadow-xs sm:flex-row sm:items-center hover:border-primary/20 transition">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <b class="text-sm font-bold text-secondary truncate">{{ $application->job->title }}</b>
                                @if($attendance->work_date->isToday())
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-[10px] font-bold">HARI INI</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-xs text-on-surface-variant">
                                {{ $attendance->work_date->format('l, d M Y') }} &middot; {{ $application->job->company->name }} &middot; {{ $application->job->location }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-xs">
                            <div class="text-right">
                                <span class="text-[10px] text-on-surface-variant block">Jam Kerja:</span>
                                <span class="font-mono text-secondary font-bold">
                                    {{ $attendance->clock_in_at?->format('H:i') ?: '--:--' }} &ndash; {{ $attendance->clock_out_at?->format('H:i') ?: '--:--' }}
                                </span>
                            </div>

                            @if($attendance->status === 'completed')
                                <span class="badge-success inline-flex items-center gap-1">
                                    <x-icon name="check_circle" class="size-3.5" />
                                    <span>Selesai</span>
                                </span>
                            @elseif($attendance->status === 'present' || ($attendance->clock_in_at && !$attendance->clock_out_at))
                                <form method="POST" action="{{ route('attendance.clock-out', $attendance) }}" class="inline">
                                    @csrf
                                    <button class="btn-primary compact !py-1.5 !px-3.5 !text-xs !bg-amber-500 hover:!bg-amber-600 shadow-sm" type="submit" title="Selesaikan shift">
                                        <x-icon name="check" class="size-3.5" />
                                        <span>Check-Out</span>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('attendance.clock-in', $attendance) }}" class="attendance-form inline">
                                    @csrf
                                    <input type="hidden" name="latitude" class="geo-lat" value="">
                                    <input type="hidden" name="longitude" class="geo-lng" value="">
                                    <button class="btn-primary compact !py-1.5 !px-3.5 !text-xs !bg-[#007d7d] hover:!bg-[#006262] shadow-sm" type="submit" title="Mulai shift">
                                        <x-icon name="check_circle" class="size-3.5" />
                                        <span>Check-In</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="card text-center text-on-surface-variant py-12">
                    <p class="font-medium">Belum ada riwayat kehadiran shift.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $applications->links() }}
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition((pos) => {
            document.querySelectorAll('.geo-lat').forEach(input => input.value = pos.coords.latitude.toFixed(6));
            document.querySelectorAll('.geo-lng').forEach(input => input.value = pos.coords.longitude.toFixed(6));
        }, () => {
            // Geolocation not granted or failed, coordinates remain null
        }, { timeout: 5000 });
    }
});
</script>
@endsection