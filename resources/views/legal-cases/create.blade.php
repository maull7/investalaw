@extends('layouts.app')

@section('title', 'Tambah Kasus')
@section('header', 'Tambah Kasus')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Analisa Kasus</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Tambah Kasus Baru</h3>
                        <p class="text-sm text-[#667085] mt-1">Unggah materi gugatan/perkara PDF dan tentukan regulasi acuan.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('legal-cases.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="title" class="block text-sm font-semibold text-[#071833] mb-2">Judul Kasus <span class="text-[#c99a3e]">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255" class="input-premium" placeholder="Contoh: Gugatan Wanprestasi XYZ">
                            @error('title')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="case_number" class="block text-sm font-semibold text-[#071833] mb-2">Nomor Perkara</label>
                            <input type="text" name="case_number" id="case_number" value="{{ old('case_number') }}" maxlength="255" class="input-premium" placeholder="Contoh: 12/Pdt.G/2026/PN.JKT">
                            @error('case_number')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="court" class="block text-sm font-semibold text-[#071833] mb-2">Pengadilan</label>
                            <input type="text" name="court" id="court" value="{{ old('court') }}" maxlength="255" class="input-premium" placeholder="Contoh: PN Jakarta Selatan">
                            @error('court')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="status_case" class="block text-sm font-semibold text-[#071833] mb-2">Status Perkara <span class="text-[#c99a3e]">*</span></label>
                            <select name="status_case" id="status_case" class="select-premium">
                                <option value="ongoing" {{ old('status_case') === 'finished' ? '' : 'selected' }}>Berlangsung</option>
                                <option value="finished" {{ old('status_case') === 'finished' ? 'selected' : '' }}>Selesai</option>
                            </select>
                            @error('status_case')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="file" class="block text-sm font-semibold text-[#071833] mb-2">Materi Gugatan/Perkara (PDF) <span class="text-[#c99a3e]">*</span></label>
                            <input type="file" name="file" id="file" accept="application/pdf" required class="file-premium">
                            <p class="mt-1.5 text-xs text-[#667085]">Accepted format: PDF — maximum 10 MB.</p>
                            @error('file')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Simpan</x-button>
                        <x-button href="{{ route('legal-cases.index') }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection