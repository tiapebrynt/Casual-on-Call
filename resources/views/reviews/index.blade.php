@extends('layouts.app')
@section('title', 'Reviews & Ratings')
@section('content')
<section class="mx-auto max-w-[1200px] px-4 py-10 sm:px-6 lg:px-10 lg:py-14">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
            <span class="eyebrow">REPUTATION SYSTEM</span>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight sm:text-4xl">Reviews & Ratings</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Sistem reputasi dua arah untuk membangun ekosistem kerja casual yang transparan dan tepercaya.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->hasRole('worker'))
                <a href="{{ route('jobs.my') }}" class="btn-primary compact">Beri Ulasan di Pekerjaan Saya</a>
            @else
                <a href="{{ route('applications.index') }}" class="btn-primary compact">Beri Ulasan di Daftar Pelamar</a>
            @endif
        </div>
    </div>

    <!-- HOW REVIEWS WORK EXPLANATION CARD -->
    <div class="mt-8 overflow-hidden rounded-3xl border border-black/5 bg-gradient-to-br from-white via-surface to-[#fff4f6] p-6 shadow-sm lg:p-8">
        <div class="flex items-start gap-4">
            <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-primary text-white shadow-md">
                <x-icon name="sparkles" class="size-6" />
            </div>
            <div>
                <h2 class="font-display text-lg font-bold text-secondary">Cara Kerja Review & Rating di CoC</h2>
                <p class="mt-1 text-xs text-on-surface-variant sm:text-sm">Platform CoC menerapkan sistem penilaian timbal-balik (2-way rating) setelah pekerjaan selesai:</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-xs">
            <div class="rounded-2xl bg-white p-4 border border-black/5 shadow-xs">
                <div class="font-bold text-primary flex items-center gap-1.5">
                    <span class="grid size-5 place-items-center rounded-full bg-primary-soft text-[10px]">1</span>
                    <span>Pekerjaan Selesai</span>
                </div>
                <p class="mt-2 text-on-surface-variant leading-5">Setelah shift terlaksana, perusahaan menandai status lamaran menjadi <b>Completed (Selesai)</b>.</p>
            </div>

            <div class="rounded-2xl bg-white p-4 border border-black/5 shadow-xs">
                <div class="font-bold text-primary flex items-center gap-1.5">
                    <span class="grid size-5 place-items-center rounded-full bg-primary-soft text-[10px]">2</span>
                    <span>Worker Menilai Perusahaan</span>
                </div>
                <p class="mt-2 text-on-surface-variant leading-5">Worker memberi 1-5 bintang dan ulasan mengenai lingkungan kerja dan kejelasan instruksi.</p>
            </div>

            <div class="rounded-2xl bg-white p-4 border border-black/5 shadow-xs">
                <div class="font-bold text-primary flex items-center gap-1.5">
                    <span class="grid size-5 place-items-center rounded-full bg-primary-soft text-[10px]">3</span>
                    <span>Perusahaan Menilai Worker</span>
                </div>
                <p class="mt-2 text-on-surface-variant leading-5">Perusahaan memberi 1-5 bintang dan ulasan mengenai kedisiplinan, skill, dan etika kerja worker.</p>
            </div>

            <div class="rounded-2xl bg-white p-4 border border-black/5 shadow-xs">
                <div class="font-bold text-primary flex items-center gap-1.5">
                    <span class="grid size-5 place-items-center rounded-full bg-primary-soft text-[10px]">4</span>
                    <span>Tingkatkan Reputasi</span>
                </div>
                <p class="mt-2 text-on-surface-variant leading-5">Nilai rata-rata langsung tampil pada profil publik dan mempermudah pencocokan kerja selanjutnya.</p>
            </div>
        </div>
    </div>

    <!-- SCORE SUMMARY & BREAKDOWN -->
    <div class="mt-8 grid gap-6 lg:grid-cols-[320px_1fr]">
        <div class="card text-center flex flex-col justify-center items-center !p-8">
            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Rating Rata-rata</p>
            <strong class="mt-3 block font-display text-6xl font-extrabold text-secondary">{{ number_format($average, 1) }}</strong>
            
            <div class="mt-3 flex items-center justify-center gap-1 text-xl text-amber-500">
                @for($i = 1; $i <= 5; $i++)
                    <span>{{ $i <= round($average) ? '★' : '☆' }}</span>
                @endfor
            </div>
            
            <p class="mt-3 text-xs text-on-surface-variant">Berdasarkan {{ $totalReceived }} ulasan diterima</p>
        </div>

        <div class="card !p-6 flex flex-col justify-center">
            <h3 class="font-display text-base font-bold mb-4">Distribusi Bintang</h3>
            <div class="space-y-2.5">
                @foreach($starBreakdown as $star => $data)
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-12 font-semibold text-secondary flex items-center gap-1">
                            <span>{{ $star }}</span>
                            <span class="text-amber-500">★</span>
                        </span>
                        <div class="h-2.5 flex-1 rounded-full bg-surface-low overflow-hidden">
                            <div class="h-full rounded-full bg-amber-400 transition-all duration-500" style="width: {{ $data['percentage'] }}%"></div>
                        </div>
                        <span class="w-10 text-right text-on-surface-variant">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="mt-10 flex border-b border-black/10 gap-4">
        <a href="{{ route('reviews.index', ['tab' => 'received']) }}" 
           class="pb-3 text-sm font-bold border-b-2 transition-all {{ ($tab ?? 'received') === 'received' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-secondary' }}">
            <span>Ulasan Diterima</span>
            <span class="ml-1.5 rounded-full bg-primary-soft px-2 py-0.5 text-xs text-primary font-bold">{{ $totalReceived }}</span>
        </a>
        <a href="{{ route('reviews.index', ['tab' => 'given']) }}" 
           class="pb-3 text-sm font-bold border-b-2 transition-all {{ ($tab ?? 'received') === 'given' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-secondary' }}">
            <span>Ulasan Diberikan</span>
            <span class="ml-1.5 rounded-full bg-surface-container px-2 py-0.5 text-xs text-neutral font-bold">{{ $totalGiven }}</span>
        </a>
    </div>

    <!-- REVIEW LIST -->
    <div class="mt-6 grid gap-5 md:grid-cols-2">
        @forelse($reviews as $rating)
            @php
                $targetName = ($tab === 'given') ? ($rating->reviewee->name ?? 'User') : ($rating->reviewer->name ?? 'User');
            @endphp
            <article class="card flex flex-col justify-between hover:border-primary/20 transition-all">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="grid size-11 place-items-center rounded-full bg-primary-soft font-bold text-primary">
                                {{ strtoupper(substr($targetName, 0, 1)) }}
                            </div>
                            <div>
                                <b class="block text-sm text-secondary">{{ $targetName }}</b>
                                <p class="text-[11px] text-on-surface-variant">{{ $rating->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200/50">
                            <span>★</span>
                            <span>{{ $rating->score }}.0</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-black/5">
                        <span class="text-xs font-bold text-primary block">
                            {{ $rating->application->job->title ?? 'Pekerjaan Casual' }}
                        </span>
                        <h3 class="mt-1 font-display text-base font-bold text-secondary">
                            {{ $rating->review?->title ?: 'Ulasan Pekerjaan' }}
                        </h3>
                        <p class="mt-2 text-xs sm:text-sm leading-6 text-on-surface-variant whitespace-pre-line">
                            "{{ $rating->review?->body ?: 'Rating diberikan setelah pekerjaan berhasil diselesaikan.' }}"
                        </p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-black/5 flex items-center justify-between text-xs text-on-surface-variant">
                    <span>Status: Terverifikasi</span>
                    <span class="text-primary font-semibold">{{ $rating->score >= 4 ? '⭐ Sangat Puas' : '⭐ Selesai' }}</span>
                </div>
            </article>
        @empty
            <div class="card col-span-full py-16 text-center">
                <div class="mx-auto grid size-14 place-items-center rounded-full bg-primary-soft text-primary">
                    <x-icon name="star" class="size-7" />
                </div>
                <h3 class="mt-4 font-display text-xl font-bold">Belum ada ulasan di tab ini</h3>
                <p class="mt-1 text-xs sm:text-sm text-on-surface-variant">
                    {{ ($tab ?? 'received') === 'received' 
                        ? 'Ulasan yang kamu terima dari rekan kerja atau perusahaan akan tampil di sini.' 
                        : 'Ulasan yang kamu kirimkan untuk rekan kerja atau perusahaan akan tampil di sini.' }}
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $reviews->links() }}
    </div>
</section>
@endsection


