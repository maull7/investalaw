@extends('layouts.app')

@section('title', 'Viewer — '.$reviewDocument->title)
@section('header', 'PDF Viewer')

@section('content')
    <div x-data="pdfViewer('{{ route('review-documents.view-file', [$reviewDocument, false]) }}', true)"
        @keydown.arrow-left.window="prevPage()" @keydown.arrow-right.window="nextPage()"
        class="bg-white rounded-2xl ring-1 ring-[#e7eaf0] overflow-hidden flex flex-col h-[calc(100vh-250px)]">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-b border-[#e7eaf0] bg-white">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('review-documents.show', [$reviewDocument, false]) }}"
                    class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-[#071833] truncate">{{ $reviewDocument->title }}</p>
                    <p class="text-[10px] text-[#667085]">{{ $reviewDocument->status->label() }}</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                {{-- Mode toggle --}}
                <div class="flex items-center gap-1 rounded-xl bg-[#f6f8fb] p-1">
                    <button type="button" @click="showingScroll = false" :class="!showingScroll ? 'bg-white shadow-sm text-[#071833]' : 'text-[#667085] hover:text-[#071833]'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">Presentasi</button>
                    <button type="button" @click="toggleScroll()" :class="showingScroll ? 'bg-white shadow-sm text-[#071833]' : 'text-[#667085] hover:text-[#071833]'" class="px-3 py-1.5 text-xs font-bold rounded-lg transition">Scroll</button>
                </div>
                <div class="w-px h-6 bg-[#e7eaf0] mx-1"></div>

                {{-- Page nav (slide mode) --}}
                <div class="flex items-center gap-1.5" x-show="!showingScroll">
                    <button type="button" @click="prevPage()" :disabled="currentPage <= 1" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition disabled:opacity-40 disabled:pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <span class="text-xs font-semibold text-[#071833] whitespace-nowrap" x-text="`${currentPage} / ${totalPages}`"></span>
                    <button type="button" @click="nextPage()" :disabled="currentPage >= totalPages" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition disabled:opacity-40 disabled:pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>

                <div class="w-px h-6 bg-[#e7eaf0] mx-1"></div>

                {{-- Zoom --}}
                <div class="flex items-center gap-1">
                    <button type="button" @click="zoomOut()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM13.5 7.5h-6"/></svg>
                    </button>
                    <span class="text-xs font-semibold text-[#667085] w-10 text-center" x-text="`${Math.round(scale * 100)}%`"></span>
                    <button type="button" @click="zoomIn()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6"/></svg>
                    </button>
                </div>

                {{-- Fullscreen --}}
                <button type="button" @click="toggleFullscreen()"
                    class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                    <svg x-show="!isFullscreen" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M5.25 20.25h-1.5a1.5 1.5 0 0 1-1.5-1.5v-1.5m19.5 4.5H15m6 0V15m0 6V15M3.75 20.25H9m15-12v-1.5a1.5 1.5 0 0 0-1.5-1.5h-1.5M5.25 3.75H9m12 3.75h-1.5a1.5 1.5 0 0 1-1.5-1.5V3.75"/></svg>
                    <svg x-show="isFullscreen" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M15 9h4.5M15 9V4.5m0 4.5 5.25-5.25M15 15H4.5m10.5 0v4.5m0-4.5 5.25 5.25M9 15v4.5M9 15l-5.25 5.25"/></svg>
                    <span x-text="isFullscreen ? 'Keluar' : 'Fullscreen'"></span>
                </button>
            </div>
        </div>

        {{-- Loading / error --}}
        <div x-show="!loaded" class="flex-1 flex items-center justify-center text-sm text-[#667085]">
            <div class="flex flex-col items-center gap-3">
                <template x-if="!error">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                        Memuat dokumen…
                    </div>
                </template>
                <div x-text="error" class="text-rose-600 font-semibold" x-show="error"></div>
            </div>
        </div>

        {{-- Slide mode --}}
        <div x-ref="slideWrapper" x-show="loaded && !showingScroll" class="flex-1 overflow-hidden bg-[#f6f8fb]"
            @mousedown="dragStart($event)" @mouseup="dragEnd($event)"
            @touchstart.passive="dragStart($event)" @touchend.passive="dragEnd($event)">
            <div class="h-full flex items-center justify-center p-6 select-none">
                <div class="flex flex-col items-center gap-3">
                    <canvas x-ref="pdfCanvas" class="shadow-2xl bg-white"></canvas>
                    <p class="text-[10px] text-[#b0b8c5]">Geser kiri/kanan atau tekan ← → untuk ganti halaman</p>
                </div>
            </div>
        </div>

        {{-- Scroll mode --}}
        <div x-show="loaded && showingScroll" class="flex-1 overflow-y-auto overscroll-contain bg-[#f6f8fb]">
            <div class="py-4 space-y-4" x-ref="scrollContainer"></div>
        </div>
    </div>
@endsection