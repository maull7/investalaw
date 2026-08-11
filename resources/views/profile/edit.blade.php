@extends('layouts.app')

@section('title', 'Lengkapi Data Pribadi')
@section('header', 'Lengkapi Data Pribadi')

@section('content')
    {{-- Banner --}}
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/20 blur-3xl"></div>

        <div class="relative">
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                Data Pribadi
            </span>
            <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight">Lengkapi data pribadi Anda</h2>
            <p class="mt-2 text-white/70 text-sm max-w-xl">Institusi, jabatan, asal provinsi, dan nomor telepon dibutuhkan
                sebelum Anda dapat mengakses dashboard.</p>
        </div>
    </section>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            @unless (auth()->user()->hasCompletedProfile())
                <div class="mb-6 flex items-start gap-3 rounded-2xl bg-amber-50 ring-1 ring-amber-200 px-5 py-4">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <p class="text-sm font-semibold text-amber-800">Anda belum melengkapi data pribadi. Isi formulir di bawah
                        untuk mengaktifkan akses penuh.</p>
                </div>
            @endunless

            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-[#071833]">Form Data Pribadi</h3>
                </x-slot>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-2">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="institution" class="block text-sm font-semibold text-[#071833] mb-2">Institusi
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="institution" id="institution" value="{{ old('institution', auth()->user()->institution) }}"
                                required class="input-premium" placeholder="Nama kantor / institusi">
                            @error('institution')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="position" class="block text-sm font-semibold text-[#071833] mb-2">Jabatan
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="position" id="position" value="{{ old('position', auth()->user()->position) }}"
                                required class="input-premium" placeholder="Contoh: Compliance Officer">
                            @error('position')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="province" class="block text-sm font-semibold text-[#071833] mb-2">Asal Provinsi
                                <span class="text-rose-500">*</span></label>
                            <select name="province" id="province" required class="input-premium">
                                <option value="">Pilih provinsi</option>
                                @foreach (config('provinces') as $province)
                                    <option value="{{ $province }}"
                                        @selected(old('province', auth()->user()->province) === $province)>{{ $province }}</option>
                                @endforeach
                            </select>
                            @error('province')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-[#071833] mb-2">No. Telepon
                                <span class="text-rose-500">*</span></label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}"
                                required class="input-premium" placeholder="08xxxxxxxxxx">
                            @error('phone')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        @if (auth()->user()->hasCompletedProfile())
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white transition">
                                Kembali
                            </a>
                        @endif
                        <x-button type="submit" variant="primary">Simpan Data Pribadi</x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <div>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Kenapa perlu?</h3>
                </x-slot>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-8 h-8 rounded-lg bg-[#f6f8fb] text-[#c99a3e] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </span>
                        <p class="text-sm text-[#667085] leading-relaxed">Memungkinkan pendampingan hukum yang disesuaikan dengan profil kelembagaan Anda.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-8 h-8 rounded-lg bg-[#f6f8fb] text-[#c99a3e] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </span>
                        <p class="text-sm text-[#667085] leading-relaxed">Memverifikasi identitas untuk keamanan dan kepercayaan dalam setiap transaksi.</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection