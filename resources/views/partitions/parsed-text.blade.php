@extends('layouts.app')

@section('title', 'Hasil Parser PDF')
@section('header', 'Hasil Parser PDF')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Parser PDF
                    </span>
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-white/10 text-white/80 uppercase tracking-wider">{{ count($docPages) }} halaman dokumen</span>
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-white/10 text-white/80 uppercase tracking-wider">{{ count($regulations) }} regulasi</span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">Teks mentah hasil ekstraksi parser dari dokumen review dan regulasi. Ini adalah data yang dikirim ke AI untuk perbandingan dan analisa.</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                @if($isParsed)
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/20">
                        Tercache {{ $document->parsed_at->diffForHumans() }}
                    </span>
                @else
                    <form action="{{ route('partitions.parse-pdf', $document) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-[#c99a3e] hover:bg-[#b8892f] transition">
                            Parse PDF Sekarang
                        </button>
                    </form>
                @endif
                <a href="{{ route('partitions.index', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Kembali ke Partisi
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6" x-data="{ activeTab: 'document' }">
        {{-- Tab Navigation --}}
        <div class="flex gap-1 bg-[#f6f8fb] rounded-2xl p-1.5 ring-1 ring-[#e7eaf0]">
            <button @click="activeTab = 'document'"
                    :class="activeTab === 'document' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Dokumen Review
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#c99a3e]/10 text-[#c99a3e]">{{ count($docPages) }} hlm</span>
                </span>
            </button>
            <button @click="activeTab = 'regulations'"
                    :class="activeTab === 'regulations' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
                    Regulasi Acuan
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">{{ count($regulations) }}</span>
                </span>
            </button>
        </div>

        {{-- Tab: Dokumen Review --}}
        <div x-show="activeTab === 'document'" class="mt-4 space-y-4">
            {{-- Summary --}}
            <x-card :padding="false">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[#071833]">Ringkasan Parser — Dokumen Review</h3>
                        <span class="text-xs text-[#667085]">{{ $document->title }}</span>
                    </div>
                </x-slot>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-[#071833]">{{ count($docPages) }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Total Halaman</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-[#071833]">{{ number_format($docTotalChars) }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Total Karakter</p>
                    </div>
                    <div class="text-center">
                        @php $emptyPages = count(array_filter($docPages, fn($p) => $p['char_count'] === 0)); @endphp
                        <p class="text-2xl font-bold text-[#071833]">{{ $emptyPages }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Halaman Kosong</p>
                    </div>
                    <div class="text-center">
                        @php $avgChars = count($docPages) > 0 ? (int)($docTotalChars / count($docPages)) : 0; @endphp
                        <p class="text-2xl font-bold text-[#071833]">{{ number_format($avgChars) }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Rata-rata / Halaman</p>
                    </div>
                </div>
                @if($babs->isNotEmpty())
                    <div class="border-t border-[#e7eaf0] px-6 py-3 flex flex-wrap gap-2">
                        @foreach($babs as $bab)
                            <a href="#bab-{{ $bab->id }}" class="px-2.5 py-1 rounded-full text-[9px] font-bold bg-[#c99a3e]/10 text-[#c99a3e] hover:bg-[#c99a3e]/20 transition">
                                {{ $bab->name }} (hlm.{{ $bab->start_page }}–{{ $bab->end_page }})
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Grouped by BAB --}}
            @if($babs->isNotEmpty())
                <div x-data="{ expandedBab: null, expandedPage: null }" class="space-y-3">
                    {{-- Unassigned pages (before first BAB) --}}
                    @if(count($unassignedPages) > 0)
                        <div class="rounded-2xl border border-[#e7eaf0] overflow-hidden bg-white">
                            <button @click="expandedBab = expandedBab === 'preamble' ? null : 'preamble'" class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-[#f6f8fb] transition">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-[#071833]">Pendahuluan</p>
                                        <p class="text-[10px] text-[#667085]">{{ count($unassignedPages) }} halaman</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-[10px] font-bold text-gray-600">{{ number_format(array_sum(array_column($unassignedPages, 'char_count'))) }} char</span>
                                    <svg class="w-4 h-4 text-[#667085] transition-transform" :class="expandedBab === 'preamble' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </div>
                            </button>
                            <div x-show="expandedBab === 'preamble'" x-collapse>
                                <div class="px-5 pb-4 space-y-1.5">
                                    @foreach($unassignedPages as $pageData)
                                        <div class="rounded-xl border border-[#e7eaf0] overflow-hidden">
                                            <button @click="expandedPage = expandedPage === 'pre-{{ $pageData['page'] }}' ? null : 'pre-{{ $pageData['page'] }}'" class="w-full flex items-center justify-between px-4 py-2 text-left hover:bg-[#f6f8fb] transition">
                                                <span class="text-xs font-semibold text-[#071833]">Halaman {{ $pageData['page'] }}</span>
                                                <span class="text-[10px] text-[#667085]">{{ number_format($pageData['char_count']) }} char</span>
                                            </button>
                                            <div x-show="expandedPage === 'pre-{{ $pageData['page'] }}'" x-collapse>
                                                <div class="px-4 pb-3">
                                                    @if($pageData['char_count'] > 0)
                                                        <div class="rounded-lg bg-[#f6f8fb] p-3 ring-1 ring-[#e7eaf0] max-h-60 overflow-y-auto">
                                                            <pre class="text-[10px] text-[#071833] leading-relaxed whitespace-pre-wrap font-mono break-words">{{ $pageData['text'] }}</pre>
                                                        </div>
                                                    @else
                                                        <p class="text-xs text-[#667085] italic">Kosong</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- BAB sections --}}
                    @foreach($babs as $bab)
                        @php $group = $babGroups[$bab->id] ?? null; @endphp
                        @if($group)
                            <div class="rounded-2xl border border-[#e7eaf0] overflow-hidden bg-white" id="bab-{{ $bab->id }}">
                                <button @click="expandedBab = expandedBab === {{ $bab->id }} ? null : {{ $bab->id }}" class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-[#f6f8fb] transition">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#c99a3e]/15 to-[#e6c06a]/15 text-[#c99a3e] text-xs font-bold flex items-center justify-center shrink-0">{{ $loop->iteration }}</span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-[#071833] truncate">{{ $bab->name }}</p>
                                            <p class="text-[10px] text-[#667085]">Halaman {{ $bab->start_page }}–{{ $bab->end_page }} · {{ count($group['pages']) }} halaman</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 ml-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                            {{ number_format(array_sum(array_column($group['pages'], 'char_count'))) }} char
                                        </span>
                                        @if($bab->children->isNotEmpty())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700">{{ $bab->children->count() }} sub</span>
                                        @endif
                                        <svg class="w-4 h-4 text-[#667085] transition-transform shrink-0" :class="expandedBab === {{ $bab->id }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                    </div>
                                </button>
                                <div x-show="expandedBab === {{ $bab->id }}" x-collapse>
                                    <div class="px-5 pb-4 space-y-1.5">
                                        <div class="flex items-center justify-end gap-2 mb-2">
                                            <form action="{{ route('bab-structures.detect', [$document, $bab]) }}" method="POST" onsubmit="return confirm('Deteksi struktur AI untuk {{ $bab->name }}?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg text-[10px] font-bold bg-gradient-to-r from-[#c99a3e]/15 to-[#e6c06a]/15 text-[#c99a3e] hover:from-[#c99a3e]/25 hover:to-[#e6c06a]/25 ring-1 ring-[#c99a3e]/20 transition">
                                                    AI Detect Struktur
                                                </button>
                                            </form>
                                        </div>
                                        @foreach($group['pages'] as $pageData)
                                            <div class="rounded-xl border border-[#e7eaf0] overflow-hidden">
                                                <button @click="expandedPage = expandedPage === '{{ $bab->id }}-{{ $pageData['page'] }}' ? null : '{{ $bab->id }}-{{ $pageData['page'] }}'" class="w-full flex items-center justify-between px-4 py-2 text-left hover:bg-[#f6f8fb] transition">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-6 h-6 rounded-md bg-gradient-to-br from-[#c99a3e]/10 to-[#e6c06a]/10 text-[#c99a3e] text-[9px] font-bold flex items-center justify-center">{{ $pageData['page'] }}</span>
                                                        <span class="text-xs font-semibold text-[#071833]">Halaman {{ $pageData['page'] }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        @if($pageData['char_count'] === 0)
                                                            <span class="px-1.5 py-0.5 rounded-full bg-rose-100 text-[8px] font-bold text-rose-700">Kosong</span>
                                                        @elseif($pageData['char_count'] < 50)
                                                            <span class="px-1.5 py-0.5 rounded-full bg-amber-100 text-[8px] font-bold text-amber-700">Sedikit</span>
                                                        @else
                                                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-[8px] font-bold text-emerald-700">OK</span>
                                                        @endif
                                                        <span class="text-[10px] text-[#667085]">{{ number_format($pageData['char_count']) }} char</span>
                                                        <svg class="w-3.5 h-3.5 text-[#667085] transition-transform" :class="expandedPage === '{{ $bab->id }}-{{ $pageData['page'] }}' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                                    </div>
                                                </button>
                                                <div x-show="expandedPage === '{{ $bab->id }}-{{ $pageData['page'] }}'" x-collapse>
                                                    <div class="px-4 pb-3">
                                                        @if($pageData['char_count'] > 0)
                                                            <div class="rounded-lg bg-[#f6f8fb] p-3 ring-1 ring-[#e7eaf0] max-h-60 overflow-y-auto">
                                                                <pre class="text-[10px] text-[#071833] leading-relaxed whitespace-pre-wrap font-mono break-words">{{ $pageData['text'] }}</pre>
                                                            </div>
                                                        @else
                                                            <p class="text-xs text-[#667085] italic">Halaman ini tidak mengandung teks yang bisa diekstrak (mungkin gambar atau scan).</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- AI Detected Structure --}}
                                        @if($bab->children->isNotEmpty())
                                            <div x-data="{ expandedAi: null }" class="mt-3 pt-3 border-t border-[#e7eaf0]">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-sky-700 mb-2">Struktur AI — {{ $bab->children->count() }} Subbab</p>
                                                <div class="space-y-1">
                                                    @foreach($bab->children as $subbab)
                                                        <div class="rounded-lg border border-[#e7eaf0] overflow-hidden">
                                                            <button @click="expandedAi = expandedAi === {{ $subbab->id }} ? null : {{ $subbab->id }}" class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-[#f6f8fb] transition">
                                                                <div class="flex items-center gap-2 min-w-0">
                                                                    <span class="w-5 h-5 rounded bg-sky-100 text-sky-700 text-[8px] font-bold flex items-center justify-center shrink-0">{{ $loop->iteration }}</span>
                                                                    <span class="text-[11px] font-semibold text-[#071833] truncate">{{ $subbab->name }}</span>
                                                                </div>
                                                                <div class="flex items-center gap-2 shrink-0 ml-2">
                                                                    <span class="text-[9px] text-[#667085]">hlm.{{ $subbab->start_page }}–{{ $subbab->end_page }}</span>
                                                                    @if($subbab->children->isNotEmpty())
                                                                        <span class="px-1.5 py-0.5 rounded-full bg-sky-50 text-[8px] font-bold text-sky-600">{{ $subbab->children->count() }}</span>
                                                                    @endif
                                                                    <svg class="w-3 h-3 text-[#667085] transition-transform shrink-0" :class="expandedAi === {{ $subbab->id }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                                                </div>
                                                            </button>
                                                            @if($subbab->children->isNotEmpty())
                                                                <div x-show="expandedAi === {{ $subbab->id }}" x-collapse>
                                                                    <div class="px-3 pb-2 space-y-0.5">
                                                                        @foreach($subbab->children as $isi)
                                                                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#f6f8fb]">
                                                                                <span class="w-4 h-4 rounded bg-emerald-100 text-emerald-700 text-[7px] font-bold flex items-center justify-center shrink-0">I</span>
                                                                                <span class="text-[10px] text-[#071833] truncate">{{ $isi->name }}</span>
                                                                                <span class="text-[8px] text-[#667085] shrink-0 ml-auto">hlm.{{ $isi->start_page }}–{{ $isi->end_page }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                {{-- Fallback: flat per-page list when no BAB --}}
                <div x-data="{ expandedPage: null }" class="space-y-2">
                    @foreach($docPages as $pageData)
                        <div class="rounded-2xl border border-[#e7eaf0] overflow-hidden bg-white">
                            <button @click="expandedPage = expandedPage === {{ $pageData['page'] }} ? null : {{ $pageData['page'] }}" class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-[#f6f8fb] transition">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#c99a3e]/15 to-[#e6c06a]/15 text-[#c99a3e] text-xs font-bold flex items-center justify-center">{{ $pageData['page'] }}</span>
                                    <div>
                                        <p class="text-sm font-semibold text-[#071833]">Halaman {{ $pageData['page'] }}</p>
                                        <p class="text-[10px] text-[#667085]">{{ number_format($pageData['char_count']) }} karakter</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($pageData['char_count'] === 0)
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-[10px] font-bold text-rose-700">Kosong</span>
                                    @elseif($pageData['char_count'] < 50)
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">Sedikit</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">OK</span>
                                    @endif
                                    <svg class="w-4 h-4 text-[#667085] transition-transform" :class="expandedPage === {{ $pageData['page'] }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </div>
                            </button>
                            <div x-show="expandedPage === {{ $pageData['page'] }}" x-collapse>
                                <div class="px-5 pb-5">
                                    @if($pageData['char_count'] > 0)
                                        <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0] max-h-96 overflow-y-auto">
                                            <pre class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap font-mono break-words">{{ $pageData['text'] }}</pre>
                                        </div>
                                    @else
                                        <p class="text-sm text-[#667085] italic">Halaman ini tidak mengandung teks yang bisa diekstrak (mungkin gambar atau scan).</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab: Regulasi Acuan --}}
        <div x-show="activeTab === 'regulations'" class="mt-4 space-y-4">
            @if(empty($regulations))
                <x-card>
                    <div class="text-center py-14">
                        <div class="mx-auto w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
                        </div>
                        <p class="mt-4 text-base font-bold text-[#071833]">Belum ada regulasi acuan</p>
                        <p class="mt-1 text-sm text-[#667085]">Tambahkan regulasi pada dokumen ini untuk melihat hasil parser regulasi.</p>
                    </div>
                </x-card>
            @else
                <div x-data="{ expandedReg: null, expandedDoc: null }" class="space-y-3">
                    @foreach($regulations as $index => $reg)
                        <div class="rounded-2xl border border-[#e7eaf0] overflow-hidden bg-white">
                            {{-- Regulation Header --}}
                            <button @click="expandedReg = expandedReg === {{ $index }} ? null : {{ $index }}" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-[#f6f8fb] transition">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <span class="shrink-0 w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center ring-1 ring-emerald-200">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[#071833] truncate">{{ $reg['regulation_number'] }}</p>
                                        <p class="text-[11px] text-[#667085] truncate">{{ $reg['title'] }} ({{ $reg['year'] }})</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0 ml-3">
                                    @if($reg['main_chars'] > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">{{ number_format($reg['main_chars']) }} char</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-[10px] font-bold text-rose-700">No file</span>
                                    @endif
                                    @if(count($reg['documents']) > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-sky-100 text-[10px] font-bold text-sky-700">{{ count($reg['documents']) }} dok</span>
                                    @endif
                                    <svg class="w-4 h-4 text-[#667085] transition-transform" :class="expandedReg === {{ $index }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </div>
                            </button>

                            <div x-show="expandedReg === {{ $index }}" x-collapse>
                                <div class="px-5 pb-5 space-y-4">
                                    {{-- Main regulation file --}}
                                    @if($reg['main_chars'] > 0)
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085] mb-2">File Regulasi Utama — {{ number_format($reg['main_chars']) }} karakter</p>
                                            <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0] max-h-96 overflow-y-auto">
                                                <pre class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap font-mono break-words">{{ $reg['main_text'] }}</pre>
                                            </div>
                                        </div>
                                    @elseif($reg['main_chars'] === 0 && empty($reg['documents']))
                                        <div class="rounded-xl bg-rose-50 p-4 ring-1 ring-rose-200">
                                            <p class="text-sm text-rose-700">Tidak ada file regulasi yang terupload. Parser tidak bisa mengekstrak teks.</p>
                                        </div>
                                    @endif

                                    {{-- Attached documents --}}
                                    @foreach($reg['documents'] as $docIndex => $doc)
                                        <div class="border-t border-[#e7eaf0] pt-4">
                                            <button @click="expandedDoc = expandedDoc === '{{ $index }}_{{ $docIndex }}' ? null : '{{ $index }}_{{ $docIndex }}'" class="w-full flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                                    <p class="text-xs font-bold text-[#071833]">{{ $doc['name'] }}</p>
                                                    <span class="px-2 py-0.5 rounded-full bg-[#f6f8fb] text-[10px] font-bold text-[#667085]">{{ number_format($doc['chars']) }} char</span>
                                                </div>
                                                <svg class="w-4 h-4 text-[#667085] transition-transform" :class="expandedDoc === '{{ $index }}_{{ $docIndex }}' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                            </button>
                                            <div x-show="expandedDoc === '{{ $index }}_{{ $docIndex }}'" x-collapse>
                                                @if($doc['chars'] > 0)
                                                    <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0] max-h-96 overflow-y-auto">
                                                        <pre class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap font-mono break-words">{{ $doc['text'] }}</pre>
                                                    </div>
                                                @else
                                                    <p class="text-xs text-[#667085] italic">File tidak bisa diekstrak atau kosong.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
