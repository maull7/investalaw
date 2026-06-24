@extends('layouts.app')

@section('title', 'Daftar Regulasi')
@section('header', 'Daftar Regulasi')

@section('content')
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Manajemen Regulasi</p>
            <h2 class="mt-2 text-3xl font-bold text-[#071833] tracking-tight">Daftar Regulasi</h2>
            <p class="mt-1.5 text-sm text-[#667085]">Kelola seluruh regulasi dengan metadata lengkap untuk analisis kepatuhan.</p>
        </div>
        <x-button href="{{ route('regulations.create') }}" variant="primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Regulasi
        </x-button>
    </div>

    {{-- Filters --}}
    <x-card class="mt-6">
        <form method="GET" action="{{ route('regulations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="input-premium" placeholder="Cari nomor atau judul regulasi...">
            </div>
            <select name="year" class="select-premium">
                <option value="">Semua Tahun</option>
                @foreach($filterOptions['years'] as $year)
                    <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <select name="type_id" class="select-premium">
                <option value="">Semua Jenis</option>
                @foreach($filterOptions['types'] as $type)
                    <option value="{{ $type->id }}" {{ ($filters['type_id'] ?? '') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <x-button type="submit" variant="primary" size="md" class="flex-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    Cari
                </x-button>
                <x-button href="{{ route('regulations.index') }}" variant="outline" size="md">Reset</x-button>
            </div>
        </form>
    </x-card>

    {{-- Table --}}
    <x-card :padding="false" class="mt-6" x-data="{ docModal: null, docs: [], parseModal: null, parseData: null }">
        @if($regulations->isEmpty())
            <div class="text-center py-14">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </div>
                <p class="mt-4 text-base font-bold text-[#071833]">Belum ada regulasi</p>
                <p class="mt-1 text-sm text-[#667085]">Tambahkan regulasi pertama Anda untuk memulai pengelolaan.</p>
                <x-button href="{{ route('regulations.create') }}" variant="primary" size="sm" class="mt-5">Tambah Regulasi</x-button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>No. Regulasi</th>
                            <th>Judul</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th class="text-center">Dok Tambahan</th>
                            <th class="text-center">Status Parser</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regulations as $reg)
                            <tr>
                                <td>
                                    <a href="{{ route('regulations.show', $reg) }}" class="font-semibold text-[#071833] hover:text-[#c99a3e] transition">{{ $reg->regulation_number }}</a>
                                </td>
                                <td>
                                    <span class="text-sm text-[#071833]">{{ Str::limit($reg->title, 60) }}</span>
                                </td>
                                <td>
                                    @if($reg->type)
                                        <x-badge :color="$reg->type->levelBadgeColor()">{{ $reg->type->name }}</x-badge>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm text-[#667085]">{{ $reg->category?->name }}</span>
                                </td>
                                <td>
                                    <span class="font-semibold text-[#071833]">{{ $reg->year }}</span>
                                </td>
                                <td class="text-center">
                                    @php $docCount = $reg->documents->count(); @endphp
                                    @if($docCount > 0)
                                        <button type="button" @click="docModal = {{ $reg->id }}; docs = {{ Js::from($reg->documents->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'type' => $d->document_type])) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-700 hover:bg-sky-200 transition cursor-pointer">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                            {{ $docCount }}
                                        </button>
                                    @else
                                        <span class="text-xs text-[#b0b8c5]">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $status = $reg->parseStatusLabel();
                                        $color = $reg->parseStatusBadgeColor();
                                    @endphp
                                    <x-badge :color="$color">{{ $status }}</x-badge>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <x-button href="{{ route('regulations.show', $reg) }}" variant="outline" size="sm">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                            Detail
                                        </x-button>
                                        <x-button href="{{ route('regulations.edit', $reg) }}" variant="outline" size="sm">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                                            Edit
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-[#e7eaf0]">
                {{ $regulations->links() }}
            </div>
        @endif

        {{-- Dok Tambahan Modal --}}
        <div x-show="docModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-[#071b3a]/60 backdrop-blur-sm overflow-hidden"
             @click.self="docModal = null">
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-lg w-full mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-[#071833]">Dokumen Tambahan</h3>
                    <button type="button" @click="docModal = null" class="p-1.5 rounded-lg text-[#667085] hover:bg-[#f6f8fb] transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <template x-if="docs.length === 0">
                    <p class="text-sm text-[#667085] py-4 text-center">Belum ada dokumen tambahan.</p>
                </template>
                <template x-if="docs.length > 0">
                    <ul class="divide-y divide-[#e7eaf0]">
                        <template x-for="doc in docs" :key="doc.id">
                            <li class="flex items-center gap-3 py-3">
                                <div class="shrink-0 w-9 h-9 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 3.5L18.5 8H14V3.5zM6 20V4h7v5h5v11H6z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-[#071833]" x-text="doc.name"></p>
                                    <p class="text-xs text-[#667085]" x-text="doc.type"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </template>
                <div class="mt-4 pt-3 border-t border-[#e7eaf0] flex justify-end">
                    <x-button type="button" variant="outline" size="sm" @click="docModal = null">Tutup</x-button>
                </div>
            </div>
        </div>
    </x-card>
@endsection
