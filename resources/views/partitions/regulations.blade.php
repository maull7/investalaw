@extends('layouts.app')

@section('title', 'Regulasi Acuan — ' . $document->title)
@section('header', 'Regulasi Acuan')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Regulasi Acuan
                    </span>
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-white/10 text-white/80 uppercase tracking-wider">{{ count($regulations) }} regulasi</span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">Daftar regulasi acuan yang digunakan dalam dokumen ini beserta hasil parser teks.</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('partitions.parsed-text', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Kembali ke Hasil Parser
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6 space-y-3">
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
            @foreach($regulations as $reg)
                <div class="rounded-2xl border border-[#e7eaf0] overflow-hidden bg-white">
                    <div class="flex items-center justify-between px-5 py-4">
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
                            @if($reg['main_parsed'])
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">{{ number_format($reg['main_chars']) }} char</span>
                            @elseif($reg['is_parsed'])
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">0 char</span>
                            @elseif(! $reg['has_file'])
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-[10px] font-bold text-rose-700">No file</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">Belum Parse</span>
                            @endif
                            @if(count($reg['documents']) > 0)
                                <span class="px-2 py-0.5 rounded-full bg-sky-100 text-[10px] font-bold text-sky-700">{{ count($reg['documents']) }} dok</span>
                            @endif
                        </div>
                    </div>
                    <div class="px-5 pb-5 space-y-4 border-t border-[#e7eaf0] pt-4">
                        @if(! $reg['has_file'])
                            <div class="rounded-xl bg-rose-50 p-4 ring-1 ring-rose-200 text-center">
                                <p class="text-sm font-semibold text-rose-700">Tidak ada file regulasi</p>
                                <p class="text-xs text-rose-600 mt-1">Upload file PDF pada halaman regulasi untuk melakukan parser.</p>
                            </div>
                        @elseif(! $reg['is_parsed'])
                            <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200 text-center">
                                <p class="text-sm font-semibold text-amber-800">Parser Regulasi Belum Tersedia</p>
                                <p class="text-xs text-amber-700 mt-1">Lakukan parser PDF pada halaman regulasi untuk melihat teks hasil ekstraksi.</p>
                            </div>
                        @elseif(! $reg['has_text'])
                            <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200 text-center">
                                <p class="text-sm font-semibold text-amber-800">Parser Selesai — Tidak Ada Teks</p>
                                <p class="text-xs text-amber-700 mt-1">PDF sudah diparse tetapi tidak menghasilkan teks (mungkin halaman kosong atau OCR gagal).</p>
                            </div>
                        @else
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085] mb-2">File Regulasi Utama — {{ number_format($reg['main_chars']) }} karakter</p>
                                <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0] max-h-96 overflow-y-auto">
                                    <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0] max-h-96 overflow-y-auto">
                                    <div class="text-xs text-[#071833] leading-relaxed">@formatText($reg['main_text'])</div>
                                </div>
                            </div>
                        @endif
                        @foreach($reg['documents'] as $doc)
                            <div class="border-t border-[#e7eaf0] pt-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    <p class="text-xs font-bold text-[#071833]">{{ $doc['name'] }}</p>
                                    @if($doc['has_text'])
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">{{ number_format($doc['chars']) }} char</span>
                                    @elseif($doc['is_parsed'])
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">0 char</span>
                                    @elseif(! $doc['has_file'])
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-[10px] font-bold text-rose-700">No file</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">Belum Parse</span>
                                    @endif
                                </div>
                                @if($doc['has_text'])
                                    <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0] max-h-96 overflow-y-auto">
                                        <div class="text-xs text-[#071833] leading-relaxed">@formatText($doc['text'])</div>
                                    </div>
                                @elseif(! $doc['has_file'])
                                    <p class="text-xs text-[#667085] italic">Dokumen tidak memiliki file.</p>
                                @elseif(! $doc['is_parsed'])
                                    <p class="text-xs text-[#667085] italic">Dokumen belum diparse. Lakukan parser pada halaman regulasi.</p>
                                @else
                                    <p class="text-xs text-[#667085] italic">Dokumen sudah diparse tetapi tidak mengandung teks.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection