@extends('layouts.app')
@section('title', 'Atur Ulang Kata Sandi - CoC (Casual on Call)')
@section('content')
<section class="min-h-screen bg-[#F4F6F8] flex items-center justify-center px-4 py-8 sm:py-12">
    <!-- WHITE CARD CONTAINER -->
    <div class="w-full max-w-[460px] mx-auto bg-white rounded-3xl sm:rounded-[32px] p-6 sm:p-10 shadow-xl shadow-slate-200/80 border border-slate-200/80 flex flex-col items-center">
        
        <!-- TOP BRAND ICON BADGE -->
        <div class="size-16 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shadow-sm">
            <svg class="size-8 text-[#D60036]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>

        <!-- APP NAME & SUBTITLE -->
        <h1 class="mt-4 font-display text-xl sm:text-2xl font-bold tracking-tight text-[#9E0A2B]">Casual on Call</h1>
        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">MARKETPLACE TENAGA KERJA CASUAL</p>

        <!-- HEADING & INSTRUCTION -->
        <h2 class="mt-7 font-display text-2xl sm:text-[26px] font-bold text-slate-900 text-center">Atur Kata Sandi Baru</h2>
        <p class="mt-2 text-center text-xs sm:text-sm text-slate-500 max-w-sm leading-relaxed">
            Silakan tentukan kata sandi baru untuk akun Anda (minimal 8 karakter).
        </p>

        <!-- FLASH NOTIFICATION -->
        @if(session('status'))
            <div class="mt-6 w-full rounded-2xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs sm:text-sm text-emerald-800 flex items-center gap-2.5">
                <x-icon name="check_circle" class="size-5 text-emerald-600 shrink-0" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mt-6 w-full rounded-2xl bg-rose-50 border border-rose-200 p-3.5 text-xs sm:text-sm text-rose-800 flex items-start gap-2.5">
                <x-icon name="error" class="size-5 text-rose-600 shrink-0 mt-0.5" />
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- RESET PASSWORD FORM -->
        <form method="POST" action="{{ route('password.update') }}" class="mt-7 w-full space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- EMAIL (TERVERIFIKASI) -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5" for="email">
                    Alamat Email Akun
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-400 pointer-events-none flex items-center">
                        <x-icon name="shield_check" class="size-5" />
                    </span>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        value="{{ old('email', $email) }}" 
                        required 
                        readonly
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-600 focus:outline-none transition shadow-sm cursor-not-allowed"
                    >
                </div>
            </div>

            <!-- KATA SANDI BARU -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5" for="password">
                    Kata Sandi Baru
                </label>
                <div class="relative flex items-center password-wrap">
                    <span class="absolute left-3.5 text-slate-400 pointer-events-none flex items-center">
                        <x-icon name="lock" class="size-5" />
                    </span>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        placeholder="Minimal 8 karakter" 
                        required 
                        autofocus
                        class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#D60036] focus:outline-none focus:ring-4 focus:ring-rose-500/10 transition shadow-sm"
                    >
                </div>
            </div>

            <!-- KONFIRMASI KATA SANDI BARU -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5" for="password_confirmation">
                    Konfirmasi Kata Sandi Baru
                </label>
                <div class="relative flex items-center password-wrap">
                    <span class="absolute left-3.5 text-slate-400 pointer-events-none flex items-center">
                        <x-icon name="lock" class="size-5" />
                    </span>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        placeholder="Ulangi kata sandi baru" 
                        required 
                        class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#D60036] focus:outline-none focus:ring-4 focus:ring-rose-500/10 transition shadow-sm"
                    >
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button 
                type="submit" 
                class="mt-6 w-full py-3.5 px-6 rounded-xl font-bold text-white bg-[#D60036] hover:bg-[#B3002D] active:scale-[0.99] transition shadow-lg shadow-rose-500/25 flex items-center justify-center gap-2 text-sm sm:text-base cursor-pointer"
            >
                <span>Simpan Kata Sandi Baru</span>
                <x-icon name="arrow_forward" class="size-4" />
            </button>
        </form>

        <!-- BACK TO LOGIN LINK -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-[#D60036] inline-flex items-center gap-1.5 transition">
                <span>&larr; Batalkan dan Kembali ke Masuk</span>
            </a>
        </div>
    </div>
</section>
@endsection