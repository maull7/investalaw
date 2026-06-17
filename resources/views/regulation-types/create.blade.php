@extends('layouts.app')

@section('title', 'Tambah Jenis Regulasi')
@section('header', 'Tambah Jenis Regulasi')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Master Data</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Tambah Jenis Regulasi</h3>
                        <p class="text-sm text-[#667085] mt-1">Buat jenis regulasi baru beserta level hierarkinya.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('regulation-types.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#071833] mb-2">Nama Jenis Regulasi <span class="text-[#c99a3e]">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-premium" placeholder="Contoh: Undang-Undang">
                        @error('name')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="level" class="block text-sm font-semibold text-[#071833] mb-2">Level Hierarki <span class="text-[#c99a3e]">*</span></label>
                        <select name="level" id="level" required class="select-premium">
                            <option value="">-- Pilih Level --</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('level') == $i ? 'selected' : '' }}>
                                    Level {{ $i }}{{ $i === 1 ? ' (Tertinggi)' : ($i === 5 ? ' (Terendah)' : '') }}
                                </option>
                            @endfor
                        </select>
                        @error('level')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Simpan</x-button>
                        <x-button href="{{ route('regulation-types.index') }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <aside>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Panduan Level Hierarki</h3>
                </x-slot>
                <div class="space-y-3">
                    <p class="text-sm text-[#667085] leading-relaxed">Level hierarki digunakan untuk menentukan tingkatan regulasi:</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2"><x-badge color="red">Level 1</x-badge><span class="text-xs text-[#667085]">UUD / UU</span></div>
                        <div class="flex items-center gap-2"><x-badge color="orange">Level 2</x-badge><span class="text-xs text-[#667085]">Peraturan Pemerintah</span></div>
                        <div class="flex items-center gap-2"><x-badge color="yellow">Level 3</x-badge><span class="text-xs text-[#667085]">Peraturan Menteri / OJK</span></div>
                        <div class="flex items-center gap-2"><x-badge color="blue">Level 4</x-badge><span class="text-xs text-[#667085]">Keputusan / Surat Edaran</span></div>
                        <div class="flex items-center gap-2"><x-badge color="green">Level 5</x-badge><span class="text-xs text-[#667085]">Pedoman / Lainnya</span></div>
                    </div>
                </div>
            </x-card>
        </aside>
    </div>
@endsection
