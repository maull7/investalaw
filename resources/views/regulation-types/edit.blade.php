@extends('layouts.app')

@section('title', 'Edit Jenis Regulasi')
@section('header', 'Edit Jenis Regulasi')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Editing</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">{{ $regulationType->name }}</h3>
                        <p class="text-sm text-[#667085] mt-1">Perbarui informasi jenis regulasi dan level hierarkinya.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('regulation-types.update', $regulationType) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#071833] mb-2">Nama Jenis Regulasi <span class="text-[#c99a3e]">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $regulationType->name) }}" required class="input-premium">
                        @error('name')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="level" class="block text-sm font-semibold text-[#071833] mb-2">Level Hierarki <span class="text-[#c99a3e]">*</span></label>
                        <select name="level" id="level" required class="select-premium">
                            <option value="">-- Pilih Level --</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('level', $regulationType->level) == $i ? 'selected' : '' }}>
                                    Level {{ $i }}{{ $i === 1 ? ' (Tertinggi)' : ($i === 5 ? ' (Terendah)' : '') }}
                                </option>
                            @endfor
                        </select>
                        @error('level')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Perbarui</x-button>
                        <x-button href="{{ route('regulation-types.index') }}" variant="outline" size="lg">Batal</x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <aside>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Informasi</h3>
                </x-slot>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Dibuat</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#071833]">{{ $regulationType->created_at->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Terakhir Diperbarui</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#071833]">{{ $regulationType->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </x-card>
        </aside>
    </div>
@endsection
