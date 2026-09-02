@extends('layouts.app')
@section('title', 'Dompet & Saldo (Wallet)')
@section('content')
<section class="mx-auto max-w-[1200px] px-4 py-10 sm:px-6 lg:px-10 lg:py-14">
    <div>
        <span class="eyebrow">FINANCE WORKSPACE</span>
        <h1 class="mt-2 font-display text-3xl font-bold lg:text-4xl">Dompet & Saldo (Wallet)</h1>
        <p class="mt-1 text-sm text-on-surface-variant">Pantau saldo pendapatan, saldo tertunda, dan riwayat mutasi dana.</p>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_1.3fr]">
        <!-- BALANCE CARD -->
        <div class="rounded-[28px] bg-gradient-to-br from-primary via-[#8a0022] to-[#4f0014] p-7 text-white shadow-xl flex flex-col justify-between">
            <div>
                <span class="text-xs uppercase tracking-wider text-white/70 font-semibold">Saldo Tersedia (Ready)</span>
                <strong class="mt-3 block font-display text-3xl sm:text-4xl font-extrabold">
                    Rp{{ number_format($wallet->balance, 0, ',', '.') }}
                </strong>
            </div>

            <div class="mt-8 border-t border-white/20 pt-5 text-xs sm:text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-white/80">Saldo Menunggu Pelunasan</span>
                    <b class="font-bold text-white">Rp{{ number_format($wallet->pending_balance, 0, ',', '.') }}</b>
                </div>
            </div>
        </div>

        <!-- WITHDRAW FORM -->
        <form method="POST" action="{{ route('wallet.withdraw') }}" class="card !p-6 sm:!p-7 bg-white border border-black/5 shadow-sm">
            @csrf
            <h2 class="font-display text-lg font-bold text-secondary">Tarik Saldo ke Rekening</h2>
            <p class="mt-1 text-xs text-on-surface-variant">Penarikan minimal Rp50.000 ke rekening bank atau dompet digital terdaftar.</p>

            <div class="mt-5">
                <label class="label" for="amount">Jumlah Penarikan (Rp)</label>
                <input class="input" id="amount" name="amount" type="number" min="50000" step="5000" max="{{ (int)$wallet->balance }}" placeholder="Contoh: 100000" required>
            </div>

            <div class="mt-5 flex items-center justify-between">
                <span class="text-xs text-on-surface-variant">Biaya admin penarikan: <b>Gratis (Rp0)</b></span>
                <button class="btn-primary compact" type="submit" @disabled($wallet->balance < 50000)>
                    <x-icon name="cash" class="size-4" />
                    <span>Tarik Saldo</span>
                </button>
            </div>
        </form>
    </div>

    <div class="mt-12">
        <h2 class="font-display text-2xl font-bold">Riwayat Transaksi Dompet</h2>
        
        <div class="mt-5 overflow-hidden rounded-3xl bg-white border border-black/5 shadow-xs">
            @forelse($transactions as $transaction)
                <div class="flex items-center justify-between gap-4 border-b border-black/5 p-5 last:border-0 hover:bg-surface-low/50 transition-colors">
                    <div class="flex items-center gap-3.5">
                        <div class="grid size-10 shrink-0 place-items-center rounded-xl {{ $transaction->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            <x-icon name="{{ $transaction->type === 'credit' ? 'arrow_downward' : 'arrow_upward' }}" class="size-5" />
                        </div>
                        <div>
                            <b class="text-sm text-secondary block">{{ $transaction->description }}</b>
                            <p class="mt-0.5 text-xs text-on-surface-variant">
                                {{ $transaction->created_at->format('d M Y, H:i') }} &middot; <span class="font-mono">{{ $transaction->reference }}</span>
                            </p>
                        </div>
                    </div>
                    <strong class="font-display text-sm sm:text-base {{ $transaction->type === 'credit' ? 'text-emerald-600' : 'text-primary' }}">
                        {{ $transaction->type === 'credit' ? '+' : '-' }} Rp{{ number_format($transaction->amount, 0, ',', '.') }}
                    </strong>
                </div>
            @empty
                <div class="p-12 text-center text-xs sm:text-sm text-on-surface-variant">
                    Belum ada riwayat transaksi di dompet ini.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $transactions->links() }}
        </div>
    </div>
</section>
@endsection


