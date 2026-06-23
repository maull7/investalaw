@extends('layouts.app')

@section('title', 'Partisi Dokumen')
@section('header', 'Partisi Dokumen')

@php
    $showDaftarIsi = request()->query('tab') === 'daftar-isi';
@endphp

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Partisi Dokumen
                    </span>
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-white/10 text-white/80 uppercase tracking-wider">{{ $totalPages }} halaman</span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">Buat partisi, tentukan halaman Daftar Isi, lalu ekstrak otomatis Bab → Subbab → Isi.</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('partitions.parsed-text', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Hasil Parser PDF
                </a>
                <a href="{{ route('ai-preview.show', $document) }}?type={{ request('type', 'analisa') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-[#c99a3e]/30 bg-[#c99a3e]/15 hover:bg-[#c99a3e]/25 backdrop-blur transition">
                    AI Preview
                </a>
                <a href="{{ route('review-documents.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back to Documents
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6" x-data="{ activeTab: '{{ $showDaftarIsi ? 'daftar-isi' : 'partitions' }}' }">
        {{-- Tab Navigation --}}
        <div class="flex gap-1 bg-[#f6f8fb] rounded-2xl p-1.5 ring-1 ring-[#e7eaf0] mb-6">
            <button @click="activeTab = 'partitions'"
                    :class="activeTab === 'partitions' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition text-center">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                    Partisi & Analisa
                </span>
            </button>
            <button @click="activeTab = 'daftar-isi'"
                    :class="activeTab === 'daftar-isi' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition text-center">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    Daftar Isi
                    @if($document->partitions->isNotEmpty())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#c99a3e]/10 text-[#c99a3e]">{{ $document->partitions->count() }} partisi</span>
                    @endif
                </span>
            </button>
            <a href="{{ route('partitions.parsed-text', $document) }}"
               class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition text-[#667085] hover:text-[#071833] text-center">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Parse PDF
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#c99a3e]/10 text-[#c99a3e]">{{ $totalPages }} hlm</span>
                </span>
            </a>
        </div>

        {{-- Tab: Partitions Content --}}
        <div x-show="activeTab === 'partitions'" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Sidebar --}}
                <div class="space-y-6">
                    {{-- Partition Configuration --}}
                    <x-card>
                        <x-slot name="header">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-[#071833]">Partisi Dokumen</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $document->partitions->count() }}</span>
                            </div>
                        </x-slot>

                        <div x-data="partitionForm({{ json_encode($document->partitions->map(fn($p) => [
                            'name' => $p->name,
                            'start_page' => $p->start_page,
                            'end_page' => $p->end_page,
                            'description' => $p->description ?? '',
                            'has_toc' => $p->has_toc,
                        ])->values()) }}, {{ $totalPages }})">
                            <form method="POST" action="{{ route('partitions.store', $document) }}">
                                @csrf
                                <div class="space-y-3">
                                    <template x-for="(part, idx) in partitions" :key="idx">
                                        <div class="rounded-xl bg-[#f6f8fb] p-3 ring-1 ring-[#e7eaf0] border-l-2 border-l-[#c99a3e]">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2 flex-1">
                                                    <span class="text-[10px] font-bold text-[#c99a3e] uppercase tracking-wider">PARTISI</span>
                                                    <input type="text"
                                                           :name="`partitions[${idx}][name]`"
                                                           x-model="part.name"
                                                           placeholder="Nama Partisi (contoh: Daftar Isi)"
                                                           class="input-premium !py-1.5 !text-xs flex-1">
                                                </div>
                                                <button type="button" @click="removePartition(idx)" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition" x-show="partitions.length > 1">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 mb-2">
                                                <div>
                                                    <label class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Hal. Awal</label>
                                                    <input type="number" :name="`partitions[${idx}][start_page]`"
                                                           x-model.number="part.start_page" min="1" :max="totalPages || undefined"
                                                           class="input-premium !py-1.5 !text-xs mt-0.5">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Hal. Akhir</label>
                                                    <input type="number" :name="`partitions[${idx}][end_page]`"
                                                           x-model.number="part.end_page" min="1" :max="totalPages || undefined"
                                                           class="input-premium !py-1.5 !text-xs mt-0.5">
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 mb-2">
                                                <input type="hidden" :name="`partitions[${idx}][has_toc]`" :value="part.has_toc ? '1' : '0'">
                                                <button type="button" @click="part.has_toc = !part.has_toc"
                                                        :class="part.has_toc ? 'bg-[#c99a3e] border-[#c99a3e]' : 'bg-white border-[#dde0e6]'"
                                                        class="w-9 h-5 rounded-full border-2 flex items-center transition shrink-0 focus:outline-none focus:ring-2 focus:ring-[#c99a3e] focus:ring-offset-1"
                                                        :id="`has_toc_toggle_${idx}`">
                                                    <span :class="part.has_toc ? 'translate-x-[18px]' : 'translate-x-[2px]'"
                                                          class="w-3.5 h-3.5 rounded-full bg-white shadow-sm transition"></span>
                                                </button>
                                                <label :for="`has_toc_toggle_${idx}`" @click="part.has_toc = !part.has_toc"
                                                       class="text-[10px] font-bold text-[#c99a3e] uppercase tracking-wider cursor-pointer select-none">
                                                    Daftar Isi
                                                </label>
                                            </div>
                                            <input type="text" :name="`partitions[${idx}][description]`"
                                                   x-model="part.description" placeholder="Deskripsi (opsional)"
                                                   class="input-premium !py-1.5 !text-xs w-full">

                                            {{-- Ekstrak button (only when has_toc is checked) --}}
                                            <div x-show="part.has_toc" x-cloak class="mt-2">
                                                <template x-if="part.id">
                                                    <form method="POST" :action="`{{ route('partitions.extract-toc', ['reviewDocument' => $document->id, 'documentPartition' => '__ID__']) }}`.replace('__ID__', part.id)">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 transition">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                                            Ekstrak
                                                        </button>
                                                    </form>
                                                </template>
                                                <template x-if="!part.id">
                                                    <p class="text-[10px] text-emerald-600 italic">Simpan partisi dulu untuk mengaktifkan ekstraksi.</p>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <template x-if="errorMessage">
                                    <div class="mt-3 p-2.5 rounded-xl bg-rose-50 text-rose-700 text-xs font-medium" x-text="errorMessage"></div>
                                </template>

                                <div class="mt-4 flex gap-2">
                                    <button type="button" @click="addPartition()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#c99a3e] bg-[#c99a3e]/10 hover:bg-[#c99a3e]/20 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        Tambah Partisi
                                    </button>
                                    <x-button type="submit" variant="primary" size="sm">
                                        Simpan Partisi
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </x-card>

                    {{-- Saved Partitions with Analysis --}}
                    @if($document->partitions->isNotEmpty())
                        <x-card :padding="false">
                            <x-slot name="header">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-bold text-[#071833]">Data Partisi & Analisa</h3>
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">{{ $document->partitions->count() }} partisi</span>
                                </div>
                            </x-slot>

                            <div x-data="{ openPart: null, openBab: null, openSub: null, openAnalysis: null }">
                                @foreach($document->partitions as $pIdx => $part)
                                    <div class="border-b border-[#e7eaf0] last:border-0">
                                        {{-- Partisi Header --}}
                                        <div class="flex items-center justify-between px-4 py-3 hover:bg-[#f6f8fb]/40 transition">
                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                <button @click="openPart = openPart === {{ $pIdx }} ? null : {{ $pIdx }}" class="flex items-center gap-2 min-w-0 flex-1 text-left">
                                                    <svg class="w-3.5 h-3.5 text-[#c99a3e] shrink-0 transition-transform" :class="openPart === {{ $pIdx }} ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                                    <span class="w-2 h-2 rounded-full bg-[#c99a3e] shrink-0"></span>
                                                    <span class="font-bold text-[#071833] text-sm truncate">{{ $part->name }}</span>
                                                    <span class="text-[10px] text-[#667085] font-normal shrink-0">h.{{ $part->start_page }}–{{ $part->end_page }}</span>
                                                </button>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0 ml-2">
                                                @if($part->has_toc)
                                                    <div class="flex items-center gap-1">
                                                        <form method="POST" action="{{ route('partitions.extract-toc', [$document, $part]) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 transition">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                                                Ekstrak
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('partitions.debug-toc', [$document, $part]) }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-semibold text-amber-700 bg-amber-100 hover:bg-amber-200 transition">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                                            Debug
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($part->analysis)
                                                    @php $sc = $part->analysis->compliance_status?->value; @endphp
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $sc === 'compliant' ? 'emerald' : ($sc === 'partially_compliant' ? 'amber' : ($sc === 'non_compliant' ? 'rose' : 'gray')) }}-100 text-{{ $sc === 'compliant' ? 'emerald' : ($sc === 'partially_compliant' ? 'amber' : ($sc === 'non_compliant' ? 'rose' : 'gray')) }}-700">
                                                        {{ $part->analysis->compliance_status?->label() ?? 'Terisi' }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Belum</span>
                                                @endif
                                                <button @click="openAnalysis = openAnalysis === 'part-{{ $pIdx }}' ? null : 'part-{{ $pIdx }}'" class="p-1.5 rounded-lg text-[#c99a3e] hover:bg-[#c99a3e]/10 transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Partisi Detail --}}
                                        <div x-show="openPart === {{ $pIdx }}" x-collapse>
                                            <div class="px-4 pb-2 space-y-2">
                                                @if($part->description)
                                                    <p class="text-xs text-[#667085] ml-6">{{ $part->description }}</p>
                                                @endif
                                                <div class="flex items-center gap-4 text-[10px] text-[#667085] ml-6">
                                                    <span>Halaman: <strong class="text-[#071833]">{{ $part->start_page }} – {{ $part->end_page }}</strong></span>
                                                    @if($part->has_toc)
                                                        <span><strong class="text-[#c99a3e]">✓ Daftar Isi</strong></span>
                                                    @endif
                                                    <span>Jumlah: <strong class="text-[#071833]">{{ ($part->end_page - $part->start_page) + 1 }} hlm</strong></span>
                                                </div>

                                                {{-- Partisi-level analysis --}}
                                                <div class="ml-6 mt-2">
                                                    <button @click="openAnalysis = openAnalysis === 'part-{{ $pIdx }}' ? null : 'part-{{ $pIdx }}'"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#c99a3e] bg-[#c99a3e]/10 hover:bg-[#c99a3e]/20 transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                                        {{ $part->analysis ? 'Edit Analisa Partisi' : 'Buat Analisa Partisi' }}
                                                    </button>
                                                </div>

                                                {{-- Partisi Analysis Form --}}
                                                <div x-show="openAnalysis === 'part-{{ $pIdx }}'" x-collapse>
                                                    <div class="ml-6 pb-3">
                                                        <form method="POST" action="{{ route('partitions.save-analysis', [$document, $part]) }}">
                                                            @csrf
                                                            <div class="space-y-3 rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0]">
                                                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Analisa Partisi: {{ $part->name }}</p>
                                                                <div>
                                                                    <label class="text-[10px] font-bold text-[#667085] uppercase">Ringkasan</label>
                                                                    <textarea name="summary" rows="3" class="input-premium !text-xs mt-1 w-full">{{ $part->analysis?->summary ?? '' }}</textarea>
                                                                </div>
                                                                <div>
                                                                    <label class="text-[10px] font-bold text-[#667085] uppercase">Temuan</label>
                                                                    <textarea name="findings" rows="3" class="input-premium !text-xs mt-1 w-full">{{ $part->analysis?->findings ?? '' }}</textarea>
                                                                </div>
                                                                <div>
                                                                    <label class="text-[10px] font-bold text-[#667085] uppercase">Status Kepatuhan</label>
                                                                    <select name="compliance_status" class="select-premium !text-xs mt-1 w-full">
                                                                        <option value="">— Pilih Status —</option>
                                                                        <option value="compliant" {{ $part->analysis?->compliance_status?->value === 'compliant' ? 'selected' : '' }}>Compliant</option>
                                                                        <option value="partially_compliant" {{ $part->analysis?->compliance_status?->value === 'partially_compliant' ? 'selected' : '' }}>Partially Compliant</option>
                                                                        <option value="non_compliant" {{ $part->analysis?->compliance_status?->value === 'non_compliant' ? 'selected' : '' }}>Non-Compliant</option>
                                                                    </select>
                                                                </div>
                                                                <div class="flex justify-end">
                                                                    <x-button type="submit" variant="primary" size="sm">Simpan Analisa</x-button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Summary Footer --}}
                                <div class="bg-[#f6f8fb]/60 px-4 py-2.5 flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Total Halaman Terpartisi</span>
                                    <span class="text-xs font-bold text-[#071833]">
                                        {{ $document->partitions->sum(fn($p) => ($p->end_page - $p->start_page) + 1) }} / {{ $totalPages }} hlm
                                    </span>
                                </div>
                            </div>
                        </x-card>

                        {{-- Generate AI Analysis --}}
                        <x-card>
                            <x-slot name="header">
                                <h3 class="text-base font-bold text-[#071833]">Analisa AI Per-Partisi</h3>
                            </x-slot>
                            <p class="text-sm text-[#667085] mb-4">AI akan menganalisa setiap partisi secara terpisah terhadap regulasi acuan.</p>
                            <form method="POST" action="{{ route('partitions.analyse', $document) }}" x-data="{ aiType: 'analisa' }">
                                @csrf
                                <div class="flex gap-2">
                                    <select name="type" x-model="aiType" class="select-premium flex-1 !text-xs">
                                        <option value="analisa">Analisa</option>
                                        <option value="review">Review</option>
                                        <option value="rekomendasi">Rekomendasi</option>
                                        <option value="validitas">Validitas</option>
                                    </select>
                                    <x-button type="submit" variant="primary" size="sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                                        Generate AI
                                    </x-button>
                                </div>
                            </form>
                        </x-card>

                        {{-- Regulasi Acuan --}}
                        @if($document->regulations->isNotEmpty())
                            <x-card>
                                <x-slot name="header">
                                    <h3 class="text-base font-bold text-[#071833]">Regulasi Acuan</h3>
                                </x-slot>
                                <div class="space-y-2">
                                    @foreach($document->regulations as $reg)
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-[#071833] truncate">{{ $reg->regulation_number }}</p>
                                                <p class="text-[10px] text-[#667085] truncate">{{ $reg->title }} ({{ $reg->year }})</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </x-card>
                        @endif
                    @endif
                </div>

                {{-- Right: PDF Viewer --}}
                <div class="lg:col-span-2">
                    <x-card :padding="false">
                        <x-slot name="header">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-[#071833]">PDF Viewer</h3>
                            </div>
                        </x-slot>
                        <div x-data="pdfViewer('{{ route('review-documents.view-file', $document) }}')"
                             @go-to-page.window="goToPage($event.detail.page)"
                             class="p-4">
                            <div class="flex items-center justify-between mb-4 px-2">
                                <div class="flex items-center gap-2">
                                    <button @click="prevPage()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                                    </button>
                                    <span class="text-sm font-semibold text-[#071833]" x-text="`${currentPage} / ${totalPages}`"></span>
                                    <button @click="nextPage()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="zoomOut()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM13.5 7.5h-6"/></svg>
                                    </button>
                                    <span class="text-xs font-semibold text-[#667085]" x-text="`${Math.round(scale * 100)}%`"></span>
                                    <button @click="zoomIn()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-auto max-h-[80vh] bg-[#e7eaf0] rounded-xl p-2 flex justify-center">
                                <canvas x-ref="pdfCanvas" class="shadow-lg"></canvas>
                            </div>
                            <div class="mt-3 flex items-center gap-2 px-2">
                                <label class="text-xs font-semibold text-[#667085]">Ke halaman:</label>
                                <input type="number" min="1" :max="totalPages" @change="goToPage(parseInt($event.target.value))" class="input-premium !py-1.5 !text-xs w-20">
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>

        {{-- Tab: Daftar Isi --}}
        <div x-show="activeTab === 'daftar-isi'" x-cloak>
            @if(empty($babTree))
                <x-card>
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-[#e7eaf0] mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <p class="text-sm text-[#667085]">Belum ada Bab. Ekstrak Daftar Isi dari partisi yang dicentang.</p>
                    </div>
                </x-card>
            @else
                <x-card class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#f6f8fb] text-[10px] font-bold uppercase tracking-wider text-[#667085]">
                                    <th class="text-left px-4 py-2.5 w-10">No</th>
                                    <th class="text-left px-4 py-2.5">Bab / Subbab / Isi</th>
                                    <th class="text-center px-4 py-2.5 w-24">Halaman</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e7eaf0]">
                                @foreach($babTree as $bIdx => $bab)
                                    <tr class="bg-[#fafbfc]">
                                        <td class="px-4 py-2.5 text-xs font-bold text-[#c99a3e]">{{ $bIdx + 1 }}.</td>
                                        <td class="px-4 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-[#c99a3e] shrink-0"></span>
                                                <span class="font-bold text-[#071833] text-sm">{{ $bab['title'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-xs text-[#667085] font-semibold">{{ $bab['start_page'] }}–{{ $bab['end_page'] }}</td>
                                    </tr>
                                    @foreach($bab['children'] as $sIdx => $subbab)
                                        <tr class="hover:bg-[#f6f8fb]/40">
                                            <td class="px-4 py-1.5"></td>
                                            <td class="px-4 py-1.5">
                                                <div class="flex items-center gap-2 ml-6">
                                                    <span class="w-1.5 h-1.5 rounded-full border-2 border-[#c99a3e] shrink-0"></span>
                                                    <span class="text-xs font-semibold text-[#071833]">{{ $subbab['title'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-1.5"></td>
                                        </tr>
                                        @foreach($subbab['children'] ?? [] as $isi)
                                            <tr class="text-[#667085]">
                                                <td class="px-4 py-1"></td>
                                                <td class="px-4 py-1">
                                                    <div class="flex items-center gap-2 ml-12">
                                                        <span class="w-1 h-1 rounded-full bg-[#c99a3e]/60 shrink-0"></span>
                                                        <span class="text-[11px]">{{ $isi['title'] }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-1"></td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
function partitionForm(existingPartitions, totalPages) {
    const defaultPart = () => ({
        name: '',
        start_page: 1,
        end_page: 1,
        description: '',
        has_toc: false,
        children: [],
    });

    return {
        partitions: existingPartitions.length > 0 ? existingPartitions : [defaultPart()],
        totalPages: totalPages,
        errorMessage: '',

        addPartition() {
            const last = this.partitions[this.partitions.length - 1];
            this.partitions.push({
                ...defaultPart(),
                start_page: last ? last.end_page + 1 : 1,
                end_page: last ? last.end_page + 1 : 1,
            });
        },

        removePartition(index) {
            if (this.partitions.length > 1) {
                this.partitions.splice(index, 1);
            }
        },
    };
}
</script>
@endpush
