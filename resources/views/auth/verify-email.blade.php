@extends('layouts.guest')

@section('title', 'Verifikasi Email')

@section('content')
    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- Left: Brand showcase --}}
        <div class="relative hidden lg:flex flex-col justify-between p-12 bg-navy-gradient text-white overflow-hidden">
            <div
                class="pointer-events-none absolute -top-32 -left-32 w-[28rem] h-[28rem] rounded-full bg-[#c99a3e]/15 blur-3xl">
            </div>
            <div
                class="pointer-events-none absolute -bottom-32 -right-32 w-[32rem] h-[32rem] rounded-full bg-[#0b2a55]/60 blur-3xl">
            </div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
                style="background-image: linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px); background-size: 60px 60px;">
            </div>

            <div class="relative">
                <a href="/" class="inline-flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] flex items-center justify-center shadow-[0_10px_30px_rgba(201,154,62,.35)]">
                        <svg class="w-6 h-6 text-[#071b3a]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18M5 8l7-5 7 5M3 8h18M5 21h14M7 8v9M17 8v9" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold tracking-tight">{{ config('app.name') }}</p>
                        <p class="text-[11px] font-medium tracking-[0.18em] uppercase text-[#c99a3e]">Legal · Strategic</p>
                    </div>
                </a>
            </div>

            <div class="relative max-w-md">
                <p class="text-[11px] font-semibold tracking-[0.2em] uppercase text-[#c99a3e]">Aktivasi Akun</p>
                <h2 class="mt-4 text-4xl xl:text-5xl font-bold leading-[1.1] tracking-tight">
                    One step to unlock <span class="text-gold-gradient">your workspace</span>.
                </h2>
                <p class="mt-5 text-base text-white/70 leading-relaxed">
                    Konfirmasi alamat email untuk mengaktifkan akun dan mengakses seluruh fitur compliance review.
                </p>
            </div>

            <div class="relative flex items-center justify-between text-xs text-white/50">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'InvestaLaw') }}. All rights reserved.</p>
            </div>
        </div>

        {{-- Right: Verification --}}
        <div class="flex items-center justify-center px-6 sm:px-10 py-12 bg-white relative">
            <div class="absolute top-6 left-6 lg:hidden">
                <a href="/" class="inline-flex items-center gap-2">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#071b3a]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18M5 8l7-5 7 5M3 8h18M5 21h14M7 8v9M17 8v9" />
                        </svg>
                    </div>
                    <span class="text-base font-bold text-[#071833]">{{ config('app.name', 'InvestaLaw') }}</span>
                </a>
            </div>

            <div class="w-full max-w-md">
                <div class="text-center lg:text-left">
                    <span
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#c99a3e]/10 ring-1 ring-[#c99a3e]/25 text-[11px] font-semibold tracking-wider uppercase text-[#8c6a25]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#c99a3e]"></span>
                        Email Confirmation
                    </span>
                    <h1 class="mt-5 text-3xl sm:text-4xl font-bold tracking-tight text-[#071833]">Verifikasi email Anda</h1>
                    <p class="mt-2 text-sm text-[#667085]">
                        Kami telah mengirim link aktivasi ke
                        <strong class="text-[#071833]">{{ auth()->user()->email }}</strong>.
                        Buka email lalu klik tombol verifikasi untuk mengaktifkan akun.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="mt-6 flex items-center gap-3 p-4 rounded-2xl border border-emerald-200/60 bg-emerald-50/70 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mt-8 p-6 rounded-2xl border border-[#e7eaf0] bg-[#fafbfd]">
                    <p class="text-sm text-[#667085] leading-relaxed">
                        Tidak menerima email? Periksa folder spam, atau kirim ulang link verifikasi.
                    </p>
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
                        @csrf
                        <x-button type="submit" variant="primary" size="lg" class="w-full">
                            Kirim Ulang Link Verifikasi
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0v1.5a2.25 2.25 0 0 0 4.5 0V12a9 9 0 1 0-9 9" />
                            </svg>
                        </x-button>
                    </form>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-6">
                    @csrf
                    <button type="submit"
                        class="w-full text-center text-xs font-semibold text-[#667085] hover:text-[#c99a3e] transition">
                        Keluar dari akun
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection