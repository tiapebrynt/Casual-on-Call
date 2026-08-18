@extends('layouts.app')
@section('title', 'Form Lamaran')
@section('content')
<section class="bg-gradient-to-br from-[#fff7f8] to-[#eaffff]">
    <div class="mx-auto max-w-5xl px-5 py-12 lg:px-10 lg:py-16">
        <a href="{{ route('jobs.show', $job) }}" class="text-sm font-bold text-primary">&larr; Kembali ke detail lowongan</a>
        <p class="mt-8 text-xs font-bold uppercase tracking-[.18em] text-primary">Formulir lamaran</p>
        <h1 class="mt-3 font-display text-3xl font-bold lg:text-5xl">Lamar {{ $job->title }}</h1>
        <p class="mt-3 text-on-surface-variant">{{ $job->company->name }} · {{ $job->location }}</p>
    </div>
</section>
<section class="mx-auto grid max-w-5xl gap-8 px-5 py-12 lg:grid-cols-[1fr_320px] lg:px-10">
    <div class="card">
        @if($alreadyApplied)
            <div class="alert-success"><x-icon name="check_circle" />Kamu sudah pernah melamar lowongan ini.</div>
            <a href="{{ route('applications.index') }}" class="btn-primary mt-6">Lihat status lamaran</a>
        @else
            <form method="POST" action="{{ route('applications.store', $job) }}">
                @csrf
                <h2 class="font-display text-2xl font-bold">Perkenalkan dirimu</h2>
                <p class="mt-2 text-sm leading-6 text-on-surface-variant">Ceritakan pengalaman, keterampilan, dan alasan kamu cocok untuk posisi ini.</p>
                <div class="mt-7">
                    <label class="label" for="cover_letter">Pesan lamaran</label>
                    <textarea id="cover_letter" name="cover_letter" class="input min-h-52 resize-y" maxlength="3000" required placeholder="Contoh: Saya memiliki pengalaman sebagai barista selama 2 tahun dan terbiasa bekerja dalam shift...">{{ old('cover_letter') }}</textarea>
                    <div class="mt-2 flex justify-between gap-4 text-xs text-on-surface-variant"><span>Maksimal 3.000 karakter</span><span>CV dari profil akan ikut tersedia.</span></div>
                </div>
                <label class="choice mt-6 items-start"><input type="checkbox" required class="mt-1 accent-primary"><span><span class="block text-sm">Saya memastikan informasi yang diberikan benar</span><span class="mt-1 block text-xs font-normal text-on-surface-variant">Periksa kembali detail lowongan dan jadwal sebelum mengirim.</span></span></label>
                <button class="btn-primary mt-7 w-full" type="submit">Kirim lamaran</button>
            </form>
        @endif
    </div>
    <aside class="card h-fit !p-6 lg:sticky lg:top-28">
        <span class="badge">{{ $job->category->name }}</span>
        <h2 class="mt-4 font-display text-xl font-bold">{{ $job->title }}</h2>
        <dl class="mt-6 space-y-4 text-sm">
            <div><dt class="text-on-surface-variant">Bayaran</dt><dd class="mt-1 font-bold">Rp{{ number_format($job->daily_rate, 0, ',', '.') }}/hari</dd></div>
            <div><dt class="text-on-surface-variant">Jadwal</dt><dd class="mt-1 font-bold">{{ $job->starts_at->format('d M') }} – {{ $job->ends_at->format('d M Y') }}</dd></div>
            <div><dt class="text-on-surface-variant">Batas lamaran</dt><dd class="mt-1 font-bold">{{ $job->application_deadline->format('d M Y') }}</dd></div>
        </dl>
    </aside>
</section>
@endsection
