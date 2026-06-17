@extends('layouts.app')

@section('title', 'Edit Prompt')
@section('header', 'Edit Prompt')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">AI Settings</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Edit Prompt</h3>
                        <p class="text-sm text-[#667085] mt-1">Ubah prompt AI untuk {{ $aiPrompt->type }}.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('ai-prompts.update', $aiPrompt) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="type" class="block text-sm font-semibold text-[#071833] mb-2">Type <span class="text-[#c99a3e]">*</span></label>
                        <select name="type" id="type" required class="select-premium">
                            <option value="">-- Pilih Type --</option>
                            <option value="analisa" {{ old('type', $aiPrompt->type) === 'analisa' ? 'selected' : '' }}>Analisa</option>
                            <option value="review" {{ old('type', $aiPrompt->type) === 'review' ? 'selected' : '' }}>Review</option>
                            <option value="rekomendasi" {{ old('type', $aiPrompt->type) === 'rekomendasi' ? 'selected' : '' }}>Rekomendasi</option>
                            <option value="validitas" {{ old('type', $aiPrompt->type) === 'validitas' ? 'selected' : '' }}>Validitas</option>
                        </select>
                        @error('type')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-semibold text-[#071833] mb-2">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $aiPrompt->title) }}" class="input-premium" placeholder="Contoh: Prompt Analisa Dokumen">
                        @error('title')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="prompt_text" class="block text-sm font-semibold text-[#071833] mb-2">Prompt Text <span class="text-[#c99a3e]">*</span></label>
                        <textarea name="prompt_text" id="prompt_text" rows="10" required class="input-premium" placeholder="Masukkan prompt untuk AI...">{{ old('prompt_text', $aiPrompt->prompt_text) }}</textarea>
                        @error('prompt_text')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $aiPrompt->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-[#d0d5dd] text-[#c99a3e] focus:ring-[#c99a3e]">
                        <label for="is_active" class="text-sm font-semibold text-[#071833]">Active</label>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Update</x-button>
                        <x-button href="{{ route('ai-prompts.index') }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
