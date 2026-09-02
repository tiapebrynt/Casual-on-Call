@extends('layouts.app')
@section('title', 'Pekerjaan Saya')
@section('content')
<section class="mx-auto max-w-[1280px] px-4 py-10 sm:px-6 lg:px-10 lg:py-14">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <span class="eyebrow">WORKER WORKSPACE</span>
            <h1 class="mt-2 font-display text-3xl font-bold lg:text-4xl">Pekerjaan Saya</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Kelola shift aktif, jadwal mendatang, ulasan perusahaan, dan riwayat pekerjaanmu.</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="btn-primary">
            <x-icon name="search" class="size-4" />
            <span>Cari Lowongan</span>
        </a>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="card !p-6">
            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Pekerjaan Aktif</p>
            <strong class="mt-2 block font-display text-3xl text-[#006262]">{{ $activeCount }}</strong>
        </div>
        <div class="card !p-6">
            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Pekerjaan Selesai</p>
            <strong class="mt-2 block font-display text-3xl text-secondary">{{ $completedCount }}</strong>
        </div>
        <div class="card !p-6">
            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Total Penghasilan</p>
            <strong class="mt-2 block font-display text-3xl text-primary">Rp{{ number_format($totalEarnings, 0, ',', '.') }}</strong>
        </div>
    </div>

    @if($activeJob)
        <div class="mt-8 overflow-hidden rounded-[28px] bg-gradient-to-br from-secondary via-[#321e25] to-[#4f2933] p-6 text-white shadow-2xl sm:p-9">
            <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-[#007d7d] px-3 py-1 text-xs font-bold">SHIFT AKTIF</span>
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs">{{ $activeJob->job->category->name }}</span>
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs capitalize">{{ $activeJob->job->payment_type === 'project' ? 'Proyek' : 'Harian' }}</span>
                    </div>

                    <h2 class="mt-4 font-display text-2xl font-bold sm:text-3xl">{{ $activeJob->job->title }}</h2>
                    <p class="mt-2 text-white/70 text-sm sm:text-base">{{ $activeJob->job->company->name }} &middot; {{ $activeJob->job->location }}</p>

                    <div class="mt-6 grid gap-4 text-xs sm:text-sm sm:grid-cols-3 bg-white/5 rounded-2xl p-4 border border-white/10">
                        <div>
                            <span class="block text-white/50">Jadwal Tanggal</span>
                            <b class="mt-0.5 block text-white">{{ $activeJob->job->starts_at->format('d M') }} &ndash; {{ $activeJob->job->ends_at->format('d M Y') }}</b>
                        </div>
                        <div>
                            <span class="block text-white/50">Rate Upah</span>
                            <b class="mt-0.5 block text-white">Rp{{ number_format($activeJob->job->daily_rate, 0, ',', '.') }}/{{ $activeJob->job->payment_type === 'project' ? 'proyek' : 'hari' }}</b>
                        </div>
                        <div>
                            <span class="block text-white/50">Status Lamaran</span>
                            <b class="mt-0.5 block text-[#5df2d6]">Diterima & Terjadwal</b>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center rounded-2xl bg-white/10 p-6 text-center backdrop-blur sm:min-w-[200px]">
                    <span class="text-xs uppercase tracking-wider text-white/70">Mulai Dalam</span>
                    <strong class="mt-1 block font-display text-4xl font-extrabold text-white">
                        {{ $activeDays }}
                    </strong>
                    <span class="text-xs text-white/80">hari lagi</span>
                    <div class="mt-4 flex w-full flex-col gap-2">
                        <a href="{{ route('attendance.index') }}" class="block rounded-full bg-[#007d7d] px-4 py-2 text-xs font-bold text-white hover:bg-[#006262] transition-colors">
                            Check-in Kehadiran
                        </a>
                        <a href="{{ route('jobs.show', $activeJob->job) }}" class="block rounded-full bg-white px-4 py-2 text-xs font-bold text-secondary hover:bg-slate-100 transition-colors">
                            Lihat Detail Job
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-12">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl font-bold">Semua Riwayat Pekerjaan</h2>
            <span class="text-xs text-on-surface-variant font-medium">{{ $jobs->total() }} pekerjaan tercatat</span>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            @forelse($jobs as $application)
                @php
                    $workerReview = $application->ratings->firstWhere('reviewer_id', auth()->id());
                @endphp
                <article class="flex flex-col justify-between rounded-3xl bg-white p-6 shadow-sm border border-black/5 hover:border-primary/20 transition-all">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-primary-soft font-display text-lg font-bold text-primary">
                                {{ strtoupper(substr($application->job->company->name, 0, 1)) }}
                            </div>
                            @if($application->status === 'accepted')
                                <span class="badge-success">DITERIMA</span>
                            @elseif($application->status === 'completed')
                                <span class="badge-neutral">SELESAI</span>
                            @else
                                <span class="badge">{{ strtoupper($application->status) }}</span>
                            @endif
                        </div>

                        <h3 class="mt-4 font-display text-lg font-bold text-secondary">
                            <a href="{{ route('jobs.show', $application->job) }}" class="hover:text-primary transition-colors">
                                {{ $application->job->title }}
                            </a>
                        </h3>
                        <p class="mt-1 text-xs text-on-surface-variant">{{ $application->job->company->name }} &middot; {{ $application->job->location }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-2 rounded-2xl bg-surface-low p-3.5 text-xs">
                            <div>
                                <span class="block text-on-surface-variant">Jadwal Mulai</span>
                                <b>{{ $application->job->starts_at->format('d M Y') }}</b>
                            </div>
                            <div>
                                <span class="block text-on-surface-variant">Catatan Kehadiran</span>
                                <b>{{ $application->attendances->count() }} shift</b>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-black/5 pt-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="block text-[11px] text-on-surface-variant">Upah</span>
                                <strong class="font-display text-base font-bold text-primary">
                                    Rp{{ number_format($application->job->daily_rate, 0, ',', '.') }}
                                </strong>
                                <span class="text-[11px] text-on-surface-variant">/{{ $application->job->payment_type === 'project' ? 'proyek' : 'hari' }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($application->status === 'completed')
                                    @if($workerReview)
                                        <button type="button" onclick="document.getElementById('review-modal-{{ $application->id }}').showModal()" class="btn-ghost compact !py-1.5 !text-xs !bg-amber-50 !text-amber-700">
                                            <span>⭐ {{ $workerReview->score }}/5 Diulas</span>
                                        </button>
                                    @else
                                        <button type="button" onclick="document.getElementById('review-modal-{{ $application->id }}').showModal()" class="btn-primary compact !py-1.5 !text-xs">
                                            <span>⭐ Beri Ulasan</span>
                                        </button>
                                    @endif
                                @endif
                                <a href="{{ route('jobs.show', $application->job) }}" class="btn-ghost compact !py-1.5 !text-xs">
                                    <span>Detail &rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- REVIEW MODAL FOR WORKER -->
                @if($application->status === 'completed')
                <dialog id="review-modal-{{ $application->id }}" class="rounded-3xl p-0 backdrop:bg-black/60 backdrop:backdrop-blur-sm w-full max-w-lg shadow-2xl">
                    <div class="bg-white p-6 sm:p-8">
                        <div class="flex items-center justify-between border-b border-black/10 pb-4">
                            <div>
                                <span class="badge">REVIEW PERUSAHAAN</span>
                                <h3 class="mt-1 font-display text-xl font-bold">{{ $application->job->company->name }}</h3>
                                <p class="text-xs text-on-surface-variant">{{ $application->job->title }}</p>
                            </div>
                            <button type="button" onclick="document.getElementById('review-modal-{{ $application->id }}').close()" class="icon-button !size-9">
                                <x-icon name="close" />
                            </button>
                        </div>

                        <form method="POST" action="{{ route('applications.review', $application) }}" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <label class="label">Beri Rating Bintang (1 - 5)</label>
                                <div class="flex items-center gap-3">
                                    <select class="input" name="score" required>
                                        <option value="5" @selected(($workerReview?->score ?? 5) == 5)>⭐⭐⭐⭐⭐ 5 - Sangat Memuaskan</option>
                                        <option value="4" @selected(($workerReview?->score ?? 5) == 4)>⭐⭐⭐⭐ 4 - Bagus & Profesional</option>
                                        <option value="3" @selected(($workerReview?->score ?? 5) == 3)>⭐⭐⭐ 3 - Cukup Baik</option>
                                        <option value="2" @selected(($workerReview?->score ?? 5) == 2)>⭐⭐ 2 - Kurang Memuaskan</option>
                                        <option value="1" @selected(($workerReview?->score ?? 5) == 1)>⭐ 1 - Sangat Mengecewakan</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="label">Judul Ulasan <span class="optional">Opsional</span></label>
                                <input class="input" name="title" value="{{ old('title', $workerReview?->review?->title) }}" placeholder="Contoh: Pembayaran cepat & lingkungan kerja ramah">
                            </div>

                            <div>
                                <label class="label">Pengalaman & Masukan</label>
                                <textarea class="input min-h-24 resize-y" name="body" placeholder="Tuliskan ulasan jujur tentang pengalaman kerjamu bersama perusahaan ini..." required>{{ old('body', $workerReview?->review?->body) }}</textarea>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-black/10 pt-4">
                                <button type="button" onclick="document.getElementById('review-modal-{{ $application->id }}').close()" class="btn-ghost">Batal</button>
                                <button type="submit" class="btn-primary">Kirim Ulasan</button>
                            </div>
                        </form>
                    </div>
                </dialog>
                @endif
            @empty
                <div class="card col-span-full py-16 text-center">
                    <div class="mx-auto grid size-16 place-items-center rounded-full bg-primary-soft text-primary">
                        <x-icon name="description" class="size-8" />
                    </div>
                    <h3 class="mt-5 font-display text-2xl font-bold">Belum ada pekerjaan</h3>
                    <p class="mt-2 text-sm text-on-surface-variant">Pekerjaan yang telah diterima oleh perusahaan akan otomatis tampil di halaman ini.</p>
                    <a href="{{ route('jobs.index') }}" class="btn-primary compact mt-6">Cari Lowongan</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $jobs->links() }}
        </div>
    </div>
</section>
@endsection


