@extends('layouts.app')
@section('title', 'Profil Worker')
@section('content')
<section class="mx-auto max-w-5xl px-5 py-12">
    <span class="eyebrow">PROFIL WORKER</span>
    <div class="mt-4 flex flex-col justify-between gap-4 md:flex-row md:items-end"><div><h1 class="font-display text-4xl font-bold">Lengkapi profilmu</h1><p class="mt-2 text-neutral">CV bersifat opsional. Kamu tetap bisa melamar tanpa mengunggah CV.</p></div><span class="badge">{{ ucfirst($worker->verification_status) }}</span></div>
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card mt-8 grid gap-6 md:grid-cols-2">
        @csrf @method('PUT')
        <div><label class="label">Nama lengkap</label><input class="input" name="name" value="{{ old('name', auth()->user()->name) }}" required></div>
        <div><label class="label">Nomor telepon</label><input class="input" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required></div>
        <div><label class="label">Headline profesional <span class="optional">Opsional</span></label><input class="input" name="headline" value="{{ old('headline', $worker->headline) }}" placeholder="Contoh: Barista & Event Crew"></div>
        <div><label class="label">Kota</label><input class="input" name="city" value="{{ old('city', $worker->city) }}" required></div>
        <div><label class="label">Pengalaman (tahun)</label><input class="input" type="number" min="0" max="60" name="experience_years" value="{{ old('experience_years', $worker->experience_years) }}" required></div>
        <div><label class="label">Link portfolio <span class="optional">Opsional</span></label><input class="input" type="url" name="portfolio_url" value="{{ old('portfolio_url', $worker->portfolio_url) }}" placeholder="https://..."></div>
        <div class="md:col-span-2"><label class="label">Tentang saya <span class="optional">Opsional</span></label><textarea class="input min-h-32" name="bio" placeholder="Ceritakan pengalaman dan keahlianmu">{{ old('bio', $worker->bio) }}</textarea></div>
        <div class="md:col-span-2 rounded-3xl border-2 border-dashed border-primary/25 bg-primary-soft p-6"><div class="flex flex-col justify-between gap-5 md:flex-row md:items-center"><div><label class="font-display text-lg font-bold">Curriculum Vitae <span class="optional">Opsional</span></label><p class="mt-1 text-sm text-neutral">PDF, DOC, atau DOCX &middot; maksimal 5 MB. File disimpan privat.</p></div><input class="file-input" type="file" name="cv" accept=".pdf,.doc,.docx"></div>@if($worker->cv_path)<div class="mt-5 flex flex-wrap items-center gap-3 rounded-2xl bg-white p-4"><span class="font-semibold">&#10003; CV sudah tersimpan</span><a class="btn-secondary" href="{{ route('profile.cv.download') }}">Unduh CV</a></div>@endif</div>
        <button class="btn-primary md:col-span-2">Simpan profil</button>
    </form>
    @if($worker->cv_path)<form class="mt-4 text-right" method="post" action="{{ route('profile.cv.destroy') }}" onsubmit="return confirm('Hapus CV yang tersimpan?')">@csrf @method('DELETE')<button class="text-sm font-bold text-red-700">Hapus CV</button></form>@endif
</section>
@endsection



