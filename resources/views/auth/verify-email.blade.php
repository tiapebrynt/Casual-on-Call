@extends('layouts.app')
@section('title','Verifikasi Email')
@section('content')
<section class="relative grid min-h-[70vh] place-items-center overflow-hidden px-5 py-16"><div class="absolute size-[500px] rounded-full bg-primary/10 blur-3xl"></div><div class="card relative max-w-xl text-center"><div class="mx-auto grid size-20 place-items-center rounded-full bg-primary-soft text-primary"><x-icon name="description" class="size-9"/></div><h1 class="mt-7 font-display text-3xl font-bold">Verifikasi email kamu</h1><p class="mt-4 leading-7 text-on-surface-variant">Kami mengirim tautan verifikasi ke email yang terdaftar. Buka email tersebut untuk mengaktifkan seluruh fitur akun.</p><button class="btn-primary mt-8 w-full">Kirim ulang email</button><a href="{{ route('dashboard') }}" class="mt-5 inline-block font-semibold text-primary">Kembali ke dashboard</a></div></section>
@endsection

