@extends('layouts.app')

@section('title', 'Edit Kasus')
@section('header', 'Edit Kasus')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Analisa Kasus</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Edit Kasus</h3>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('legal-cases.update', $legalCase) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="title" class="block text-sm font-semibold text-[#071833] mb-2">Judul Kasus <span class="text-[#c99a3e]">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $legalCase->title) }}" required maxlength="255" class="input-premium">
                            @error('title')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="case_number" class="block text-sm font-semibold text-[#071833] mb-2">Nomor Perkara</label>
                            <input type="text" name="case_number" id="case_number" value="{{ old('case_number', $legalCase->case_number) }}" maxlength="255" class="input-premium">
                            @error('case_number')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="court" class="block text-sm font-semibold text-[#071833] mb-2">Pengadilan</label>
                            <input type="text" name="court" id="court" value="{{ old('court', $legalCase->court) }}" maxlength="255" class="input-premium">
                            @error('court')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="status_case" class="block text-sm font-semibold text-[#071833] mb-2">Status Perkara <span class="text-[#c99a3e]">*</span></label>
                            <select name="status_case" id="status_case" class="select-premium">
                                <option value="ongoing" {{ old('status_case', $legalCase->status_case) === 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                                <option value="finished" {{ old('status_case', $legalCase->status_case) === 'finished' ? 'selected' : '' }}>Selesai</option>
                            </select>
                            @error('status_case')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Simpan</x-button>
                        <x-button href="{{ route('legal-cases.show', $legalCase) }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection