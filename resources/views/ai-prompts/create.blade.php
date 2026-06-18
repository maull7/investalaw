@extends('layouts.app')

@section('title', 'Tambah Prompt')
@section('header', 'Tambah Prompt')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">AI Settings</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Tambah Prompt Baru</h3>
                        <p class="text-sm text-[#667085] mt-1">Buat prompt AI untuk digunakan saat generate summary.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('ai-prompts.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="type" class="block text-sm font-semibold text-[#071833] mb-2">Type <span
                                class="text-[#c99a3e]">*</span></label>
                        <select name="type" id="type" required class="select-premium">
                            <option value="">-- Pilih Type --</option>
                            <option value="analisa" {{ old('type') === 'analisa' ? 'selected' : '' }}>Analisa</option>
                            <option value="review" {{ old('type') === 'review' ? 'selected' : '' }}>Review</option>
                            <option value="rekomendasi" {{ old('type') === 'rekomendasi' ? 'selected' : '' }}>Rekomendasi
                            </option>
                            <option value="validitas" {{ old('type') === 'validitas' ? 'selected' : '' }}>Validitas</option>
                        </select>
                        @error('type')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-semibold text-[#071833] mb-2">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}"
                            class="input-premium" placeholder="Contoh: Prompt Analisa Dokumen">
                        @error('title')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="prompt_text" class="block text-sm font-semibold text-[#071833] mb-2">Prompt Text <span
                                class="text-[#c99a3e]">*</span></label>
                        <textarea name="prompt_text" id="prompt_text" rows="7" required class="input-premium input-textarea w-full"
                            placeholder="Masukkan prompt untuk AI...">{{ old('prompt_text') }}</textarea>
                        @error('prompt_text')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-[#d0d5dd] text-[#c99a3e] focus:ring-[#c99a3e]">
                        <label for="is_active" class="text-sm font-semibold text-[#071833]">Active</label>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Simpan</x-button>
                        <x-button href="{{ route('ai-prompts.index') }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
