<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CoC') &middot; CoC (Casual on Call)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface text-secondary flex flex-col justify-between">
<header class="sticky top-0 z-50 border-b border-[#eadadb] bg-white/95 backdrop-blur-xl">
    <nav class="mx-auto flex h-20 max-w-[1440px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
                <span class="brand-mark"><i></i><i></i><i></i></span>
                <span class="font-display text-xl font-bold tracking-tight">CoC</span>
            </a>
        </div>

        <div class="hidden items-center gap-6 lg:flex">
            <a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.index', 'jobs.show') ? 'active' : '' }}">Lowongan</a>
            @guest
                <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">Perusahaan</a>
                <a href="{{ route('home') }}#how-it-works" class="nav-link">Cara Kerja</a>
            @else
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                @if(auth()->user()->hasRole('worker'))
                    <a href="{{ route('applications.index') }}" class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">Lamaran Saya</a>
                    <a href="{{ route('jobs.my') }}" class="nav-link {{ request()->routeIs('jobs.my') ? 'active' : '' }}">Pekerjaan Saya</a>
                    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">Kehadiran</a>
                @else
                    <a href="{{ route('jobs.manage') }}" class="nav-link {{ request()->routeIs('jobs.manage') ? 'active' : '' }}">Kelola Lowongan</a>
                    <a href="{{ route('applications.index') }}" class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">Daftar Pelamar</a>
                @endif
                <a href="{{ route('reviews.index') }}" class="nav-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}">Reviews</a>
                <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">Pesan</a>
                <a href="{{ route('wallet.index') }}" class="nav-link {{ request()->routeIs('wallet.*') ? 'active' : '' }}">Wallet</a>
            @endguest
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @guest
                <a href="{{ route('login') }}" class="btn-ghost !px-4">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary compact">Daftar</a>
            @else
                <a href="{{ route('notifications.index') }}" class="icon-button relative" title="Notifikasi" aria-label="Notifikasi">
                    <x-icon name="notifications" />
                    @if(auth()->user()->unreadNotifications()->count() > 0)
                        <span class="absolute top-2 right-2 size-2.5 rounded-full bg-primary ring-2 ring-white"></span>
                    @endif
                </a>
                <a href="{{ auth()->user()->hasRole('worker') ? route('profile.edit') : route('settings.index') }}" class="icon-button" title="Akun saya" aria-label="Akun saya">
                    <x-icon name="person" />
                </a>
                <form method="post" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button class="btn-primary compact hidden sm:inline-flex" title="Keluar">Keluar</button>
                </form>
            @endguest

            <button type="button" id="mobile-menu-toggle" class="icon-button lg:hidden" aria-label="Buka Menu" onclick="document.getElementById('mobile-drawer').classList.toggle('hidden')">
                <x-icon name="swap_vert" />
            </button>
        </div>
    </nav>
</header>

<!-- Mobile Navigation Drawer -->
<div id="mobile-drawer" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm lg:hidden" onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="absolute right-0 top-0 h-full w-[280px] bg-white p-6 shadow-2xl flex flex-col justify-between overflow-y-auto">
        <div>
            <div class="flex items-center justify-between pb-6 border-b border-black/10">
                <div class="flex items-center gap-2">
                    <span class="brand-mark small"><i></i><i></i><i></i></span>
                    <span class="font-display font-bold">Menu CoC</span>
                </div>
                <button type="button" class="icon-button !size-9" onclick="document.getElementById('mobile-drawer').classList.add('hidden')">
                    <x-icon name="close" />
                </button>
            </div>
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('jobs.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('jobs.index') ? 'bg-primary-soft text-primary' : '' }}">Cari Lowongan</a>
                @guest
                    <a href="{{ route('companies.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary">Perusahaan</a>
                    <a href="{{ route('home') }}#how-it-works" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary">Cara Kerja</a>
                    <div class="mt-4 pt-4 border-t border-black/5 flex flex-col gap-2">
                        <a href="{{ route('login') }}" class="btn-ghost justify-start">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary compact">Daftar Sekarang</a>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('dashboard') ? 'bg-primary-soft text-primary' : '' }}">Dashboard</a>
                    @if(auth()->user()->hasRole('worker'))
                        <a href="{{ route('applications.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('applications.*') ? 'bg-primary-soft text-primary' : '' }}">Lamaran Saya</a>
                        <a href="{{ route('jobs.my') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('jobs.my') ? 'bg-primary-soft text-primary' : '' }}">Pekerjaan Saya</a>
                        <a href="{{ route('attendance.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('attendance.*') ? 'bg-primary-soft text-primary' : '' }}">Kehadiran</a>
                        <a href="{{ route('profile.edit') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('profile.*') ? 'bg-primary-soft text-primary' : '' }}">Profil & CV</a>
                    @else
                        <a href="{{ route('jobs.manage') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('jobs.manage') ? 'bg-primary-soft text-primary' : '' }}">Kelola Lowongan</a>
                        <a href="{{ route('applications.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('applications.*') ? 'bg-primary-soft text-primary' : '' }}">Daftar Pelamar</a>
                    @endif
                    <a href="{{ route('reviews.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('reviews.*') ? 'bg-primary-soft text-primary' : '' }}">Reviews & Rating</a>
                    <a href="{{ route('messages.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('messages.*') ? 'bg-primary-soft text-primary' : '' }}">Pesan</a>
                    <a href="{{ route('wallet.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('wallet.*') ? 'bg-primary-soft text-primary' : '' }}">Wallet</a>
                    <a href="{{ route('settings.index') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-primary-soft hover:text-primary {{ request()->routeIs('settings.*') ? 'bg-primary-soft text-primary' : '' }}">Pengaturan Akun</a>
                @endguest
            </div>
        </div>

        @auth
            <div class="pt-4 border-t border-black/10">
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-primary compact w-full">Keluar Akun</button>
                </form>
            </div>
        @endauth
    </div>
</div>

@auth
<nav class="sticky top-20 z-40 flex gap-2 overflow-x-auto border-b border-black/5 bg-white px-4 py-2.5 lg:hidden">
    <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('jobs.index') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('jobs.index') }}">Lowongan</a>
    <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('dashboard') }}">Dashboard</a>
    @if(auth()->user()->hasRole('worker'))
        <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('applications.index') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('applications.index') }}">Lamaran</a>
        <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('jobs.my') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('jobs.my') }}">Pekerjaan Saya</a>
        <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('attendance.index') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('attendance.index') }}">Kehadiran</a>
    @else
        <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('jobs.manage') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('jobs.manage') }}">Kelola Job</a>
        <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('applications.index') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('applications.index') }}">Pelamar</a>
    @endif
    <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('reviews.index') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('reviews.index') }}">Reviews</a>
    <a class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold {{ request()->routeIs('messages.index') ? 'bg-primary text-white' : 'bg-surface-container text-neutral' }}" href="{{ route('messages.index') }}">Pesan</a>
</nav>
@endauth

@if(session('success'))
<div class="mx-auto mt-5 w-full max-w-7xl px-4 sm:px-6 lg:px-10">
    <div class="alert-success">
        <x-icon name="check_circle" class="size-5 shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

@if($errors->any())
<div class="mx-auto mt-5 w-full max-w-7xl px-4 sm:px-6 lg:px-10">
    <div class="alert-error">
        <x-icon name="error" class="size-5 shrink-0" />
        <span>{{ $errors->first() }}</span>
    </div>
</div>
@endif

<main class="flex-1">
    @yield('content')
</main>

<footer class="border-t border-[#eadadb] bg-white mt-20">
    <div class="mx-auto max-w-[1440px] px-5 py-14 lg:px-10">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
            <div class="sm:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="brand-mark small"><i></i><i></i><i></i></span>
                    <span class="font-display text-xl font-bold">CoC</span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-6 text-on-surface-variant">Menghubungkan talenta casual terbaik dengan perusahaan paling inovatif di Indonesia.</p>
            </div>
            <div>
                <h3 class="footer-title">Perusahaan</h3>
                <a href="{{ route('companies.index') }}">Direktori Perusahaan</a>
                <a href="{{ route('help.index') }}">Pusat Bantuan</a>
                <a href="{{ route('reviews.index') }}">Review & Reputasi</a>
            </div>
            <div>
                <h3 class="footer-title">Peluang Kerja</h3>
                <a href="{{ route('jobs.index') }}">Cari Lowongan</a>
                <a href="{{ route('register') }}?role=company">Posting Lowongan</a>
                <a href="{{ route('register') }}?role=worker">Daftar Jadi Worker</a>
            </div>
            <div>
                <h3 class="footer-title">Bantuan & Legal</h3>
                <a href="{{ route('help.index') }}">Pusat Bantuan</a>
                <a href="#">Syarat & Ketentuan</a>
                <a href="#">Kebijakan Privasi</a>
            </div>
        </div>
        <div class="mt-12 flex flex-col justify-between gap-4 border-t border-[#eadadb] pt-8 text-xs text-on-surface-variant sm:flex-row sm:items-center">
            <span>&copy; {{ date('Y') }} CoC (Casual on Call). Seluruh hak dilindungi.</span>
            <span>Platform Kerja Casual Fleksibel & Terpercaya</span>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type=password]').forEach((input) => {
        const wrap = input.parentElement;
        if (wrap.classList.contains('password-wrap')) return;
        
        wrap.classList.add('password-wrap', 'relative');
        input.classList.add('pr-12');
        
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Lihat password');
        btn.className = 'absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center text-neutral hover:text-primary p-1.5 rounded-lg transition-colors';
        btn.innerHTML = `<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
        
        btn.onclick = () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Lihat password');
            btn.innerHTML = isPassword 
                ? `<svg class="size-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>`
                : `<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
        };
        wrap.appendChild(btn);
    });
});
</script>
</body>
</html>


