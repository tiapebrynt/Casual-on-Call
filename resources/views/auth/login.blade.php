@extends('layouts.app')
@section('title', 'Masuk - CoC (Casual on Call)')
@section('content')
<section class="min-h-screen bg-[#F4F6F8] flex items-center justify-center px-4 py-8 sm:py-12">
    <!-- WHITE CARD CONTAINER -->
    <div class="w-full max-w-[460px] mx-auto bg-white rounded-3xl sm:rounded-[32px] p-6 sm:p-10 shadow-xl shadow-slate-200/80 border border-slate-200/80 flex flex-col items-center">
        
        <!-- TOP BRAND ICON BADGE -->
        <div class="size-16 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center shadow-sm">
            <svg class="size-8 text-[#D60036]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
                <path d="M9 22v-4h6v4M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01M8 18h.01M16 18h.01"/>
            </svg>
        </div>

        <!-- APP NAME & SUBTITLE -->
        <h1 class="mt-4 font-display text-xl sm:text-2xl font-bold tracking-tight text-[#9E0A2B]">Casual on Call</h1>
        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">MARKETPLACE TENAGA KERJA CASUAL</p>

        <!-- WELCOME HEADING & INSTRUCTION -->
        <h2 class="mt-7 font-display text-2xl sm:text-[26px] font-bold text-slate-900 text-center">Selamat Datang Kembali</h2>
        <p class="mt-2 text-center text-xs sm:text-sm text-slate-500 max-w-sm leading-relaxed">
            Silakan masuk menggunakan No. ID Pekerjaan atau Email Anda
        </p>

        <!-- FLASH NOTIFICATION -->
        @if(session('success'))
            <div class="mt-6 w-full rounded-2xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs sm:text-sm text-emerald-800 flex items-center gap-2.5">
                <x-icon name="check_circle" class="size-5 text-emerald-600 shrink-0" />
                <span>{{ session('success') }}</span>
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

        <!-- LOGIN FORM -->
        <form method="POST" action="{{ route('login') }}" class="mt-7 w-full space-y-4">
            @csrf

            <!-- FIELD 1: NO ID / EMAIL -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5" for="login">
                    No. ID Pekerjaan / Email
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-400 pointer-events-none flex items-center">
                        <x-icon name="shield_check" class="size-5" />
                    </span>
                    <input 
                        id="login" 
                        name="login" 
                        type="text" 
                        value="{{ old('login', old('email')) }}" 
                        placeholder="Contoh: worker@casualhub.id atau ID" 
                        required 
                        autofocus 
                        class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#D60036] focus:outline-none focus:ring-4 focus:ring-rose-500/10 transition shadow-sm"
                    >
                </div>
            </div>

            <!-- FIELD 2: KATA SANDI -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5" for="password">
                    Kata Sandi
                </label>
                <div class="relative flex items-center password-wrap">
                    <span class="absolute left-3.5 text-slate-400 pointer-events-none flex items-center">
                        <x-icon name="lock" class="size-5" />
                    </span>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        placeholder="Masukkan kata sandi" 
                        required 
                        class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#D60036] focus:outline-none focus:ring-4 focus:ring-rose-500/10 transition shadow-sm"
                    >
                    <button 
                        type="button" 
                        onclick="toggleLoginPassword()" 
                        id="toggle-pwd-btn"
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-slate-600 transition" 
                        aria-label="Lihat kata sandi"
                    >
                        <x-icon name="eye" class="size-5" />
                    </button>
                </div>

                <!-- LUPA KATA SANDI LINK -->
                <div class="mt-2 text-left">
                    <a href="{{ route('password.request') }}" class="text-xs sm:text-sm font-semibold text-[#D60036] hover:underline">
                        Lupa Kata Sandi?
                    </a>
                </div>
            </div>

            <!-- CHECKBOX INGAT SAYA -->
            <div class="pt-1">
                <label class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-600 select-none cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        value="1" 
                        class="size-4 rounded border-slate-300 text-[#D60036] accent-[#D60036] focus:ring-rose-500"
                    >
                    <span>Ingat saya</span>
                </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <button 
                type="submit" 
                class="mt-6 w-full py-3.5 px-6 rounded-xl font-bold text-white bg-[#D60036] hover:bg-[#B3002D] active:scale-[0.99] transition shadow-lg shadow-rose-500/25 flex items-center justify-center gap-2 text-sm sm:text-base cursor-pointer"
            >
                <span>Masuk</span>
                <x-icon name="arrow_forward" class="size-4" />
            </button>
        </form>

        <!-- FOOTER LINKS -->
        <p class="mt-8 text-center text-xs sm:text-sm text-slate-500">
            Butuh bantuan? <a href="{{ route('help.index') }}" class="font-semibold text-[#D60036] hover:underline">Hubungi Administrator Sistem</a>
        </p>
        <p class="mt-2 text-center text-xs sm:text-sm text-slate-500">
            Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-[#D60036] hover:underline">Daftar sekarang</a>
        </p>

        <!-- QUICK FILL AKUN DEMO -->
        <details class="mt-8 w-full rounded-2xl border border-slate-200 bg-slate-50/70 p-3 text-xs text-slate-600">
            <summary class="font-semibold cursor-pointer text-slate-700 hover:text-primary flex items-center justify-between">
                <span>🔑 Akun Demo (Klik untuk Isi Otomatis)</span>
                <x-icon name="chevron_right" class="size-4" />
            </summary>
            <div class="mt-3 grid gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="fillLogin('worker@casualhub.id', 'Password123!')" class="p-2 rounded-xl bg-white hover:bg-rose-50 text-left border border-slate-200 flex items-center justify-between transition">
                    <div>
                        <strong class="text-slate-800 block">Worker (Rizky Pratama)</strong>
                        <span class="text-[11px] text-slate-500">worker@casualhub.id (Password: Password123!)</span>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-primary px-2 py-0.5 rounded-lg bg-rose-50">Isi</span>
                </button>
                <button type="button" onclick="fillLogin('company@casualhub.id', 'Password123!')" class="p-2 rounded-xl bg-white hover:bg-rose-50 text-left border border-slate-200 flex items-center justify-between transition">
                    <div>
                        <strong class="text-slate-800 block">Perusahaan (Nusa Hospitality)</strong>
                        <span class="text-[11px] text-slate-500">company@casualhub.id (Password: Password123!)</span>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-primary px-2 py-0.5 rounded-lg bg-rose-50">Isi</span>
                </button>
                <button type="button" onclick="fillLogin('admin@casualhub.id', 'Password123!')" class="p-2 rounded-xl bg-white hover:bg-rose-50 text-left border border-slate-200 flex items-center justify-between transition">
                    <div>
                        <strong class="text-slate-800 block">Administrator</strong>
                        <span class="text-[11px] text-slate-500">admin@casualhub.id (Password: Password123!)</span>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-primary px-2 py-0.5 rounded-lg bg-rose-50">Isi</span>
                </button>
            </div>
        </details>
    </div>
</section>

<script>
function toggleLoginPassword() {
    const input = document.getElementById('password');
    const btn = document.getElementById('toggle-pwd-btn');
    if (!input || !btn) return;
    
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
    btn.innerHTML = isPassword 
        ? `<svg class="size-5 text-[#D60036]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>`
        : `<svg class="size-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
}

function fillLogin(login, password) {
    const loginInput = document.getElementById('login');
    const passInput = document.getElementById('password');
    if (loginInput) loginInput.value = login;
    if (passInput) passInput.value = password;
}
</script>
@endsection


