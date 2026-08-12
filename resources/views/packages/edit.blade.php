@extends('layouts.app')

@section('title', 'Edit Paket')
@section('header', 'Edit Paket')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Master Paket</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Edit Paket</h3>
                        <p class="text-sm text-[#667085] mt-1">Perbarui harga, benefit, dan tampilan paket.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('packages.update', $package) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-[#071833] mb-2">Nama Paket <span
                                    class="text-[#c99a3e]">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" required
                                maxlength="255" class="input-premium" placeholder="Contoh: Dasar">
                            @error('name')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-semibold text-[#071833] mb-2">Harga <span
                                    class="text-[#c99a3e]">*</span></label>
                            <input type="text" name="price" id="price" value="{{ old('price', $package->price) }}" required
                                maxlength="255" class="input-premium"
                                placeholder="Tampil sebagai Rp{price}. Contoh: 5 atau Custom">
                            @error('price')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price_period" class="block text-sm font-semibold text-[#071833] mb-2">Periode</label>
                            <input type="text" name="price_period" id="price_period"
                                value="{{ old('price_period', $package->price_period) }}" maxlength="255"
                                class="input-premium" placeholder="Contoh: /bulan">
                            @error('price_period')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tagline" class="block text-sm font-semibold text-[#071833] mb-2">Deskripsi Singkat</label>
                            <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $package->tagline) }}"
                                maxlength="255" class="input-premium" placeholder="Contoh: Menghindari risiko sengketa kepatuhan">
                            @error('tagline')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs text-[#667085]">
                                Gambar QRIS diambil dari file <span class="font-mono">public/qris/qris.png</span> (hardcode).
                                Taruh gambar QRIS Anda di lokasi tersebut.
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="benefits" class="block text-sm font-semibold text-[#071833] mb-2">Benefit <span
                                class="text-[#c99a3e]">*</span></label>
                        <textarea name="benefits" id="benefits" rows="6" required
                            class="input-premium input-textarea w-full"
                            placeholder="Satu benefit per baris. Contoh:&#10;Review 1 dokumen per bulan&#10;Konsultasi email">{{ old('benefits', implode("\n", $package->benefits ?? [])) }}</textarea>
                        <p class="mt-1.5 text-xs text-[#667085]">Tulis satu benefit tiap baris.</p>
                        @error('benefits')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_popular" id="is_popular" value="1"
                                {{ old('is_popular', $package->is_popular) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-[#d0d5dd] text-[#c99a3e] focus:ring-[#c99a3e]">
                            <span class="text-sm font-semibold text-[#071833]">Tandai "Paling Populer"</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $package->is_active) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-[#d0d5dd] text-[#c99a3e] focus:ring-[#c99a3e]">
                            <span class="text-sm font-semibold text-[#071833]">Aktif</span>
                        </label>

                        <div class="sm:ml-auto">
                            <label for="sort" class="block text-sm font-semibold text-[#071833] mb-1">Urutan</label>
                            <input type="number" name="sort" id="sort" value="{{ old('sort', $package->sort) }}" min="0"
                                class="input-premium w-24" placeholder="0">
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Simpan Perubahan</x-button>
                        <x-button href="{{ route('packages.index') }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection