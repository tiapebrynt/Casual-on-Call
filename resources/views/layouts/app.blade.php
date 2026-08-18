<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CasualHub') &middot; CasualHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface text-secondary">
<header class="sticky top-0 z-50 border-b border-[#eadadb] bg-white/90 backdrop-blur-xl">
    <nav class="mx-auto flex h-20 max-w-[1440px] items-center justify-between gap-6 px-5 lg:px-10">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3"><span class="brand-mark"><i></i><i></i><i></i></span><span class="font-display text-xl font-bold tracking-tight">CasualHub</span></a>
        <div class="hidden items-center gap-7 lg:flex">
            <a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.index', 'jobs.show') ? 'active' : '' }}">Lowongan</a>
            @guest
                <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">Perusahaan</a>
                <a href="{{ route('home') }}#how-it-works" class="nav-link">Cara Kerja</a>
            @else
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                @if(auth()->user()->hasRole('worker'))
                    <a href="{{ route('applications.index') }}" class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">Lamaran Saya</a>
                    <a href="{{ route('jobs.my') }}" class="nav-link {{ request()->routeIs('jobs.my') ? 'active' : '' }}">Pekerjaan Saya</a>
                @else
                    <a href="{{ route('jobs.manage') }}" class="nav-link {{ request()->routeIs('jobs.manage') ? 'active' : '' }}">Kelola Lowongan</a>
                    <a href="{{ route('applications.index') }}" class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">Pelamar</a>
                @endif
                <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">Pesan</a>
            @endguest
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            @guest<a href="{{ route('login') }}" class="btn-ghost">Masuk</a><a href="{{ route('register') }}" class="btn-primary compact">Daftar</a>@else
                <a href="{{ auth()->user()->hasRole('worker') ? route('profile.edit') : route('settings.index') }}" class="icon-button" title="Akun saya" aria-label="Akun saya"><x-icon name="person" /></a>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="btn-primary compact">Keluar</button></form>
            @endguest
        </div>
    </nav>
</header>
@auth
<nav class="sticky top-20 z-40 flex gap-2 overflow-x-auto border-b border-black/5 bg-white px-4 py-3 lg:hidden">
    <a class="whitespace-nowrap rounded-full bg-surface-container px-4 py-2 text-sm font-semibold" href="{{ route('jobs.index') }}">Lowongan</a>
    <a class="whitespace-nowrap rounded-full bg-surface-container px-4 py-2 text-sm font-semibold" href="{{ route('dashboard') }}">Dashboard</a>
    @if(auth()->user()->hasRole('worker'))
        <a class="whitespace-nowrap rounded-full bg-surface-container px-4 py-2 text-sm font-semibold" href="{{ route('applications.index') }}">Lamaran Saya</a>
        <a class="whitespace-nowrap rounded-full bg-surface-container px-4 py-2 text-sm font-semibold" href="{{ route('jobs.my') }}">Pekerjaan Saya</a>
    @else
        <a class="whitespace-nowrap rounded-full bg-surface-container px-4 py-2 text-sm font-semibold" href="{{ route('jobs.manage') }}">Kelola Lowongan</a>
        <a class="whitespace-nowrap rounded-full bg-surface-container px-4 py-2 text-sm font-semibold" href="{{ route('applications.index') }}">Pelamar</a>
    @endif
    <a class="whitespace-nowrap rounded-full bg-surface-container px-4 py-2 text-sm font-semibold" href="{{ route('messages.index') }}">Pesan</a>
</nav>
@endauth
@if(session('success'))<div class="mx-auto mt-5 max-w-7xl px-5"><div class="alert-success"><x-icon name="check_circle" />{{ session('success') }}</div></div>@endif
@if($errors->any())<div class="mx-auto mt-5 max-w-7xl px-5"><div class="alert-error"><x-icon name="error" />{{ $errors->first() }}</div></div>@endif
<main>@yield('content')</main>
<footer class="border-t border-[#eadadb] bg-white"><div class="mx-auto max-w-[1440px] px-5 py-16 lg:px-10"><div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-5"><div class="sm:col-span-2"><a href="{{ route('home') }}" class="flex items-center gap-3"><span class="brand-mark small"><i></i><i></i><i></i></span><span class="font-display text-xl font-bold">CasualHub</span></a><p class="mt-5 max-w-sm text-sm leading-6 text-on-surface-variant">Menghubungkan talenta casual terbaik dengan perusahaan paling inovatif di Indonesia.</p></div><div><h3 class="footer-title">Perusahaan</h3><a href="#">Tentang Kami</a><a href="#">Karier</a><a href="#">Berita</a></div><div><h3 class="footer-title">Produk</h3><a href="{{ route('jobs.index') }}">Cari Job</a><a href="{{ route('register') }}">Posting Job</a><a href="#">Fitur</a></div><div><h3 class="footer-title">Bantuan</h3><a href="#">Pusat Bantuan</a><a href="#">Kontak</a><a href="#">Keamanan</a></div></div><div class="mt-14 flex flex-col justify-between gap-5 border-t border-[#eadadb] pt-8 text-xs text-on-surface-variant sm:flex-row"><span>&copy; {{ date('Y') }} CasualHub. Seluruh hak dilindungi.</span><span>Privacy &middot; Terms &middot; Cookie Policy</span></div></div></footer>
</body></html>

