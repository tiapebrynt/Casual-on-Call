@extends('layouts.app')
@section('title', 'Dompet & Transaksi Keuangan')
@section('content')
<section class="mx-auto max-w-[1200px] px-4 py-8 sm:px-6 lg:px-10 lg:py-12">
    <div>
        <div class="flex items-center gap-2">
            <span class="badge uppercase tracking-wider !text-xs font-bold !bg-primary-soft !text-primary">
                {{ auth()->user()->hasRole('company') ? 'COMPANY FINANCE' : 'WORKER FINANCE' }}
            </span>
            <span class="text-xs text-on-surface-variant">&bull; Sistem Pembayaran Resmi CoC</span>
        </div>
        <h1 class="mt-2 font-display text-2xl sm:text-3xl font-bold text-secondary">
            {{ auth()->user()->hasRole('company') ? 'CoC Company Wallet & Payroll' : 'Dompet & Saldo Pendapatan' }}
        </h1>
        <p class="mt-1 text-xs sm:text-sm text-on-surface-variant">
            {{ auth()->user()->hasRole('company') 
                ? 'Kelola saldo deposit perusahaan untuk membayar gaji casual worker secara instan tanpa hambatan.' 
                : 'Pantau saldo penghasilan dari shift selesai, saldo tertunda, dan lakukan penarikan ke rekening bank.' }}
        </p>
    </div>



    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_1.3fr]">
        <!-- BALANCE CARD -->
        <div class="rounded-3xl bg-gradient-to-br from-[#1b2533] via-[#243042] to-[#2d3748] p-6 sm:p-8 text-white shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-slate-300 font-bold">
                        {{ auth()->user()->hasRole('company') ? 'Saldo Siap Bayar Gaji' : 'Saldo Siap Tarik (Ready)' }}
                    </span>
                    <div class="size-9 grid place-items-center rounded-xl bg-white/10 text-primary-soft">
                        <x-icon name="wallet" class="size-5" />
                    </div>
                </div>
                <strong class="mt-4 block font-display text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    Rp{{ number_format($wallet->balance, 0, ',', '.') }}
                </strong>
                <p class="mt-2 text-xs text-slate-300">
                    {{ auth()->user()->hasRole('company') 
                        ? 'Saldo ini langsung didebit saat Anda melunasi invoice gaji casual worker.' 
                        : 'Hasil jerih payah dari pekerjaan yang telah diverifikasi dan diselesaikan.' }}
                </p>
            </div>

            <div class="mt-8 border-t border-white/15 pt-5 text-xs sm:text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-300">Saldo Menunggu Pelunasan</span>
                    <b class="font-bold text-amber-400">Rp{{ number_format($wallet->pending_balance, 0, ',', '.') }}</b>
                </div>
            </div>
        </div>

        <!-- INTERACTIVE WALLET ACTIONS CONTAINER -->
        <div class="card !p-6 sm:!p-7 bg-white border border-black/5 shadow-sm">
            <!-- TAB SWITCHER -->
            <div class="flex items-center gap-2 pb-4 border-b border-black/5">
                <button type="button" id="tab-btn-topup" onclick="switchWalletTab('topup')" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold text-center transition {{ auth()->user()->hasRole('company') ? 'bg-primary text-white shadow-sm' : 'bg-surface-low text-secondary hover:bg-black/5' }}">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="payments" class="size-4" />
                        <span>Isi Saldo / Top Up (Gateway)</span>
                    </span>
                </button>
                <button type="button" id="tab-btn-withdraw" onclick="switchWalletTab('withdraw')" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold text-center transition {{ !auth()->user()->hasRole('company') ? 'bg-primary text-white shadow-sm' : 'bg-surface-low text-secondary hover:bg-black/5' }}">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="cash" class="size-4" />
                        <span>Tarik Saldo / Pencairan</span>
                    </span>
                </button>
            </div>

            <!-- TOP UP FORM (PAYMENT GATEWAY) -->
            <div id="panel-topup" class="{{ auth()->user()->hasRole('company') ? '' : 'hidden' }} pt-4">
                <form method="POST" action="{{ route('wallet.topup') }}">
                    @csrf
                    <div class="flex items-center justify-between pb-3">
                        <div>
                            <h2 class="font-display text-base font-bold text-secondary">Isi Saldo Wallet (Payment Gateway)</h2>
                            <p class="text-xs text-on-surface-variant">Top up saldo deposit instan via Virtual Account atau QRIS.</p>
                        </div>
                        <span class="badge !bg-emerald-50 !text-emerald-700 font-bold !text-[11px]">Instant 24/7</span>
                    </div>

                    <!-- Quick Amount Selector -->
                    <div class="mt-4">
                        <label class="label text-xs font-bold text-secondary">Pilih Cepat Nominal Top Up</label>
                        <div class="grid grid-cols-3 gap-2 mt-1.5">
                            <button type="button" onclick="document.getElementById('topup_amount').value = 500000" class="p-2 rounded-xl border border-black/10 hover:border-primary text-xs font-semibold text-center hover:bg-primary-soft/30 transition">
                                Rp500.000
                            </button>
                            <button type="button" onclick="document.getElementById('topup_amount').value = 1000000" class="p-2 rounded-xl border border-black/10 hover:border-primary text-xs font-semibold text-center hover:bg-primary-soft/30 transition">
                                Rp1.000.000
                            </button>
                            <button type="button" onclick="document.getElementById('topup_amount').value = 2500000" class="p-2 rounded-xl border border-black/10 hover:border-primary text-xs font-semibold text-center hover:bg-primary-soft/30 transition">
                                Rp2.500.000
                            </button>
                            <button type="button" onclick="document.getElementById('topup_amount').value = 5000000" class="p-2 rounded-xl border border-black/10 hover:border-primary text-xs font-semibold text-center hover:bg-primary-soft/30 transition">
                                Rp5.000.000
                            </button>
                            <button type="button" onclick="document.getElementById('topup_amount').value = 10000000" class="p-2 rounded-xl border border-black/10 hover:border-primary text-xs font-semibold text-center hover:bg-primary-soft/30 transition">
                                Rp10.000.000
                            </button>
                            <button type="button" onclick="document.getElementById('topup_amount').value = 25000000" class="p-2 rounded-xl border border-black/10 hover:border-primary text-xs font-semibold text-center hover:bg-primary-soft/30 transition">
                                Rp25.000.000
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="label text-xs font-bold text-secondary" for="topup_amount">Nominal Custom (Rp)</label>
                        <input class="input" id="topup_amount" name="amount" type="number" min="50000" step="10000" value="1000000" placeholder="Minimal Rp50.000" required>
                    </div>

                    <div class="mt-4">
                        <label class="label text-xs font-bold text-secondary">Metode Pembayaran Gateway</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <label class="cursor-pointer border border-black/10 p-2.5 rounded-xl hover:border-primary transition has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30 flex items-center gap-2">
                                <input type="radio" name="gateway_method" value="bca_va" checked class="text-primary focus:ring-primary">
                                <span class="text-xs font-semibold text-secondary">BCA Virtual Account</span>
                            </label>
                            <label class="cursor-pointer border border-black/10 p-2.5 rounded-xl hover:border-primary transition has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30 flex items-center gap-2">
                                <input type="radio" name="gateway_method" value="mandiri_va" class="text-primary focus:ring-primary">
                                <span class="text-xs font-semibold text-secondary">Mandiri VA</span>
                            </label>
                            <label class="cursor-pointer border border-black/10 p-2.5 rounded-xl hover:border-primary transition has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30 flex items-center gap-2">
                                <input type="radio" name="gateway_method" value="bri_va" class="text-primary focus:ring-primary">
                                <span class="text-xs font-semibold text-secondary">BRI Virtual Account</span>
                            </label>
                            <label class="cursor-pointer border border-black/10 p-2.5 rounded-xl hover:border-primary transition has-[:checked]:border-primary has-[:checked]:bg-primary-soft/30 flex items-center gap-2">
                                <input type="radio" name="gateway_method" value="qris" class="text-primary focus:ring-primary">
                                <span class="text-xs font-semibold text-secondary">QRIS (Semua E-Wallet)</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between pt-3 border-t border-black/5">
                        <span class="text-[11px] text-on-surface-variant">Biaya Gateway: <b>Gratis (Rp0)</b></span>
                        <button class="btn-primary compact" type="submit">
                            <x-icon name="payments" class="size-4" />
                            <span>Isi Saldo Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- WITHDRAWAL FORM -->
            <div id="panel-withdraw" class="{{ !auth()->user()->hasRole('company') ? '' : 'hidden' }} pt-4">
                <form method="POST" action="{{ route('wallet.withdraw') }}">
                    @csrf
                    <div class="flex items-center justify-between pb-3">
                        <div>
                            <h2 class="font-display text-base font-bold text-secondary">Tarik Saldo ke Rekening</h2>
                            <p class="text-xs text-on-surface-variant">Pencairan dana langsung ke bank atau dompet digital.</p>
                        </div>
                        <span class="badge !bg-primary-soft !text-primary font-bold !text-[11px]">Bebas Biaya</span>
                    </div>

                    <div class="mt-4">
                        <label class="label text-xs font-bold text-secondary" for="withdraw_amount">Jumlah Penarikan (Rp)</label>
                        <input class="input" id="withdraw_amount" name="amount" type="number" min="50000" step="5000" max="{{ (int)$wallet->balance }}" placeholder="Minimal Rp50.000" required>
                        <span class="text-[11px] text-on-surface-variant mt-1 block">Saldo maksimal yang dapat ditarik: Rp{{ number_format($wallet->balance, 0, ',', '.') }}</span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div>
                            <label class="label text-xs font-bold text-secondary" for="bank_name">Bank / E-Wallet</label>
                            <select class="input text-xs" id="bank_name" name="bank_name" required>
                                <option value="BCA">Bank BCA</option>
                                <option value="Mandiri">Bank Mandiri</option>
                                <option value="BRI">Bank BRI</option>
                                <option value="BNI">Bank BNI</option>
                                <option value="SeaBank">SeaBank</option>
                                <option value="GoPay">GoPay</option>
                                <option value="DANA">DANA</option>
                                <option value="OVO">OVO</option>
                            </select>
                        </div>
                        <div>
                            <label class="label text-xs font-bold text-secondary" for="account_number">Nomor Rekening / HP</label>
                            <input class="input text-xs" id="account_number" name="account_number" type="text" placeholder="Contoh: 8820129381" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="label text-xs font-bold text-secondary" for="account_holder">Nama Pemilik Rekening</label>
                        <input class="input text-xs" id="account_holder" name="account_holder" type="text" value="{{ auth()->user()->name }}" placeholder="Sesuai buku tabungan / e-wallet" required>
                    </div>

                    <div class="mt-5 flex items-center justify-between pt-3 border-t border-black/5">
                        <span class="text-[11px] text-on-surface-variant">Estimasi transfer: <b>1 - 5 Menit</b></span>
                        <button class="btn-primary compact" type="submit" @disabled($wallet->balance < 50000)>
                            <x-icon name="cash" class="size-4" />
                            <span>Tarik Dana Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TRANSACTIONS HISTORY TABLE -->
    <div class="mt-12">
        <div class="flex items-center justify-between pb-3 border-b border-black/5">
            <div>
                <h2 class="font-display text-2xl font-bold text-secondary">Riwayat Mutasi Dompet</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Seluruh arus kas masuk, isi saldo gateway, pembayaran gaji, dan penarikan.</p>
            </div>
            <a href="{{ route('reports.export.payments') }}" class="btn-ghost compact !text-xs !bg-surface-low border border-black/10">
                <x-icon name="description" class="size-3.5" />
                <span>Export Laporan Transaksi</span>
            </a>
        </div>

        <div class="mt-6 space-y-3">
            @forelse($transactions as $tx)
                <div class="card !p-4 bg-white border border-black/5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-primary/20 transition">
                    <div class="flex items-start gap-3">
                        <div class="size-10 grid place-items-center rounded-2xl shrink-0 {{ $tx->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            <x-icon name="{{ $tx->type === 'credit' ? 'arrow_downward' : 'arrow_upward' }}" class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-secondary">{{ $tx->description }}</p>
                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-on-surface-variant font-mono">
                                <span>{{ $tx->reference }}</span>
                                <span>&bull;</span>
                                <span>{{ $tx->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right pl-13 sm:pl-0">
                        <strong class="font-mono text-sm sm:text-base font-bold {{ $tx->type === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $tx->type === 'credit' ? '+' : '-' }}Rp{{ number_format($tx->amount, 0, ',', '.') }}
                        </strong>
                        <span class="block text-[11px] text-on-surface-variant">
                            Sisa: Rp{{ number_format($tx->balance_after, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="card py-12 text-center text-on-surface-variant text-xs">
                    Belum ada transaksi di dompet ini.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>
</section>

<script>
function switchWalletTab(tab) {
    const pTopup = document.getElementById('panel-topup');
    const pWithdraw = document.getElementById('panel-withdraw');
    const bTopup = document.getElementById('tab-btn-topup');
    const bWithdraw = document.getElementById('tab-btn-withdraw');

    if (tab === 'topup') {
        pTopup.classList.remove('hidden');
        pWithdraw.classList.add('hidden');
        bTopup.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold text-center transition bg-primary text-white shadow-sm';
        bWithdraw.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold text-center transition bg-surface-low text-secondary hover:bg-black/5';
    } else {
        pTopup.classList.add('hidden');
        pWithdraw.classList.remove('hidden');
        bWithdraw.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold text-center transition bg-primary text-white shadow-sm';
        bTopup.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold text-center transition bg-surface-low text-secondary hover:bg-black/5';
    }
}
</script>
@endsection