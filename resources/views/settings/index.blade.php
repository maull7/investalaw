@extends('layouts.app')

@section('title', 'Setting Paket Trial')
@section('header', 'Setting Paket Trial')

@section('content')
    <div class="mb-6">
        <p class="text-sm text-[#667085]">Atur kebijakan aktivasi paket trial dan batas pemakaian AI.</p>
    </div>

    <x-card>
        <form method="POST" action="{{ route('settings.update') }}" class="max-w-2xl">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="trial_requires_confirmation" class="block text-sm font-semibold text-[#071833] mb-2">
                        Trial perlu konfirmasi admin?
                    </label>
                    <select name="trial_requires_confirmation" id="trial_requires_confirmation" class="input-premium"
                        @error('trial_requires_confirmation') aria-invalid="true" @enderror>
                        <option value="0" @selected(! \App\Models\Setting::get('trial_requires_confirmation', '0'))>Tidak — trial langsung aktif</option>
                        <option value="1" @selected(\App\Models\Setting::get('trial_requires_confirmation', '0'))>Ya — perlu konfirmasi admin dulu</option>
                    </select>
                    <p class="mt-1.5 text-xs text-[#667085]">Jika ya, user memilih trial akan diarahkan menunggu konfirmasi admin di menu Konfirmasi Pembayaran.</p>
                    @error('trial_requires_confirmation')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="trial_max_hours" class="block text-sm font-semibold text-[#071833] mb-2">
                        Jam pemakaian AI paket trial
                    </label>
                    <input type="number" name="trial_max_hours" id="trial_max_hours" min="1" max="8760"
                        value="{{ old('trial_max_hours', \App\Models\Setting::get('trial_max_hours', 48)) }}" required
                        class="input-premium" @error('trial_max_hours') aria-invalid="true" @enderror>
                    <p class="mt-1.5 text-xs text-[#667085]">Batas maksimal pemakaian AI untuk pengguna paket trial (dalam jam).</p>
                    @error('trial_max_hours')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit" variant="primary">Simpan Setting</x-button>
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white transition">
                    Batal
                </a>
            </div>
        </form>
    </x-card>
@endsection