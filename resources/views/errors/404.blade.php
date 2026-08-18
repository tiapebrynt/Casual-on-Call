@extends('layouts.app')
@section('title','Halaman Tidak Ditemukan')
@section('content')
<section class="grid min-h-[65vh] place-items-center px-5 py-16 text-center"><div><p class="font-display text-8xl font-bold text-primary/20">404</p><h1 class="mt-4 font-display text-4xl font-bold">Halaman tidak ditemukan.</h1><p class="mt-4 text-on-surface-variant">Halaman yang kamu cari mungkin dipindahkan atau sudah tidak tersedia.</p><a href="{{ route('home') }}" class="btn-primary mt-8">Kembali ke beranda</a></div></section>
@endsection
