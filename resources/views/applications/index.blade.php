@extends('layouts.app')
@section('title', auth()->user()->hasRole('worker') ? 'Lamaran Saya' : 'Daftar Pelamar')
@section('content')
<section class="mx-auto max-w-[1280px] px-4 py-10 sm:px-6 lg:px-10 lg:py-14">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <span class="eyebrow">{{ auth()->user()->hasRole('worker') ? 'WORKER WORKSPACE' : 'COMPANY WORKSPACE' }}</span>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight sm:text-4xl">
                {{ auth()->user()->hasRole('worker') ? 'Lamaran Saya' : 'Daftar Pelamar Masuk' }}
            </h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                {{ auth()->user()->hasRole('worker') 
                    ? 'Pantau status seleksi, feedback perusahaan, dan ulasan pekerjaanmu.' 
                    : 'Kelola dan seleksi kandidat pelamar yang mendaftar pada lowongan perusahaan.' }}
            </p>
        </div>
        @if(auth()->user()->hasRole('worker'))
            <a href="{{ route('jobs.index') }}" class="btn-primary">
                <x-icon name="search" class="size-4" />
                <span>Cari Lowongan Baru</span>
            </a>
        @else
            <a href="{{ route('jobs.manage') }}" class="btn-primary">
                <x-icon name="plus" class="size-4" />
                <span>Kelola Lowongan</span>
            </a>
        @endif
    </div>

    <!-- STATUS FILTER PILLS -->
    <div class="mt-8 flex gap-2.5 overflow-x-auto border-b border-black/5 pb-4 scrollbar-none">
        @php
            $statuses = [
                '' => 'Semua',
                'pending' => 'Pending',
                'accepted' => 'Diterima',
                'completed' => 'Selesai',
                'rejected' => 'Ditolak',
            ];
        @endphp
        @foreach($statuses as $val => $lbl)
            <a href="{{ route('applications.index', $val ? ['status' => $val] : []) }}" 
               class="whitespace-nowrap rounded-full px-5 py-2.5 text-xs font-bold transition-all {{ request('status', '') === $val ? 'bg-primary text-white shadow-sm' : 'bg-surface-container text-neutral hover:bg-black/10' }}">
                <span>{{ $lbl }}</span>
            </a>
        @endforeach
    </div>

    <!-- APPLICATIONS LIST -->
    <div class="mt-8 space-y-5">
        @forelse($applications as $application)
            @php
                $userReview = $application->ratings->firstWhere('reviewer_id', auth()->id());
                $isWorker = auth()->user()->hasRole('worker');
            @endphp
            <article class="rounded-3xl bg-white p-5 sm:p-7 shadow-sm border border-black/5 hover:border-primary/20 transition-all">
                <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-start">
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-primary-soft font-display text-xl font-bold text-primary">
                            {{ strtoupper(substr($isWorker ? $application->job->company->name : $application->worker->user->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($application->status === 'accepted')
                                    <span class="badge-success">DITERIMA</span>
                                @elseif($application->status === 'completed')
                                    <span class="badge-neutral">SELESAI</span>
                                @elseif($application->status === 'rejected')
                                    <span class="badge-danger">DITOLAK</span>
                                @elseif($application->status === 'pending')
                                    <span class="badge-warning">MENUNGGU REVIEW</span>
                                @else
                                    <span class="badge">{{ strtoupper($application->status) }}</span>
                                @endif
                                <span class="text-xs text-on-surface-variant">&middot; {{ $application->created_at->diffForHumans() }}</span>
                            </div>

                            <h2 class="mt-2 font-display text-xl sm:text-2xl font-bold text-secondary hover:text-primary transition-colors">
                                <a href="{{ route('jobs.show', $application->job) }}">
                                    {{ $application->job->title }}
                                </a>
                            </h2>

                            <p class="mt-1 text-xs sm:text-sm text-on-surface-variant">
                                {{ $application->job->company->name }} &middot; {{ $application->job->location }}
                            </p>

                            @if(!$isWorker)
                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                    <span class="font-semibold text-secondary">Pelamar:</span>
                                    <button type="button" onclick="document.getElementById('applicant-modal-{{ $application->id }}').showModal()" class="font-bold text-primary hover:underline inline-flex items-center gap-1">
                                        <span>{{ $application->worker->user->name }}</span>
                                        <x-icon name="info" class="size-3.5" />
                                    </button>
                                    <span class="text-on-surface-variant">({{ $application->worker->city ?? 'Indonesia' }})</span>
                                </div>
                            @endif

                            @if($application->cover_letter)
                                <p class="mt-3 text-xs text-on-surface-variant bg-surface-low rounded-xl p-3 line-clamp-2">
                                    <span class="font-semibold text-secondary">Surat Lamaran:</span> "{{ $application->cover_letter }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 self-start shrink-0">
                        @if(!$isWorker)
                            <button type="button" onclick="document.getElementById('applicant-modal-{{ $application->id }}').showModal()" class="btn-ghost compact !text-xs !bg-surface-container">
                                <x-icon name="person" class="size-3.5" />
                                <span>Profil Pelamar</span>
                            </button>
                            @if($application->worker->cv_path)
                                <a class="btn-ghost compact !text-xs" href="{{ route('applications.cv.download', $application) }}">
                                    <x-icon name="description" class="size-3.5" />
                                    <span>Unduh CV</span>
                                </a>
                            @endif
                        @endif

                        <form method="POST" action="{{ route('conversations.start', $application) }}" class="inline">
                            @csrf
                            <button class="btn-ghost compact !text-xs" type="submit">
                                <x-icon name="messages" class="size-3.5" />
                                <span>Pesan</span>
                            </button>
                        </form>

                        @if(!$isWorker && $application->status === 'pending')
                            <form method="POST" action="{{ route('applications.update', $application) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="accepted">
                                <button class="btn-primary compact !text-xs !bg-[#007d7d] hover:!bg-[#006262]" type="submit">
                                    <x-icon name="check" class="size-3.5" />
                                    <span>Terima</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('applications.update', $application) }}" class="inline" onsubmit="return confirm('Tolak lamaran kandidat ini?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button class="btn-ghost compact !text-xs !text-rose-600 hover:!bg-rose-50" type="submit">
                                    <x-icon name="close" class="size-3.5" />
                                    <span>Tolak</span>
                                </button>
                            </form>
                        @elseif(!$isWorker && $application->status === 'accepted')
                            <form method="POST" action="{{ route('applications.update', $application) }}" class="inline" onsubmit="return confirm('Tandai pekerjaan ini telah selesai?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button class="btn-primary compact !text-xs" type="submit">
                                    <x-icon name="check_circle" class="size-3.5" />
                                    <span>Tandai Selesai</span>
                                </button>
                            </form>
                        @elseif($isWorker && $application->status === 'pending')
                            <form method="POST" action="{{ route('applications.update', $application) }}" class="inline" onsubmit="return confirm('Batalkan lamaran ini?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button class="btn-ghost compact !text-xs text-rose-600" type="submit">
                                    <span>Batalkan Lamaran</span>
                                </button>
                            </form>
                        @endif

                        @if($application->status === 'completed')
                            @if($userReview)
                                <button type="button" onclick="document.getElementById('review-modal-{{ $application->id }}').showModal()" class="btn-ghost compact !text-xs !bg-amber-50 !text-amber-700">
                                    <span>⭐ {{ $userReview->score }}/5 Diulas</span>
                                </button>
                            @else
                                <button type="button" onclick="document.getElementById('review-modal-{{ $application->id }}').showModal()" class="btn-primary compact !text-xs">
                                    <span>⭐ Beri Ulasan</span>
                                </button>
                            @endif
                        @endif

                        @if($application->payment)
                            <a class="btn-ghost compact !text-xs !bg-surface-container" href="{{ route('payments.show', $application->payment) }}">
                                <span>Invoice</span>
                            </a>
                        @endif

                        <a class="icon-button !size-9" href="{{ route('jobs.show', $application->job) }}" title="Lihat detail lowongan">
                            <x-icon name="arrow_forward" class="size-4" />
                        </a>
                    </div>
                </div>

                <!-- MOTIVATIONAL QUOTE CARD FOR REJECTED CANDIDATE -->
                @if($isWorker && $application->status === 'rejected')
                    <div class="mt-5 rounded-2xl bg-gradient-to-r from-[#fff5f6] to-[#fff8f2] p-4 border border-primary/15 flex items-start gap-3">
                        <div class="grid size-9 shrink-0 place-items-center rounded-full bg-primary/10 text-primary">
                            <x-icon name="sparkles" class="size-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-primary">Kata Mutiara & Motivasi CoC</span>
                            <p class="mt-1 text-xs sm:text-sm text-secondary italic leading-relaxed">
                                "{{ $application->rejection_quote }}"
                            </p>
                        </div>
                    </div>
                @endif
            </article>

            <!-- APPLICANT PROFILE MODAL (FOR COMPANY) -->
            @if(!$isWorker)
            <dialog id="applicant-modal-{{ $application->id }}" class="rounded-3xl p-0 backdrop:bg-black/60 backdrop:backdrop-blur-sm w-full max-w-lg shadow-2xl">
                <div class="bg-white p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-black/10 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="grid size-12 place-items-center rounded-2xl bg-primary-soft font-display text-xl font-bold text-primary">
                                {{ strtoupper(substr($application->worker->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-display text-xl font-bold">{{ $application->worker->user->name }}</h3>
                                <p class="text-xs text-on-surface-variant">{{ $application->worker->headline ?? 'Casual Worker' }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('applicant-modal-{{ $application->id }}').close()" class="icon-button !size-9">
                            <x-icon name="close" />
                        </button>
                    </div>

                    <div class="mt-5 space-y-4 text-xs sm:text-sm">
                        <div class="grid grid-cols-2 gap-3 bg-surface-low p-4 rounded-2xl">
                            <div>
                                <span class="text-on-surface-variant block text-xs">Kota Domisili</span>
                                <b class="text-secondary">{{ $application->worker->city ?? 'Indonesia' }}</b>
                            </div>
                            <div>
                                <span class="text-on-surface-variant block text-xs">Pengalaman</span>
                                <b class="text-secondary">{{ $application->worker->experience_years }} tahun</b>
                            </div>
                            <div>
                                <span class="text-on-surface-variant block text-xs">Nomor Telepon</span>
                                <b class="text-secondary">{{ $application->worker->user->phone ?? '-' }}</b>
                            </div>
                            <div>
                                <span class="text-on-surface-variant block text-xs">Email</span>
                                <b class="text-secondary">{{ $application->worker->user->email }}</b>
                            </div>
                        </div>

                        @if($application->worker->bio)
                            <div>
                                <span class="label">Tentang Worker</span>
                                <p class="text-on-surface-variant leading-relaxed bg-surface-low p-3.5 rounded-2xl text-xs">{{ $application->worker->bio }}</p>
                            </div>
                        @endif

                        @if($application->cover_letter)
                            <div>
                                <span class="label">Surat Lamaran</span>
                                <p class="text-on-surface-variant leading-relaxed bg-surface-low p-3.5 rounded-2xl text-xs">{{ $application->cover_letter }}</p>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 pt-3 border-t border-black/10">
                            @if($application->worker->cv_path)
                                <a href="{{ route('applications.cv.download', $application) }}" class="btn-primary compact flex-1 text-center">
                                    <x-icon name="description" class="size-4" />
                                    <span>Unduh CV Pelamar</span>
                                </a>
                            @endif
                            <button type="button" onclick="document.getElementById('applicant-modal-{{ $application->id }}').close()" class="btn-ghost compact">Tutup</button>
                        </div>
                    </div>
                </div>
            </dialog>
            @endif

            <!-- REVIEW MODAL -->
            @if($application->status === 'completed')
            <dialog id="review-modal-{{ $application->id }}" class="rounded-3xl p-0 backdrop:bg-black/60 backdrop:backdrop-blur-sm w-full max-w-lg shadow-2xl">
                <div class="bg-white p-6 sm:p-8">
                    <div class="flex items-center justify-between border-b border-black/10 pb-4">
                        <div>
                            <span class="badge">{{ $isWorker ? 'REVIEW PERUSAHAAN' : 'REVIEW WORKER' }}</span>
                            <h3 class="mt-1 font-display text-xl font-bold">
                                {{ $isWorker ? $application->job->company->name : $application->worker->user->name }}
                            </h3>
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
                            <select class="input" name="score" required>
                                <option value="5" @selected(($userReview?->score ?? 5) == 5)>⭐⭐⭐⭐⭐ 5 - Sangat Memuaskan</option>
                                <option value="4" @selected(($userReview?->score ?? 5) == 4)>⭐⭐⭐⭐ 4 - Bagus & Profesional</option>
                                <option value="3" @selected(($userReview?->score ?? 5) == 3)>⭐⭐⭐ 3 - Cukup Baik</option>
                                <option value="2" @selected(($userReview?->score ?? 5) == 2)>⭐⭐ 2 - Kurang Memuaskan</option>
                                <option value="1" @selected(($userReview?->score ?? 5) == 1)>⭐ 1 - Sangat Mengecewakan</option>
                            </select>
                        </div>

                        <div>
                            <label class="label">Judul Ulasan <span class="optional">Opsional</span></label>
                            <input class="input" name="title" value="{{ old('title', $userReview?->review?->title) }}" placeholder="Contoh: Kerja sama luar biasa dan tepat waktu">
                        </div>

                        <div>
                            <label class="label">Pengalaman & Masukan</label>
                            <textarea class="input min-h-24 resize-y" name="body" placeholder="Tuliskan ulasan pengalaman kerjamu secara jujur..." required>{{ old('body', $userReview?->review?->body) }}</textarea>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-black/10 pt-4">
                            <button type="button" onclick="document.getElementById('review-modal-{{ $application->id }}').close()" class="btn-ghost">Batal</button>
                            <button type="submit" class="btn-primary">Simpan Ulasan</button>
                        </div>
                    </form>
                </div>
            </dialog>
            @endif

        @empty
            <div class="card py-16 text-center">
                <div class="mx-auto grid size-16 place-items-center rounded-full bg-primary-soft text-primary">
                    <x-icon name="description" class="size-8" />
                </div>
                <h2 class="mt-4 font-display text-2xl font-bold">Belum ada lamaran</h2>
                <p class="mt-1 text-sm text-on-surface-variant">Aktivitas lamaran kerja akan otomatis tercatat di sini.</p>
                <a href="{{ route('jobs.index') }}" class="btn-primary compact mt-6">Cari Pekerjaan</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $applications->links() }}
    </div>
</section>
@endsection


