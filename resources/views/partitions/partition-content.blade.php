@extends('layouts.app')

@section('title', $partition->name)
@section('header', $partition->name)

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Konten Partisi
                    </span>
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-white/10 text-white/80 uppercase tracking-wider">{{ $totalPages }} halaman</span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $partition->name }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">
                    Dokumen: {{ $document->title }} &middot; Halaman {{ $partition->start_page }}–{{ $partition->end_page }}
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('partitions.index', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Kembali ke Partisi
                </a>
                <a href="{{ route('review-documents.view-file', $document) }}#page={{ $partition->start_page }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-[#c99a3e]/30 bg-[#c99a3e]/15 hover:bg-[#c99a3e]/25 backdrop-blur transition">
                    Lihat PDF
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-[#071833]">{{ $partition->name }}</h3>
                        <p class="text-xs text-[#667085] mt-0.5">Halaman {{ $partition->start_page }} – {{ $partition->end_page }}</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-[#667085]">
                        <span>{{ $totalPages }} halaman</span>
                        <span>{{ number_format($totalChars) }} karakter</span>
                    </div>
                </div>
            </x-slot>

            @if(empty($pages))
                <div class="text-center py-10">
                    <p class="text-sm text-[#667085]">Belum ada konten parsial untuk partisi ini. Jalankan parser PDF terlebih dahulu.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($pages as $pageData)
                        <div class="rounded-xl bg-[#f6f8fb] p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#c99a3e]">Halaman {{ $pageData['page'] }}</span>
                                <span class="text-[10px] text-[#667085]">{{ number_format($pageData['char_count']) }} karakter</span>
                            </div>
                            <div class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap">
                                @if(trim($pageData['text']))
                                    {{ $pageData['text'] }}
                                @else
                                    <span class="italic text-[#b0b8c5]">[Halaman kosong]</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
@endsection
