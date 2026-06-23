@extends('layouts.app')

@section('title', 'Debug TOC')
@section('header', 'Debug TOC: '.$partition->name)

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9 mb-6">
        <div class="relative">
            <h2 class="text-xl font-bold">{{ $document->title }}</h2>
            <p class="mt-1 text-white/70 text-sm">Partisi: {{ $partition->name }} (hlm. {{ $partition->start_page }}–{{ $partition->end_page }})</p>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Raw text by newline --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#071833]">Split by Newline ({{ count($rawByNewline) }})</h3>
                </div>
            </x-slot>
            <div class="max-h-[50vh] overflow-auto font-mono text-[10px] leading-relaxed">
                @foreach($rawByNewline as $i => $line)
                    <div class="{{ $i % 2 === 0 ? 'bg-[#f6f8fb]/40' : '' }} px-2 py-0.5">
                        <span class="text-[#c99a3e] font-bold mr-1">{{ $i + 1 }}:</span>
                        <span>{{ $line ?: '⏎' }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Split entries --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#071833]">Split by BAB/LAMPIRAN ({{ count($entries) }})</h3>
                </div>
            </x-slot>
            <div class="max-h-[50vh] overflow-auto font-mono text-[10px] leading-relaxed">
                @foreach($entries as $i => $line)
                    @php $isEntry = (bool) preg_match('/^(?:BAB|LAMPIRAN)\s+[IVXLCDM]+\s*:/i', $line); @endphp
                    <div class="px-2 py-0.5 {{ $isEntry ? 'bg-emerald-50' : 'bg-amber-50' }}">
                        <span class="text-[#667085] mr-1">{{ $i + 1 }}:</span>
                        <span>{{ $line }}</span>
                        @if($isEntry)
                            <span class="ml-1 px-1 rounded bg-emerald-200 text-[8px] font-bold text-emerald-800">ENTRY</span>
                        @else
                            <span class="ml-1 px-1 rounded bg-amber-200 text-[8px] font-bold text-amber-800">HDR</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Normalized + matches --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#071833]">Normalized ({{ count($normalized) }})</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $entryMatches ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ count($entryMatches) }} entries
                    </span>
                </div>
            </x-slot>
            <div class="max-h-[50vh] overflow-auto font-mono text-[10px] leading-relaxed">
                @foreach($normalized as $i => $line)
                    @php
                        $isEntry = (bool) preg_match('/^(?:BAB|LAMPIRAN)\s+[IVXLCDM]+\s*:/i', $line);
                        $hasPage = (bool) preg_match('/\|\|\d+$/', $line);
                    @endphp
                    <div class="px-2 py-0.5 {{ $isEntry ? 'bg-emerald-50 border-l-2 border-emerald-500' : ($hasPage ? 'bg-amber-50 border-l-2 border-amber-400' : '') }}">
                        <span class="text-[#667085] mr-1">{{ $i + 1 }}:</span>
                        <span>{{ $line }}</span>
                        @if($isEntry)
                            <span class="ml-1 px-1 rounded bg-emerald-200 text-[8px] font-bold text-emerald-800">✓</span>
                        @endif
                        @if($hasPage)
                            <span class="ml-1 px-1 rounded bg-amber-200 text-[8px] font-bold text-amber-800">P{{ preg_replace('/.*\|\|(\d+)$/', '$1', $line) }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($entryMatches)
                <div class="mt-4 p-3 rounded-xl bg-emerald-50 ring-1 ring-emerald-200">
                    <p class="text-xs font-bold text-emerald-800">TOC Entries Detected ({{ count($entryMatches) }}):</p>
                    <div class="max-h-40 overflow-auto mt-1">
                        @foreach($entryMatches as $m)
                            <p class="text-[10px] text-emerald-700">#{{ $m['line'] }}: <code class="bg-emerald-100 px-0.5 rounded">{{ Str::limit($m['text'], 60) }}</code></p>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-4 p-3 rounded-xl bg-rose-50 ring-1 ring-rose-200">
                    <p class="text-xs font-bold text-rose-800">Tidak ada TOC entry terdeteksi.</p>
                    <p class="text-[10px] text-rose-600 mt-1">Cek kolom "Split by BAB/LAMPIRAN" — apakah entri berhasil dipisah?</p>
                </div>
            @endif
        </x-card>
    </div>

    <div class="mt-4">
        <a href="{{ route('partitions.index', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold border border-[#e7eaf0] text-[#667085] hover:bg-[#f6f8fb] transition">
            ← Kembali ke Partisi
        </a>
    </div>
@endsection
