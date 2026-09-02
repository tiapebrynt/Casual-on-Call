@extends('layouts.app')
@section('title', 'Cari Lowongan')
@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-[#fff8f8] via-surface to-[#e9ffff]">
    <div class="absolute -left-32 top-0 size-96 rounded-full bg-primary/5 blur-3xl"></div>
    <div class="mx-auto max-w-[1100px] px-4 py-12 text-center sm:px-6 lg:py-16">
        <p class="text-xs font-bold uppercase tracking-[.2em] text-primary">Peluang terverifikasi</p>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight sm:text-5xl lg:text-6xl">Temukan Peran Berikutnya.</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-on-surface-variant sm:text-lg">Jelajahi peluang kerja casual terpercaya dari perusahaan terbaik di Indonesia.</p>
        
        <form method="GET" action="{{ route('jobs.index') }}" class="mx-auto mt-8 grid gap-2.5 rounded-[28px] bg-white p-2.5 shadow-[0_22px_50px_-24px_rgba(177,0,44,.35)] sm:grid-cols-2 lg:grid-cols-[1.3fr_1fr_1fr_1.1fr_auto]">
            <label class="relative flex items-center">
                <x-icon name="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral size-5" />
                <input class="input !bg-transparent pl-12" name="search" value="{{ request('search') }}" placeholder="Posisi atau kata kunci">
            </label>
            <label class="relative flex items-center">
                <x-icon name="location_on" class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral size-5" />
                <input class="input !bg-transparent pl-12" name="location" value="{{ request('location') }}" placeholder="Lokasi (Kota)">
            </label>
            <select class="input !bg-transparent" name="category">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select class="input !bg-transparent" name="rate_range">
                <option value="">Semua rentang upah</option>
                <option value="0-100000" @selected(request('rate_range') === '0-100000')>&lt; Rp100.000 / hari</option>
                <option value="100000-250000" @selected(request('rate_range') === '100000-250000')>Rp100.000 - Rp250.000</option>
                <option value="250000-500000" @selected(request('rate_range') === '250000-500000')>Rp250.000 - Rp500.000</option>
                <option value="500000-1000000" @selected(request('rate_range') === '500000-1000000')>Rp500.000 - Rp1.000.000</option>
                <option value="1000000-" @selected(request('rate_range') === '1000000-')>&gt; Rp1.000.000 / hari</option>
            </select>
            <button class="btn-primary sm:col-span-2 lg:col-span-1">
                <x-icon name="search" class="size-4" />
                <span>Cari Job</span>
            </button>
        </form>
    </div>
</section>

<section class="mx-auto max-w-[1280px] px-4 py-12 sm:px-6 lg:px-10">
    <div class="flex flex-col gap-8 lg:flex-row">
        <aside class="h-fit lg:sticky lg:top-28 lg:w-64 lg:shrink-0">
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-black/5">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold">Filter Pencarian</h2>
                    <a href="{{ route('jobs.index') }}" class="text-xs font-semibold text-primary hover:underline">Reset</a>
                </div>
                
                <div class="mt-6 border-t border-[#eadadb] pt-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Kategori</p>
                    <div class="mt-3 space-y-2">
                        @foreach($categories as $category)
                            <a href="{{ route('jobs.index', array_merge(request()->query(), ['category' => $category->id])) }}" 
                               class="flex items-center justify-between rounded-xl px-3 py-2 text-sm transition-colors {{ request('category') == $category->id ? 'bg-primary-soft font-bold text-primary' : 'hover:bg-surface-low text-secondary' }}">
                                <span>{{ $category->name }}</span>
                                <x-icon name="chevron_right" class="size-4" />
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 border-t border-[#eadadb] pt-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Rentang Upah</p>
                    <div class="mt-3 space-y-2">
                        @php
                            $ranges = [
                                '' => 'Semua Upah',
                                '0-100000' => '< Rp100.000',
                                '100000-250000' => 'Rp100rb - Rp250rb',
                                '250000-500000' => 'Rp250rb - Rp500rb',
                                '500000-1000000' => 'Rp500rb - Rp1jt',
                                '1000000-' => '> Rp1.000.000',
                            ];
                        @endphp
                        @foreach($ranges as $rValue => $rLabel)
                            <a href="{{ route('jobs.index', array_merge(request()->query(), ['rate_range' => $rValue ?: null])) }}" 
                               class="flex items-center justify-between rounded-xl px-3 py-2 text-sm transition-colors {{ request('rate_range', '') === $rValue ? 'bg-primary-soft font-bold text-primary' : 'hover:bg-surface-low text-secondary' }}">
                                <span>{{ $rLabel }}</span>
                                @if(request('rate_range', '') === $rValue)
                                    <x-icon name="check_circle" class="size-4 text-primary" />
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-7 rounded-2xl bg-surface-low p-5 text-center">
                    <div class="mx-auto grid size-10 place-items-center rounded-full bg-primary-soft text-primary">
                        <x-icon name="notifications" class="size-5" />
                    </div>
                    <p class="mt-2 text-sm font-semibold">Job Alerts</p>
                    <p class="mt-1 text-xs text-on-surface-variant">Dapatkan notifikasi pekerjaan casual terbaru.</p>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <p class="text-base text-on-surface-variant">
                    Menampilkan <b class="font-bold text-secondary">{{ number_format($jobs->total()) }}</b> lowongan aktif
                </p>
                @if(request('search') || request('category') || request('location') || request('rate_range'))
                    <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-1.5 rounded-full bg-surface-container px-3.5 py-1.5 text-xs font-semibold text-neutral hover:text-primary">
                        <span>Hapus Filter</span>
                        <x-icon name="close" class="size-3.5" />
                    </a>
                @endif
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                @forelse($jobs as $job)
                    <article class="group relative flex flex-col justify-between rounded-3xl border border-transparent bg-white p-6 shadow-[0_20px_50px_-30px_rgba(18,18,18,.15)] transition-all hover:-translate-y-1 hover:border-primary/20 hover:shadow-lg">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="grid size-13 place-items-center rounded-2xl bg-primary-soft font-display text-xl font-bold text-primary">
                                    {{ strtoupper(substr($job->company->name, 0, 1)) }}
                                </div>
                                <span class="badge {{ $job->payment_type === 'project' ? '!bg-amber-50 !text-amber-700' : '' }}">
                                    {{ $job->payment_type === 'project' ? 'Proyek' : 'Harian' }}
                                </span>
                            </div>

                            <a href="{{ route('companies.show', $job->company) }}" class="mt-4 block text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:text-primary">
                                {{ $job->company->name }}
                            </a>

                            <h2 class="mt-1.5 font-display text-xl font-bold text-secondary group-hover:text-primary transition-colors">
                                <a href="{{ route('jobs.show', $job) }}" class="focus:outline-none">
                                    {{ $job->title }}
                                </a>
                            </h2>

                            <p class="mt-2 flex items-center gap-1.5 text-sm text-on-surface-variant">
                                <x-icon name="location_on" class="size-4 shrink-0 text-neutral" />
                                <span>{{ $job->location }}</span>
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="badge">{{ $job->category->name }}</span>
                                <span class="rounded-full bg-surface-container px-3 py-1 text-xs font-semibold text-neutral">
                                    {{ $job->vacancies }} posisi
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-black/5 pt-4">
                            <div class="flex items-end justify-between">
                                <div>
                                    <span class="block text-xs text-on-surface-variant">Upah</span>
                                    <strong class="font-display text-lg font-bold text-primary">
                                        Rp{{ number_format($job->daily_rate, 0, ',', '.') }}
                                    </strong>
                                    <span class="text-xs text-on-surface-variant">/{{ $job->payment_type === 'project' ? 'proyek' : 'hari' }}</span>
                                </div>
                                <a href="{{ route('jobs.show', $job) }}" class="inline-flex items-center gap-1 text-sm font-bold text-primary hover:gap-2 transition-all">
                                    <span>Detail</span>
                                    <x-icon name="arrow_forward" class="size-4" />
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card col-span-full py-16 text-center">
                        <div class="mx-auto grid size-16 place-items-center rounded-full bg-primary-soft text-primary">
                            <x-icon name="search_off" class="size-8" />
                        </div>
                        <h2 class="mt-5 font-display text-2xl font-bold">Lowongan tidak ditemukan</h2>
                        <p class="mt-2 text-sm text-on-surface-variant">Coba ubah kata kunci pencarian atau sesuaikan rentang upah yang dipilih.</p>
                        <a href="{{ route('jobs.index') }}" class="btn-primary compact mt-6">Reset Pencarian</a>
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</section>
@endsection



