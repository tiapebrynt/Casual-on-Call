@extends('layouts.app')
@section('title', 'Form Lamaran')
@section('content')
<section class="bg-gradient-to-br from-[#fff7f8] to-[#eaffff]">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-10 lg:py-14">
        <a href="{{ route('jobs.show', $job) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline">
            <x-icon name="arrow_back" class="size-4" />
            <span>Kembali ke detail lowongan</span>
        </a>
        <p class="mt-6 text-xs font-bold uppercase tracking-wider text-primary">Formulir Lamaran Pekerjaan</p>
        <h1 class="mt-2 font-display text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">Lamar {{ $job->title }}</h1>
        <p class="mt-2 text-xs sm:text-sm text-on-surface-variant">{{ $job->company->name }} &middot; {{ $job->location }}</p>
    </div>
</section>

<section class="mx-auto grid max-w-5xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_320px] lg:px-10">
    <div class="card !p-6 sm:!p-8 bg-white border border-black/5 shadow-sm">
        @if($alreadyApplied)
            <div class="alert-success">
                <x-icon name="check_circle" class="size-5" />
                <span>Kamu sudah pernah melamar lowongan ini.</span>
            </div>
            <a href="{{ route('applications.index') }}" class="btn-primary mt-6">Lihat status lamaran</a>
        @else
            <form method="POST" action="{{ route('applications.store', $job) }}" enctype="multipart/form-data">
                @csrf
                <h2 class="font-display text-2xl font-bold">Perkenalkan Dirimu & Kirim CV</h2>
                <p class="mt-2 text-xs sm:text-sm leading-6 text-on-surface-variant">Kirimkan pesan lamaran dan CV terbaikmu yang relevan dengan posisi ini.</p>
                
                <!-- UPLOAD CV SECTION -->
                <div class="mt-6 rounded-2xl border border-dashed border-primary/40 bg-surface-low p-5">
                    <label class="label !text-sm !font-bold flex items-center justify-between" for="cv">
                        <span class="flex items-center gap-2 text-secondary">
                            <x-icon name="description" class="size-4 text-primary" />
                            <span>Unggah CV / Resume Pelamar</span>
                        </span>
                        <span class="text-xs font-normal text-on-surface-variant">PDF, DOC, DOCX (Maks 5MB)</span>
                    </label>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        Kamu dapat mengunggah file CV berbeda yang disesuaikan khusus untuk posisi <b>{{ $job->title }}</b> ini.
                    </p>
                    <div class="mt-3">
                        <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx" class="input !py-2.5 !bg-white file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/20">
                    </div>
                    @if(!empty($worker->cv_path))
                        <p class="mt-2.5 flex items-center gap-1.5 text-xs text-emerald-700 font-medium">
                            <x-icon name="check_circle" class="size-3.5" />
                            <span>Kamu sudah memiliki CV profil tersimpan. Jika tidak memilih file baru, CV profil akan otomatis digunakan.</span>
                        </p>
                    @endif
                    @error('cv')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label class="label" for="cover_letter">Pesan Lamaran / Cover Letter</label>
                    <textarea id="cover_letter" name="cover_letter" class="input min-h-36 resize-y" maxlength="3000" placeholder="Contoh: Saya memiliki pengalaman sebagai barista selama 2 tahun dan terbiasa bekerja dalam shift...">{{ old('cover_letter') }}</textarea>
                    <div class="mt-2 flex justify-between gap-4 text-xs text-on-surface-variant">
                        <span>Maksimal 3.000 karakter</span>
                        <span>Opsional namun sangat direkomendasikan.</span>
                    </div>
                    @error('cover_letter')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <label class="choice mt-6 items-start">
                    <input type="checkbox" required class="mt-1 accent-primary">
                    <span>
                        <span class="block text-sm font-semibold">Saya memastikan informasi & CV yang diberikan benar</span>
                        <span class="mt-0.5 block text-xs font-normal text-on-surface-variant">Periksa kembali detail lowongan dan jadwal sebelum mengirim.</span>
                    </span>
                </label>
                <button class="btn-primary mt-7 w-full justify-center" type="submit">
                    <x-icon name="send" class="size-4" />
                    <span>Kirim Lamaran Sekarang</span>
                </button>
            </form>
        @endif
    </div>

    <aside class="card h-fit !p-6 bg-white border border-black/5 shadow-sm lg:sticky lg:top-28 space-y-4">
        <span class="badge">{{ $job->category->name }}</span>
        <h2 class="font-display text-xl font-bold text-secondary">{{ $job->title }}</h2>
        <dl class="space-y-3.5 text-xs sm:text-sm border-t border-black/10 pt-4">
            <div>
                <dt class="text-on-surface-variant">Besaran Upah</dt>
                <dd class="mt-0.5 font-bold text-primary">
                    Rp{{ number_format($job->daily_rate, 0, ',', '.') }} / {{ $job->payment_type === 'project' ? 'proyek' : 'hari' }}
                </dd>
            </div>
            <div>
                <dt class="text-on-surface-variant">Jadwal Shift</dt>
                <dd class="mt-0.5 font-bold text-secondary">
                    {{ $job->starts_at->format('d M') }} &ndash; {{ $job->ends_at->format('d M Y') }}
                </dd>
            </div>
            <div>
                <dt class="text-on-surface-variant">Batas Lamaran</dt>
                <dd class="mt-0.5 font-bold text-secondary">{{ $job->application_deadline->format('d M Y, H:i') }}</dd>
            </div>
        </dl>
    </aside>
</section>
@endsection


