@extends('layouts.app')
@section('title', 'Midtrans Payment Simulator - Invoice #' . $payment->invoice_number)
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
                    Order ID: <b class="font-mono text-white">{{ $payment->midtrans_order_id }}</b>
                </div>
            </div>

            <div class="mt-6">
                <span class="text-xs uppercase tracking-wider text-slate-300">Total Pembayaran Gaji</span>
                <div class="mt-1 font-display text-3xl sm:text-4xl font-extrabold text-white">
                    Rp{{ number_format($payment->total, 0, ',', '.') }}
                </div>
                <p class="mt-1 text-xs text-slate-300">
                    Penerima: {{ $payment->application->worker->user->name }} &bull; Posisi: {{ $payment->application->job->title }}
                </p>
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
                            Halaman ini mensimulasikan gerbang pembayaran resmi Midtrans Snap untuk Invoice <b>#{{ $payment->invoice_number }}</b>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-secondary">Rincian Invoice Tagihan</h3>
                <div class="rounded-2xl border border-black/5 bg-surface-low p-4 text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Subtotal Gaji Jasa</span>
                        <b class="text-secondary font-mono">Rp{{ number_format($payment->subtotal, 0, ',', '.') }}</b>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Biaya Layanan Platform</span>
                        <b class="text-secondary font-mono">Rp{{ number_format($payment->platform_fee, 0, ',', '.') }}</b>
                    </div>
                    <div class="flex justify-between border-t border-black/5 pt-2">
                        <span class="text-secondary font-bold">Total Pembayaran</span>
                        <b class="text-primary font-bold text-sm font-mono">Rp{{ number_format($payment->total, 0, ',', '.') }}</b>
                    </div>
                </div>
            </div>

            <!-- Simulation Action Buttons -->
            <div class="pt-4 border-t border-black/5 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('payments.finish', [
                    'payment' => $payment,
                    'order_id' => $payment->midtrans_order_id,
                    'status_code' => '200',
                    'transaction_status' => 'settlement',
                    'transaction_id' => 'SIM-PAY-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                    'gross_amount' => (int) $payment->total,
                ]) }}" class="btn-primary flex-1 justify-center !py-3 !text-sm">
                    <x-icon name="check_circle" class="size-5" />
                    <span>Simulasikan Pembayaran Berhasil (Success)</span>
                </a>

                <a href="{{ route('payments.show', $payment) }}" class="btn-ghost justify-center !py-3 !text-sm border border-black/10">
                    <span>Kembali ke Invoice</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

