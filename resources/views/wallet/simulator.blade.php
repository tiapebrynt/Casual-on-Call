@extends('layouts.app')
@section('title', 'Midtrans Payment Simulator - Top Up')
@section('content')
<section class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:py-16">
    <div class="card overflow-hidden bg-white border border-black/10 shadow-xl rounded-3xl !p-0">
        <!-- Midtrans Simulator Header -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 sm:p-8 text-white relative">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="font-display font-extrabold text-xl tracking-tight text-white flex items-center gap-1.5">
                        <span class="inline-block size-3 bg-emerald-400 rounded-full animate-pulse"></span>
                        MIDTRANS SNAP
                    </span>
                    <span class="badge !bg-white/20 !text-white text-[10px] uppercase font-bold tracking-wider">SANDBOX SIMULATOR</span>
                </div>
                <div class="text-right text-xs text-slate-300">
                    Order ID: <b class="font-mono text-white">{{ $topUp->midtrans_order_id }}</b>
                </div>
            </div>

            <div class="mt-6">
                <span class="text-xs uppercase tracking-wider text-slate-300">Total Pembayaran Top Up</span>
                <div class="mt-1 font-display text-3xl sm:text-4xl font-extrabold text-white">
                    Rp{{ number_format($topUp->amount, 0, ',', '.') }}
                </div>
                <p class="mt-1 text-xs text-slate-300">Pengisian saldo ke CoC Wallet ({{ $topUp->user->name }})</p>
            </div>
        </div>

        <!-- Simulator Body -->
        <div class="p-6 sm:p-8 space-y-6">
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-900">
                <div class="flex items-start gap-2.5">
                    <x-icon name="info" class="size-5 text-amber-600 shrink-0 mt-0.5" />
                    <div>
                        <b class="font-bold">Mode Simulasi Pengujian Pembayaran Midtrans</b>
                        <p class="mt-0.5 leading-relaxed text-amber-800">
                            Halaman ini mensimulasikan gerbang pembayaran resmi Midtrans Snap. Anda dapat menguji pelunasan instan secara lokal.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-secondary">Detail Transaksi Gateway</h3>
                <div class="rounded-2xl border border-black/5 bg-surface-low p-4 text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Metode Dipilih</span>
                        <b class="text-secondary uppercase font-mono">{{ str_replace('_', ' ', $topUp->gateway_method) }}</b>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Nomor VA / Merchant ID</span>
                        <b class="text-secondary font-mono">8800{{ random_int(10000000, 99999999) }}</b>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Status Transaksi</span>
                        <span class="badge !bg-amber-100 !text-amber-800 text-[10px] font-bold">PENDING / MENUNGGU PEMBAYARAN</span>
                    </div>
                </div>
            </div>

            <!-- Simulation Action Buttons -->
            <div class="pt-4 border-t border-black/5 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('wallet.topup.finish', [
                    'order_id' => $topUp->midtrans_order_id,
                    'status_code' => '200',
                    'transaction_status' => 'settlement',
                    'transaction_id' => 'SIM-TRX-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                    'gross_amount' => (int) $topUp->amount,
                ]) }}" class="btn-primary flex-1 justify-center !py-3 !text-sm">
                    <x-icon name="check_circle" class="size-5" />
                    <span>Simulasikan Pembayaran Berhasil (Success)</span>
                </a>

                <a href="{{ route('wallet.index') }}" class="btn-ghost justify-center !py-3 !text-sm border border-black/10">
                    <span>Batalkan Transaksi</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

