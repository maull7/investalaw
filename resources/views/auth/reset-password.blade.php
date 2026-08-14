@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- Left: Brand showcase --}}
        <div class="relative hidden lg:flex flex-col justify-between p-12 bg-navy-gradient text-white overflow-hidden">
            <div class="pointer-events-none absolute -top-32 -left-32 w-[28rem] h-[28rem] rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-32 w-[32rem] h-[32rem] rounded-full bg-[#0b2a55]/60 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px); background-size: 60px 60px;"></div>

            <div class="relative">
                <a href="/" class="inline-flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] flex items-center justify-center shadow-[0_10px_30px_rgba(201,154,62,.35)]">
                        <svg class="w-6 h-6 text-[#071b3a]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18M5 8l7-5 7 5M3 8h18M5 21h14M7 8v9M17 8v9"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold tracking-tight">{{ config('app.name', 'InvestaLaw') }}</p>
                        <p class="text-[11px] font-medium tracking-[0.18em] uppercase text-[#c99a3e]">Legal · Strategic</p>
                    </div>
                </a>
            </div>

            <div class="relative max-w-md">
                <p class="text-[11px] font-semibold tracking-[0.2em] uppercase text-[#c99a3e]">Compliance Workspace</p>
                <h2 class="mt-4 text-4xl xl:text-5xl font-bold leading-[1.1] tracking-tight">
                    Set a new <span class="text-gold-gradient">password</span>.
                </h2>
                <p class="mt-5 text-base text-white/70 leading-relaxed">
                    Choose a strong password to protect your compliance workspace.
                </p>
            </div>

            <div class="relative flex items-center justify-between text-xs text-white/50">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'InvestaLaw') }}. All rights reserved.</p>
            </div>
        </div>

        {{-- Right: Form --}}
        <div class="flex items-center justify-center px-6 sm:px-10 py-12 bg-white relative">
            <div class="absolute top-6 left-6 lg:hidden">
                <a href="/" class="inline-flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#071b3a]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18M5 8l7-5 7 5M3 8h18M5 21h14M7 8v9M17 8v9"/>
                        </svg>
                    </div>
                    <span class="text-base font-bold text-[#071833]">{{ config('app.name', 'InvestaLaw') }}</span>
                </a>
            </div>

            <div class="w-full max-w-md">
                <div class="text-center lg:text-left">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#c99a3e]/10 ring-1 ring-[#c99a3e]/25 text-[11px] font-semibold tracking-wider uppercase text-[#8c6a25]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#c99a3e]"></span>
                        Reset Password
                    </span>
                    <h1 class="mt-5 text-3xl sm:text-4xl font-bold tracking-tight text-[#071833]">Buat Password Baru</h1>
                    <p class="mt-2 text-sm text-[#667085]">Password minimal 8 karakter.</p>
                </div>

                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-3 p-4 rounded-2xl border border-rose-200/60 bg-rose-50/70">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        <div class="text-sm text-rose-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#071833] mb-2">Email <span class="text-[#c99a3e]">*</span></label>
                        <input type="email" name="email" id="email" value="{{ $email }}" readonly class="input-premium bg-[#f6f8fb]">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#071833] mb-2">Password Baru <span class="text-[#c99a3e]">*</span></label>
                        <input type="password" name="password" id="password" required autofocus autocomplete="new-password" class="input-premium" placeholder="Minimal 8 karakter">
                        @error('password')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-[#071833] mb-2">Konfirmasi Password <span class="text-[#c99a3e]">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="input-premium" placeholder="Ulangi password baru">
                    </div>

                    <x-button type="submit" variant="primary" size="lg" class="w-full">
                        Reset Password
                    </x-button>
                </form>
            </div>
        </div>
    </div>
@endsection