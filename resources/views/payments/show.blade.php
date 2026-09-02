@extends('layouts.app')
@section('title', 'Invoice #' . $payment->invoice_number)
@section('content')
<section class="mx-auto max-w-[1200px] px-4 py-10 sm:px-6 lg:px-10 lg:py-14 print:p-0 print:max-w-full">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end print:hidden">
        <div>
            <span class="eyebrow">INVOICE & PEMBAYARAN</span>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight sm:text-4xl">
                Invoice #{{ $payment->invoice_number }}
            </h1>
            <div class="mt-2 flex items-center gap-2.5 text-xs sm:text-sm">
                @if($payment->status === 'paid')
                    <span class="badge-success">LUNAS / PAID</span>
                @else
                    <span class="badge-warning">MENUNGGU PEMBAYARAN</span>
                @endif
                <span class="text-on-surface-variant">&middot; Diterbitkan: {{ $payment->created_at->format('d F Y, H:i') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button class="btn-ghost compact" type="button" onclick="window.print()">
                <x-icon name="description" class="size-4" />
                <span>Cetak Invoice</span>
            </button>
            <a href="{{ route('applications.index') }}" class="btn-secondary compact">
                <span>&larr; Kembali</span>
            </a>
        </div>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_380px]">
        <!-- MAIN INVOICE CARD -->
        <div class="card !p-6 sm:!p-8 bg-white border border-black/5 shadow-sm print:border-none print:shadow-none">
            <!-- Header Invoice Printable -->
            <div class="flex items-start justify-between border-b border-black/10 pb-6">
                <div>
                    <div class="flex items-center gap-2 text-primary font-display text-2xl font-bold">
                        <span>Casual on Call</span>
                    </div>
                    <p class="mt-1 text-xs text-on-surface-variant">Platform Tenaga Kerja Casual Terpercaya</p>
                </div>
                <div class="text-right">
                    <span class="text-xs uppercase tracking-wider text-on-surface-variant block">Nomor Invoice</span>
                    <strong class="font-mono text-base sm:text-lg text-secondary">{{ $payment->invoice_number }}</strong>
                </div>
            </div>

            <!-- Parties Info -->
            <div class="mt-6 grid gap-6 sm:grid-cols-2 text-xs sm:text-sm">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Ditagihkan Kepada (Company)</p>
                    <h2 class="mt-2 font-display text-lg font-bold text-secondary">{{ $payment->application->job->company->name }}</h2>
                    <p class="mt-1 text-on-surface-variant">{{ $payment->application->job->company->address ?? 'Indonesia' }}</p>
                    <p class="mt-0.5 text-on-surface-variant">PIC: {{ $payment->application->job->company->user->name }}</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Penerima Upah (Worker)</p>
                    <h2 class="mt-2 font-display text-lg font-bold text-secondary">{{ $payment->application->worker->user->name }}</h2>
                    <p class="mt-1 text-on-surface-variant">Posisi: {{ $payment->application->job->title }}</p>
                    <p class="mt-0.5 text-on-surface-variant">Telp: {{ $payment->application->worker->user->phone ?? '-' }}</p>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="mt-8 overflow-hidden rounded-2xl border border-black/5 bg-surface-low">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-black/5 text-on-surface-variant font-bold uppercase text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Deskripsi Layanan</th>
                            <th class="py-3 px-4 text-center">Durasi / Tipe</th>
                            <th class="py-3 px-4 text-right">Tarif</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <tr>
                            <td class="py-4 px-4">
                                <b class="block text-secondary">{{ $payment->application->job->title }}</b>
                                <span class="text-xs text-on-surface-variant">
                                    Periode: {{ $payment->application->job->starts_at->format('d M') }} &ndash; {{ $payment->application->job->ends_at->format('d M Y') }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($payment->application->job->payment_type === 'project')
                                    <span>1 Proyek</span>
                                @else
                                    <span>{{ $payment->application->job->duration_days }} Hari</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                Rp{{ number_format($payment->application->job->daily_rate, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-bold">
                                Rp{{ number_format($payment->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Price Breakdown -->
            <div class="mt-6 ml-auto max-w-xs space-y-2.5 text-xs sm:text-sm">
                <div class="flex justify-between text-on-surface-variant">
                    <span>Subtotal Jasa</span>
                    <span class="font-medium text-secondary">Rp{{ number_format($payment->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-on-surface-variant">
                    <span>Biaya Layanan Platform</span>
                    <span class="font-medium text-secondary">Rp{{ number_format($payment->platform_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-black/10 pt-3 text-base sm:text-lg font-bold">
                    <span>Total Tagihan</span>
                    <span class="text-primary font-display">Rp{{ number_format($payment->total, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($payment->status === 'paid')
                <div class="mt-8 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-full bg-emerald-600 text-white font-bold text-lg">
                        ✓
                    </div>
                    <div>
                        <strong class="block text-sm">Pembayaran Telah Lunas</strong>
                        <p class="text-xs text-emerald-700">
                            Dibayar via <b>{{ $payment->method }}</b> pada {{ $payment->paid_at?->format('d M Y, H:i') }}.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- PAYMENT SIDEBAR & ACTION -->
        <aside class="space-y-5 print:hidden">
            @if(auth()->user()->hasRole('company') && $payment->status !== 'paid')
                <div class="card !p-6 bg-white border border-primary/20 shadow-md">
                    <h2 class="font-display text-lg font-bold text-secondary">Proses Pembayaran</h2>
                    <p class="mt-1 text-xs text-on-surface-variant">Pilih metode pembayaran resmi untuk menyelesaikan gaji tenaga kerja.</p>

                    <form method="POST" action="{{ route('payments.pay', $payment) }}" class="mt-5 space-y-3" id="payment-form">
                        @csrf

                        <!-- CoC Wallet Option -->
                        <label class="block cursor-pointer rounded-2xl border border-black/10 p-3.5 hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="method" value="casual_wallet" class="mt-1 text-primary focus:ring-primary" checked>
                                <div class="flex-1 text-xs">
                                    <div class="flex items-center justify-between">
                                        <b class="text-secondary text-sm">CoC Wallet Perusahaan</b>
                                        <span class="rounded bg-primary-soft px-1.5 py-0.5 text-[10px] font-bold text-primary">Instan</span>
                                    </div>
                                    <p class="mt-1 text-on-surface-variant">
                                        Saldo Anda: <strong class="text-secondary">Rp{{ number_format($companyWallet?->balance ?? 0, 0, ',', '.') }}</strong>
                                    </p>
                                </div>
                            </div>
                        </label>

                        <!-- Bank Transfer (VA) Option -->
                        <label class="block cursor-pointer rounded-2xl border border-black/10 p-3.5 hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="method" value="bank_transfer" class="mt-1 text-primary focus:ring-primary">
                                <div class="flex-1 text-xs">
                                    <div class="flex items-center justify-between">
                                        <b class="text-secondary text-sm">Transfer Virtual Account</b>
                                        <span class="text-[10px] text-on-surface-variant">BCA / Mandiri / BRI</span>
                                    </div>
                                    <div class="mt-1.5 rounded-lg bg-surface-low p-2 font-mono text-[11px] text-secondary flex items-center justify-between">
                                        <span>VA: 8809 1234 {{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('88091234{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}'); alert('Nomor Virtual Account disalin!');" class="text-primary font-bold text-[10px] underline">Salin</button>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- E-Wallet / QRIS Option -->
                        <label class="block cursor-pointer rounded-2xl border border-black/10 p-3.5 hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="method" value="e_wallet" class="mt-1 text-primary focus:ring-primary">
                                <div class="flex-1 text-xs">
                                    <div class="flex items-center justify-between">
                                        <b class="text-secondary text-sm">QRIS & E-Wallet</b>
                                        <span class="text-[10px] text-on-surface-variant">GoPay / OVO / DANA</span>
                                    </div>
                                    <p class="mt-1 text-on-surface-variant">Konfirmasi otomatis setelah pembayaran QRIS diterima.</p>
                                </div>
                            </div>
                        </label>

                        <!-- Cash Option -->
                        <label class="block cursor-pointer rounded-2xl border border-black/10 p-3.5 hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="method" value="cash" class="mt-1 text-primary focus:ring-primary">
                                <div class="flex-1 text-xs">
                                    <b class="text-secondary text-sm">Pembayaran Tunai (Cash)</b>
                                    <p class="mt-0.5 text-on-surface-variant">Serah terima tunai secara langsung di lokasi kerja.</p>
                                </div>
                            </div>
                        </label>

                        <button type="submit" class="btn-primary w-full mt-4 justify-center">
                            <x-icon name="check_circle" class="size-4" />
                            <span>Konfirmasi & Bayar Sekarang</span>
                        </button>
                    </form>
                </div>
            @endif

            <div class="card !p-6 bg-white border border-black/5">
                <h3 class="font-display text-base font-bold text-secondary">Informasi Transaksi</h3>
                <dl class="mt-4 space-y-3 text-xs">
                    <div>
                        <dt class="text-on-surface-variant">Metode Pembayaran</dt>
                        <dd class="mt-0.5 font-bold text-secondary">{{ $payment->method ?: 'Belum dipilih' }}</dd>
                    </div>
                    <div>
                        <dt class="text-on-surface-variant">Waktu Pelunasan</dt>
                        <dd class="mt-0.5 font-bold text-secondary">{{ $payment->paid_at?->format('d M Y, H:i') ?: 'Menunggu Pelunasan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-on-surface-variant">Kode Referensi</dt>
                        <dd class="mt-0.5 font-mono font-medium text-[11px] text-secondary break-all">
                            {{ $payment->transaction_reference ?: '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl bg-surface-low p-5 text-xs text-on-surface-variant space-y-2 border border-black/5">
                <div class="flex items-center gap-2 font-bold text-secondary">
                    <x-icon name="info" class="size-4 text-primary" />
                    <span>Jaminan Keamanan CoC</span>
                </div>
                <p class="leading-5">
                    Dana pembayaran langsung diteruskan ke wallet worker terdaftar secara otomatis saat transaksi berhasil diverifikasi.
                </p>
            </div>
        </aside>
    </div>
</section>
@endsection



