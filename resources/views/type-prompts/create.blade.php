@extends('layouts.app')

@section('title', 'Tambah Type Prompt')
@section('header', 'Tambah Type Prompt')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Master Data</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Tambah Type Prompt</h3>
                        <p class="text-sm text-[#667085] mt-1">Tipe yang nanti dipilih oleh AI prompt.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('type-prompts.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#071833] mb-2">Name <span class="text-[#c99a3e]">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-premium" placeholder="Contoh: Analisa">
                        @error('name')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-[#071833] mb-2">Description</label>
                        <textarea name="description" id="description" rows="3" class="input-premium input-textarea w-full" placeholder="Deskripsi tipe...">{{ old('description') }}</textarea>
                        @error('description')
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
                        <x-button href="{{ route('type-prompts.index') }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection