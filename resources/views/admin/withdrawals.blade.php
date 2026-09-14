@extends('layouts.app')

@section('title', 'Manajemen Pencairan Saldo (Withdrawals)')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-10 lg:py-12">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="badge uppercase tracking-wider !text-xs font-bold !bg-primary-soft !text-primary">ADMIN DISBURSEMENT</span>
                <span class="text-xs text-on-surface-variant">&bull; Midtrans Iris & Bank Transfer</span>
            </div>
            <h1 class="mt-2 font-display text-3xl font-bold text-secondary">Permintaan Pencairan Saldo</h1>
            <p class="mt-1 text-xs sm:text-sm text-on-surface-variant">
                Kelola dan eksekusi pencairan saldo worker dan company ke rekening bank / e-wallet tujuan secara otomatis via Midtrans Iris atau konfirmasi manual.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-ghost compact">
            <span>&larr; Dashboard</span>
        </a>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-center gap-3">
            <x-icon name="check_circle" class="size-5 text-emerald-600 shrink-0" />
            <span class="text-xs sm:text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mt-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-800 space-y-1">
            <div class="flex items-center gap-2 font-bold text-xs sm:text-sm">
                <x-icon name="close" class="size-4 text-rose-600 shrink-0" />
                <span>Terjadi kendala saat memproses:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-6">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 overflow-hidden rounded-3xl bg-white border border-black/5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-surface-low text-xs uppercase font-bold text-on-surface-variant border-b border-black/5">
                    <tr>
                        <th class="p-4 sm:p-5">Pemohon</th>
                        <th class="p-4 sm:p-5">Rekening / E-Wallet Tujuan</th>
                        <th class="p-4 sm:p-5 text-right">Nominal</th>
                        <th class="p-4 sm:p-5 text-center">Status</th>
                        <th class="p-4 sm:p-5 text-center">Aksi / Eksekusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                @forelse($withdrawals as $withdrawal)
                    <tr class="hover:bg-black/[0.01] transition">
                        <td class="p-4 sm:p-5">
                            <strong class="font-bold text-secondary text-sm block">{{ $withdrawal->user->name }}</strong>
                            <span class="text-[11px] text-on-surface-variant block">{{ $withdrawal->user->email }}</span>
                            <span class="text-[10px] text-on-surface-variant font-mono mt-0.5 block">{{ $withdrawal->created_at->format('d M Y, H:i') }}</span>
                        </td>
                        <td class="p-4 sm:p-5">
                            <div class="flex items-center gap-1.5 font-bold text-secondary">
                                <span class="badge !bg-primary-soft !text-primary !text-[10px] font-bold">{{ $withdrawal->bank_name }}</span>
                                <span class="font-mono text-xs">{{ $withdrawal->account_number }}</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant block mt-0.5">a/n <b>{{ $withdrawal->account_holder }}</b></span>
                        </td>
                        <td class="p-4 sm:p-5 text-right font-mono font-bold text-base text-secondary">
                            Rp{{ number_format($withdrawal->amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 sm:p-5 text-center">
                            <span class="badge !text-[10px] font-bold {{ $withdrawal->status === 'approved' ? '!bg-emerald-100 !text-emerald-800' : ($withdrawal->status === 'rejected' ? '!bg-rose-100 !text-rose-800' : '!bg-amber-100 !text-amber-800') }}">
                                {{ strtoupper($withdrawal->status) }}
                            </span>
                            @if($withdrawal->admin_note)
                                <small class="mt-1 block text-[10px] text-on-surface-variant max-w-[160px] mx-auto truncate" title="{{ $withdrawal->admin_note }}">
                                    {{ $withdrawal->admin_note }}
                                </small>
                            @endif
                            @if($withdrawal->processed_at)
                                <span class="text-[10px] text-on-surface-variant font-mono block mt-0.5">
                                    {{ $withdrawal->processed_at->format('d/m/y H:i') }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4 sm:p-5 text-center">
                            @if($withdrawal->status === 'pending')
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-1.5">
                                    <!-- Midtrans Iris Payout Button -->
                                    <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                                        @csrf
                                        <input type="hidden" name="mode" value="midtrans">
                                        <button class="btn-primary compact !text-[11px] !py-1.5 !px-2.5 whitespace-nowrap" type="submit" onclick="return confirm('Proses disbursement otomatis via Midtrans Iris?')">
                                            <x-icon name="payments" class="size-3.5" />
                                            <span>Midtrans Payout</span>
                                        </button>
                                    </form>

                                    <!-- Manual Approve Button -->
                                    <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                                        @csrf
                                        <input type="hidden" name="mode" value="manual">
                                        <button class="btn-secondary compact !text-[11px] !py-1.5 !px-2.5 whitespace-nowrap" type="submit" onclick="return confirm('Setujui penarikan ini setelah transfer bank manual?')">
                                            <span>Setujui Manual</span>
                                        </button>
                                    </form>

                                    <!-- Reject Form -->
                                    <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" onsubmit="const reason = prompt('Masukkan alasan penolakan (opsional):'); if (reason === null) return false; this.querySelector('[name=admin_note]').value = reason; return true;">
                                        @csrf
                                        <input type="hidden" name="admin_note" value="">
                                        <button class="btn-ghost compact !text-rose-600 hover:!bg-rose-50 !text-[11px] !py-1.5 !px-2.5" type="submit">
                                            <span>Tolak</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-on-surface-variant font-medium">Selesai Diproses</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-on-surface-variant text-xs">
                            Belum ada permintaan pencairan dana.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $withdrawals->links() }}
    </div>
</section>
@endsection
