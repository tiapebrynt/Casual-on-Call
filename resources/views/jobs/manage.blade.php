@extends('layouts.app')
@section('title', 'Kelola Lowongan')
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-10">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <span class="eyebrow">COMPANY WORKSPACE</span>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight sm:text-4xl">Kelola Lowongan</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Buat, edit, perbarui status, dan pantau seluruh lowongan pekerjaanmu.</p>
        </div>
        <button type="button" onclick="document.getElementById('create-job-modal').showModal()" class="btn-primary">
            <x-icon name="plus" class="size-5" />
            <span>Buat Lowongan Baru</span>
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card !p-5">
            <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Lowongan</p>
            <strong class="mt-2 block font-display text-2xl sm:text-3xl">{{ $jobs->total() }}</strong>
        </div>
        <div class="card !p-5">
            <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Published</p>
            <strong class="mt-2 block font-display text-2xl sm:text-3xl text-[#006262]">{{ $jobs->where('status', 'published')->count() }}</strong>
        </div>
        <div class="card !p-5">
            <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Expired</p>
            <strong class="mt-2 block font-display text-2xl sm:text-3xl text-amber-600">{{ $jobs->where('status', 'expired')->count() }}</strong>
        </div>
        <div class="card !p-5">
            <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Pelamar</p>
            <strong class="mt-2 block font-display text-2xl sm:text-3xl text-primary">{{ $jobs->sum('applications_count') }}</strong>
        </div>
    </div>

    <!-- Jobs Table & Card Container -->
    <div class="mt-8 overflow-hidden rounded-3xl border border-black/5 bg-white shadow-sm">
        <div class="p-6 border-b border-black/5 flex items-center justify-between">
            <h2 class="font-display text-xl font-bold">Daftar Lowongan Pekerjaan</h2>
            <span class="text-xs text-on-surface-variant font-medium">{{ $jobs->total() }} total ditemukan</span>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-low text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="py-4 px-6">Posisi & Kategori</th>
                        <th class="py-4 px-6">Tipe & Upah</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Pelamar</th>
                        <th class="py-4 px-6">Batas Lamaran</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 text-sm">
                    @forelse($jobs as $job)
                        <tr class="hover:bg-surface-low/50 transition-colors">
                            <td class="py-4 px-6">
                                <a href="{{ route('jobs.show', $job) }}" class="font-bold text-secondary hover:text-primary transition-colors block">
                                    {{ $job->title }}
                                </a>
                                <small class="text-on-surface-variant flex items-center gap-1 mt-0.5">
                                    <span>{{ $job->category->name }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $job->location }}</span>
                                </small>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-primary">Rp{{ number_format($job->daily_rate, 0, ',', '.') }}</span>
                                <span class="text-xs text-on-surface-variant block capitalize">
                                    {{ $job->payment_type === 'project' ? 'Per Proyek' : 'Per Hari' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($job->status === 'published')
                                    <span class="badge-success">Published</span>
                                @elseif($job->status === 'expired')
                                    <span class="badge-warning">Expired</span>
                                @elseif($job->status === 'draft')
                                    <span class="badge-neutral">Draft</span>
                                @else
                                    <span class="badge">{{ ucfirst($job->status) }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-semibold">
                                <a href="{{ route('applications.index') }}" class="inline-flex items-center gap-1 text-primary hover:underline">
                                    <span>{{ $job->applications_count }}</span>
                                    <span class="text-xs text-on-surface-variant">pelamar</span>
                                </a>
                            </td>
                            <td class="py-4 px-6 text-xs text-on-surface-variant">
                                <span>{{ $job->application_deadline->format('d M Y, H:i') }}</span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('jobs.show', $job) }}" class="icon-button !size-9" title="Lihat Detail">
                                        <x-icon name="arrow_forward" class="size-4" />
                                    </a>
                                    <button type="button" onclick="document.getElementById('edit-job-modal-{{ $job->id }}').showModal()" class="icon-button !size-9 text-blue-600 hover:bg-blue-50" title="Edit Lowongan">
                                        <x-icon name="edit" class="size-4" />
                                    </button>
                                    <form method="POST" action="{{ route('jobs.destroy', $job) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lowongan {{ $job->title }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-button !size-9 text-rose-600 hover:bg-rose-50" title="Hapus Lowongan">
                                            <x-icon name="delete" class="size-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-on-surface-variant">
                                Belum ada lowongan yang dibuat. Klik tombol <b>Buat Lowongan Baru</b> untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden divide-y divide-black/5">
            @forelse($jobs as $job)
                <div class="p-5 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('jobs.show', $job) }}" class="font-bold text-base text-secondary hover:text-primary">
                                {{ $job->title }}
                            </a>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ $job->category->name }} &middot; {{ $job->location }}</p>
                        </div>
                        @if($job->status === 'published')
                            <span class="badge-success">Published</span>
                        @elseif($job->status === 'expired')
                            <span class="badge-warning">Expired</span>
                        @elseif($job->status === 'draft')
                            <span class="badge-neutral">Draft</span>
                        @else
                            <span class="badge">{{ ucfirst($job->status) }}</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs py-2 bg-surface-low rounded-xl px-3">
                        <div>
                            <span class="text-on-surface-variant">Upah:</span>
                            <b class="text-primary block">Rp{{ number_format($job->daily_rate, 0, ',', '.') }} ({{ $job->payment_type === 'project' ? 'Proyek' : 'Harian' }})</b>
                        </div>
                        <div>
                            <span class="text-on-surface-variant">Pelamar:</span>
                            <b class="block">{{ $job->applications_count }} orang</b>
                        </div>
                        <div class="col-span-2">
                            <span class="text-on-surface-variant">Batas Lamaran:</span>
                            <b>{{ $job->application_deadline->format('d M Y, H:i') }}</b>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <a href="{{ route('jobs.show', $job) }}" class="btn-ghost compact !py-1.5 !text-xs">
                            <span>Lihat</span>
                        </a>
                        <button type="button" onclick="document.getElementById('edit-job-modal-{{ $job->id }}').showModal()" class="btn-secondary compact !py-1.5 !text-xs !bg-slate-700">
                            <x-icon name="edit" class="size-3.5" />
                            <span>Edit</span>
                        </button>
                        <form method="POST" action="{{ route('jobs.destroy', $job) }}" onsubmit="return confirm('Hapus lowongan ini?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost compact !py-1.5 !text-xs !text-rose-600 hover:!bg-rose-50">
                                <span>Hapus</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-on-surface-variant">
                    Belum ada lowongan yang dibuat.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-8">
        {{ $jobs->links() }}
    </div>
</section>

<!-- CREATE JOB MODAL -->
<dialog id="create-job-modal" class="rounded-3xl p-0 backdrop:bg-black/60 backdrop:backdrop-blur-sm w-full max-w-3xl shadow-2xl">
    <div class="bg-white p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-black/10 pb-4">
            <div>
                <span class="badge">POSTING PEKERJAAN</span>
                <h2 class="mt-1 font-display text-2xl font-bold">Buat Lowongan Baru</h2>
            </div>
            <button type="button" onclick="document.getElementById('create-job-modal').close()" class="icon-button !size-9">
                <x-icon name="close" />
            </button>
        </div>

        <form method="POST" action="{{ route('jobs.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label class="label">Judul Posisi</label>
                <input class="input" name="title" placeholder="Contoh: Barista Event Weekend" required>
            </div>

            <div>
                <label class="label">Kategori</label>
                <select class="input" name="job_category_id" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label">Lokasi Kerja</label>
                <input class="input" name="location" placeholder="Contoh: Jakarta Selatan" required>
            </div>

            <div class="sm:col-span-2">
                <label class="label">Deskripsi Pekerjaan</label>
                <textarea class="input min-h-28 resize-y" name="description" placeholder="Jelaskan rincian tanggung jawab dan tugas pekerjaan secara detail..." required></textarea>
            </div>

            <div>
                <label class="label">Tipe Pembayaran</label>
                <select class="input" name="payment_type" required>
                    <option value="daily">Harian (Daily Rate)</option>
                    <option value="project">Borongan / Proyek (Project Rate)</option>
                </select>
            </div>

            <div>
                <label class="label">Besaran Upah (Rp)</label>
                <input class="input" type="number" name="daily_rate" min="10000" step="5000" placeholder="Contoh: 250000" required>
            </div>

            <div>
                <label class="label">Tanggal & Jam Mulai</label>
                <input class="input" type="datetime-local" name="starts_at" required>
            </div>

            <div>
                <label class="label">Tanggal & Jam Selesai</label>
                <input class="input" type="datetime-local" name="ends_at" required>
            </div>

            <div>
                <label class="label">Batas Akhir Lamaran</label>
                <input class="input" type="datetime-local" name="application_deadline" required>
            </div>

            <div>
                <label class="label">Jumlah Posisi (Orang)</label>
                <input class="input" type="number" name="vacancies" value="1" min="1" required>
            </div>

            <div>
                <label class="label">Status Publikasi</label>
                <select class="input" name="status">
                    <option value="published">Published (Langsung Tayang)</option>
                    <option value="draft">Draft (Simpan Sementara)</option>
                    <option value="expired">Expired (Kedaluwarsa)</option>
                </select>
            </div>

            <div>
                <label class="label">Persyaratan 1 <span class="optional">Wajib</span></label>
                <input class="input" name="requirements[]" placeholder="Contoh: Usia minimal 18 tahun" required>
            </div>

            <div class="sm:col-span-2">
                <label class="label">Persyaratan Tambahan <span class="optional">Opsional</span></label>
                <div class="grid gap-2">
                    <input class="input" name="requirements[]" placeholder="Contoh: Pengalaman minimal 1 tahun">
                    <input class="input" name="requirements[]" placeholder="Contoh: Berpenampilan rapi dan ramah">
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-3 sm:col-span-2 border-t border-black/10 pt-4">
                <button type="button" onclick="document.getElementById('create-job-modal').close()" class="btn-ghost">Batal</button>
                <button type="submit" class="btn-primary">Publikasikan Lowongan</button>
            </div>
        </form>
    </div>
</dialog>

<!-- EDIT MODALS FOR EACH JOB -->
@foreach($jobs as $job)
<dialog id="edit-job-modal-{{ $job->id }}" class="rounded-3xl p-0 backdrop:bg-black/60 backdrop:backdrop-blur-sm w-full max-w-3xl shadow-2xl">
    <div class="bg-white p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-black/10 pb-4">
            <div>
                <span class="badge">EDIT LOWONGAN</span>
                <h2 class="mt-1 font-display text-2xl font-bold">{{ $job->title }}</h2>
            </div>
            <button type="button" onclick="document.getElementById('edit-job-modal-{{ $job->id }}').close()" class="icon-button !size-9">
                <x-icon name="close" />
            </button>
        </div>

        <form method="POST" action="{{ route('jobs.update', $job) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2">
                <label class="label">Judul Posisi</label>
                <input class="input" name="title" value="{{ old('title', $job->title) }}" required>
            </div>

            <div>
                <label class="label">Kategori</label>
                <select class="input" name="job_category_id" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($job->job_category_id === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label">Lokasi Kerja</label>
                <input class="input" name="location" value="{{ old('location', $job->location) }}" required>
            </div>

            <div class="sm:col-span-2">
                <label class="label">Deskripsi Pekerjaan</label>
                <textarea class="input min-h-28 resize-y" name="description" required>{{ old('description', $job->description) }}</textarea>
            </div>

            <div>
                <label class="label">Tipe Pembayaran</label>
                <select class="input" name="payment_type" required>
                    <option value="daily" @selected($job->payment_type === 'daily')>Harian (Daily Rate)</option>
                    <option value="project" @selected($job->payment_type === 'project')>Borongan / Proyek (Project Rate)</option>
                </select>
            </div>

            <div>
                <label class="label">Besaran Upah (Rp)</label>
                <input class="input" type="number" name="daily_rate" value="{{ (int)$job->daily_rate }}" min="10000" step="5000" required>
            </div>

            <div>
                <label class="label">Tanggal & Jam Mulai</label>
                <input class="input" type="datetime-local" name="starts_at" value="{{ $job->starts_at->format('Y-m-d\TH:i') }}" required>
            </div>

            <div>
                <label class="label">Tanggal & Jam Selesai</label>
                <input class="input" type="datetime-local" name="ends_at" value="{{ $job->ends_at->format('Y-m-d\TH:i') }}" required>
            </div>

            <div>
                <label class="label">Batas Akhir Lamaran</label>
                <input class="input" type="datetime-local" name="application_deadline" value="{{ $job->application_deadline->format('Y-m-d\TH:i') }}" required>
            </div>

            <div>
                <label class="label">Jumlah Posisi (Orang)</label>
                <input class="input" type="number" name="vacancies" value="{{ $job->vacancies }}" min="1" required>
            </div>

            <div>
                <label class="label">Status</label>
                <select class="input" name="status" required>
                    <option value="published" @selected($job->status === 'published')>Published (Aktif)</option>
                    <option value="expired" @selected($job->status === 'expired')>Expired (Kedaluwarsa)</option>
                    <option value="draft" @selected($job->status === 'draft')>Draft (Simpan Sementara)</option>
                    <option value="closed" @selected($job->status === 'closed')>Closed (Ditutup)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="label">Persyaratan</label>
                <div class="grid gap-2">
                    @forelse($job->requirements as $req)
                        <input class="input" name="requirements[]" value="{{ $req->requirement }}" placeholder="Persyaratan">
                    @empty
                        <input class="input" name="requirements[]" placeholder="Contoh: Minimal 18 tahun">
                    @endforelse
                    <input class="input" name="requirements[]" placeholder="+ Tambah persyaratan baru (opsional)">
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-3 sm:col-span-2 border-t border-black/10 pt-4">
                <button type="button" onclick="document.getElementById('edit-job-modal-{{ $job->id }}').close()" class="btn-ghost">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</dialog>
@endforeach

@endsection





