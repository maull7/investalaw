@extends('layouts.app')

@section('title', 'AI Preview - Perbandingan Dokumen')
@section('header', 'AI Preview')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        AI Preview · Perbandingan
                    </span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">AI akan membandingkan konten dokumen yang di-review dengan <strong class="text-white">{{ $document->regulations->count() }} regulasi</strong> yang dipilih untuk menghasilkan analisis perbandingan yang komprehensif.</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('partitions.parsed-text', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-[#c99a3e]/30 bg-[#c99a3e]/15 hover:bg-[#c99a3e]/25 backdrop-blur transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Lihat Hasil Parser
                </a>
                <a href="{{ route('partitions.index', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                    Partisi Dokumen
                </a>
                <a href="{{ route('review-documents.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back to Documents
                </a>
            </div>
        </div>
    </section>

    <div x-data="{ mainTab: 'analysis', selectedType: '{{ $selectedType }}' }" class="mt-6">
        {{-- Tab Navigation --}}
        <div class="flex gap-1 bg-[#f6f8fb] rounded-2xl p-1.5 ring-1 ring-[#e7eaf0] mb-6">
            <button @click="mainTab = 'analysis'"
                    :class="mainTab === 'analysis' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    AI Preview
                </span>
            </button>
            <button @click="mainTab = 'parsed'"
                    :class="mainTab === 'parsed' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Parse PDF
                    @if($parsedTexts->isNotEmpty())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">{{ $parsedTexts->count() }}</span>
                    @endif
                </span>
            </button>
        </div>

        {{-- Tab: Analysis --}}
        <div x-show="mainTab === 'analysis'">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Comparison Overview --}}
            @if($document->regulations->isNotEmpty())
                <x-card>
                    <x-slot name="header">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                            <h3 class="text-lg font-bold text-[#071833]">Objek Perbandingan</h3>
                        </div>
                    </x-slot>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0]">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#c99a3e]"></span>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Dokumen Di-Review</span>
                            </div>
                            <p class="text-sm font-semibold text-[#071833]">{{ $document->title }}</p>
                            <p class="text-xs text-[#667085] mt-1">Status: <span class="font-medium text-[#071833]">{{ $document->status->label() }}</span></p>
                            @if($document->partitions->isNotEmpty())
                                <div class="mt-2 pt-2 border-t border-[#e7eaf0]">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085] mb-1">Partisi ({{ $document->partitions->count() }})</p>
                                    <div class="space-y-0.5">
                                        @foreach($document->partitions as $p)
                                            <p class="text-[11px] text-[#071833]">• {{ $p->name }} <span class="text-[#667085]">(h.{{ $p->start_page }}–{{ $p->end_page }})</span></p>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0]">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#0b5e42]"></span>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Regulasi Pembanding</span>
                            </div>
                            <div class="space-y-1">
                                @foreach($document->regulations as $reg)
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        <span class="text-sm text-[#071833]">{{ $reg->regulation_number }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-card>
            @endif

            {{-- Insight: Parse Comparison Chart --}}
            @php
                $docParsed = $parsedTexts->where('source_type', 'document');
                $regParsed = $parsedTexts->where('source_type', 'regulation');
                $docTotalChars = $docParsed->sum('char_count');
                $regTotalChars = $regParsed->sum('char_count');
                $maxChars = max($docTotalChars, $regTotalChars, 1);
                $docPercent = round(($docTotalChars / $maxChars) * 100);
                $regPercent = round(($regTotalChars / $maxChars) * 100);
            @endphp
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        <h3 class="text-lg font-bold text-[#071833]">Insight Perbandingan</h3>
                    </div>
                </x-slot>

                @if($parsedTexts->isNotEmpty())
                    {{-- Bar Chart --}}
                    <div class="space-y-4 mb-6">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-semibold text-[#071833]">Dokumen Review</span>
                                <span class="text-xs font-bold text-[#667085]">{{ number_format($docTotalChars) }} karakter</span>
                            </div>
                            <div class="w-full h-6 rounded-full bg-[#f6f8fb] ring-1 ring-[#e7eaf0] overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] transition-all duration-500" style="width: {{ $docPercent }}%"></div>
                            </div>
                            <p class="text-[10px] text-[#667085] mt-1">{{ $docParsed->count() }} {{ $document->partitions->isNotEmpty() ? 'partisi' : 'bagian' }} · Rata-rata {{ $docParsed->count() > 0 ? number_format($docTotalChars / $docParsed->count()) : 0 }} char/bagian</p>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-semibold text-[#071833]">Regulasi Acuan</span>
                                <span class="text-xs font-bold text-[#667085]">{{ number_format($regTotalChars) }} karakter</span>
                            </div>
                            <div class="w-full h-6 rounded-full bg-[#f6f8fb] ring-1 ring-[#e7eaf0] overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-all duration-500" style="width: {{ $regPercent }}%"></div>
                            </div>
                            <p class="text-[10px] text-[#667085] mt-1">{{ $regParsed->count() }} regulasi · {{ $document->regulations->filter(fn($r) => $r->file_path)->count() }} file tersedia</p>
                        </div>
                    </div>

                    {{-- Doughnut chart --}}
                    <div class="flex items-center gap-6 mb-6" x-data="parseComparisonChart({{ $docTotalChars }}, {{ $regTotalChars }})">
                        <div class="w-32 h-32 shrink-0">
                            <canvas x-ref="parseChart"></canvas>
                        </div>
                        <div class="space-y-2 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#c99a3e]"></span>
                                <span class="text-sm text-[#071833]">Dokumen: <strong>{{ $docTotalChars > 0 ? round(($docTotalChars / max($docTotalChars + $regTotalChars, 1)) * 100) : 0 }}%</strong></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="text-sm text-[#071833]">Regulasi: <strong>{{ $regTotalChars > 0 ? round(($regTotalChars / max($docTotalChars + $regTotalChars, 1)) * 100) : 0 }}%</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Insight Explanation --}}
                    <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0] space-y-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Analisis Perbandingan Data</p>
                        @if($regTotalChars === 0)
                            <div class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                </span>
                                <p class="text-sm text-[#071833] leading-relaxed"><strong>File regulasi tidak bisa diekstrak teksnya.</strong> Kemungkinan besar file PDF regulasi berupa scan/gambar (non-OCR). AI tidak bisa membandingkan konten regulasi secara langsung. Hasil analisa mungkin kurang akurat karena AI hanya menggunakan metadata regulasi (nomor, judul, tahun) tanpa isi pasal-pasal.</p>
                            </div>
                        @elseif($docTotalChars > $regTotalChars * 3)
                            <div class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                </span>
                                <p class="text-sm text-[#071833] leading-relaxed"><strong>Dokumen jauh lebih panjang dari regulasi.</strong> Rasio {{ number_format($docTotalChars / max($regTotalChars, 1), 1) }}x lebih banyak konten. Ini bisa berarti dokumen sangat detail atau regulasi hanya mencakup sebagian aspek. AI akan fokus memetakan bagian dokumen yang relevan terhadap regulasi.</p>
                            </div>
                        @elseif($regTotalChars > $docTotalChars * 3)
                            <div class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                </span>
                                <p class="text-sm text-[#071833] leading-relaxed"><strong>Regulasi jauh lebih panjang dari dokumen.</strong> Regulasi memiliki banyak ketentuan yang mungkin belum semuanya diakomodasi. Perhatikan temuan gap/celah pada hasil analisa.</p>
                            </div>
                        @else
                            <div class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                                <p class="text-sm text-[#071833] leading-relaxed"><strong>Proporsi konten seimbang.</strong> Dokumen dan regulasi memiliki volume konten yang proporsional. AI dapat melakukan perbandingan yang komprehensif antara keduanya.</p>
                            </div>
                        @endif

                        @if($document->partitions->isNotEmpty())
                            <div class="flex items-start gap-2 pt-2 border-t border-[#e7eaf0]">
                                <span class="w-5 h-5 rounded-full bg-[#c99a3e]/10 text-[#c99a3e] flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                                </span>
                                <p class="text-sm text-[#071833] leading-relaxed">Analisa menggunakan <strong>{{ $document->partitions->count() }} partisi</strong> yang Anda buat. Setiap partisi dianalisa secara terpisah terhadap semua regulasi, menghasilkan perbandingan yang lebih granular.</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-[#667085] text-center py-6">Generate AI terlebih dahulu untuk melihat insight perbandingan.</p>
                @endif
            </x-card>

            {{-- Controls --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-[#071833]">Generate AI Preview</h3>
                </x-slot>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select x-model="selectedType" @change="window.location.href = '{{ route('ai-preview.show', $document) }}?type=' + $event.target.value" class="select-premium flex-1">
                        @foreach($prompts as $prompt)
                            <option value="{{ $prompt->type }}">{{ $prompt->title ?? ucfirst($prompt->type) }}</option>
                        @endforeach
                        @if($prompts->isEmpty())
                            <option value="analisa" {{ $selectedType === 'analisa' ? 'selected' : '' }}>Analisa Perbandingan</option>
                            <option value="review" {{ $selectedType === 'review' ? 'selected' : '' }}>Review Kesesuaian</option>
                            <option value="rekomendasi" {{ $selectedType === 'rekomendasi' ? 'selected' : '' }}>Rekomendasi</option>
                            <option value="validitas" {{ $selectedType === 'validitas' ? 'selected' : '' }}>Validitas</option>
                        @endif
                    </select>
                    <form method="POST" action="{{ route('ai-preview.generate', $document) }}">
                        @csrf
                        <input type="hidden" name="type" x-bind:value="selectedType">
                        <x-button type="submit" variant="primary" size="lg" class="w-full sm:w-auto whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z"/></svg>
                            Generate AI
                        </x-button>
                    </form>
                </div>
            </x-card>

            {{-- Result --}}
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-[#071833]">Hasil Perbandingan</h3>
                            @if($summary)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#f6f8fb] text-[#667085] ring-1 ring-[#e7eaf0]">{{ $summary->provider_used }}</span>
                            @endif
                        </div>
                        @if($summary)
                            <span class="text-xs text-[#667085]">Generated {{ $summary->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </x-slot>

                @if(session('success'))
                    <div class="mb-4 flex items-center gap-2 p-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-center gap-2 p-3 rounded-xl bg-rose-50 text-rose-700 text-sm font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @php
                    $summaryText = $summary?->summary ?? '';
                    $cleanText = preg_replace('/\*\*(.*?)\*\*/', '$1', $summaryText);
                    $cleanText = preg_replace('/\* /', '- ', $cleanText);
                    $cleanText = preg_replace('/[─]{3,}/', '---', $cleanText);
                @endphp

                @if($summary)
                    <div class="space-y-6">
                        {{-- Summary Overview Cards --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="rounded-2xl bg-white p-5 ring-1 ring-[#e7eaf0] text-center">
                                <span class="mx-auto w-10 h-10 rounded-xl bg-[#c99a3e]/10 text-[#c99a3e] flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                </span>
                                <p class="text-2xl font-bold text-[#071833]">{{ number_format($docTotalChars) }}</p>
                                <p class="text-xs font-medium text-[#667085] mt-1">Karakter Dokumen</p>
                                <p class="text-[10px] text-[#c99a3e] font-semibold mt-0.5">{{ $docParsed->count() }} {{ $document->partitions->isNotEmpty() ? 'partisi' : 'bagian' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white p-5 ring-1 ring-[#e7eaf0] text-center">
                                <span class="mx-auto w-10 h-10 rounded-xl {{ $regTotalChars > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500' }} flex items-center justify-center mb-3">
                                    @if($regTotalChars > 0)
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                    @endif
                                </span>
                                <p class="text-2xl font-bold text-[#071833]">{{ number_format($regTotalChars) }}</p>
                                <p class="text-xs font-medium text-[#667085] mt-1">Karakter Regulasi</p>
                                <p class="text-[10px] font-semibold mt-0.5 {{ $regTotalChars > 0 ? 'text-emerald-600' : 'text-rose-500' }}">{{ $regTotalChars > 0 ? 'Terekstrak' : 'Scan — perlu OCR' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white p-5 ring-1 ring-[#e7eaf0] text-center">
                                <span class="mx-auto w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                                </span>
                                <p class="text-2xl font-bold text-[#071833] capitalize">{{ $selectedType }}</p>
                                <p class="text-xs font-medium text-[#667085] mt-1">Jenis Analisa</p>
                                <p class="text-[10px] text-sky-600 font-semibold mt-0.5">{{ $document->regulations->count() }} regulasi</p>
                            </div>
                            <div class="rounded-2xl bg-white p-5 ring-1 ring-[#e7eaf0] text-center">
                                <span class="mx-auto w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </span>
                                @if($summary)
                                    <p class="text-lg font-bold text-[#071833]">{{ $summary->provider_used }}</p>
                                    <p class="text-xs font-medium text-[#667085] mt-1">{{ $summary->model_used ?? 'AI Provider' }}</p>
                                    <p class="text-[10px] text-violet-600 font-semibold mt-0.5">{{ $summary->created_at->diffForHumans() }}</p>
                                @else
                                    <p class="text-2xl font-bold text-[#071833]">—</p>
                                    <p class="text-xs font-medium text-[#667085] mt-1">AI Provider</p>
                                    <p class="text-[10px] text-[#667085] font-semibold mt-0.5">Belum digenerate</p>
                                @endif
                            </div>
                        </div>

                        {{-- Analisis Detail --}}
                        <div x-data="{ showDetail: true }" class="rounded-2xl bg-[#fafbfc] ring-1 ring-[#e7eaf0] overflow-hidden">
                            <button @click="showDetail = !showDetail" class="w-full flex items-center justify-between px-5 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    <span class="text-sm font-bold text-[#071833]">Analisis Detail</span>
                                </div>
                                <svg class="w-4 h-4 text-[#667085]" :class="showDetail ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-show="showDetail" x-collapse>
                                <div class="px-5 pb-5 space-y-4">
                                    @foreach(explode("\n", $cleanText) as $line)
                                        @if(trim($line))
                                            @php
                                                $isHeader = preg_match('/^[A-Z\s]{4,}[:]$/', trim($line)) || preg_match('/^===/', $line);
                                                $isSubHeader = preg_match('/^[A-Z][a-z]+.*:$/', trim($line)) && strlen($line) < 80;
                                                $isBullet = preg_match('/^[-•·]\s/', trim($line));
                                            @endphp
                                            @if($isHeader)
                                                <div class="pt-3 first:pt-0">
                                                    <h4 class="text-sm font-bold text-[#071833] border-l-3 border-[#c99a3e] pl-3 leading-relaxed">{{ preg_replace('/^[=\s]+|[=\s]+$/', '', trim($line)) }}</h4>
                                                </div>
                                            @elseif($isSubHeader)
                                                <p class="text-sm font-semibold text-[#071833] mt-3 first:mt-0">{{ $line }}</p>
                                            @elseif($isBullet)
                                                <div class="flex items-start gap-2 pl-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#c99a3e]/60 mt-2 shrink-0"></span>
                                                    <p class="text-sm text-[#4a5568] leading-relaxed">{{ preg_replace('/^[-•·]\s/', '', trim($line)) }}</p>
                                                </div>
                                            @else
                                                <p class="text-sm text-[#4a5568] leading-relaxed">{{ $line }}</p>
                                            @endif
                                        @endif
                                    @endforeach

                                    @if($document->partitions->isNotEmpty())
                                        <div class="pt-4 mt-4 border-t border-[#e7eaf0]">
                                            <div class="flex items-center justify-between mb-4">
                                                <h4 class="text-sm font-bold text-[#071833]">Analisa Per-Partisi</h4>
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $document->partitions->filter(fn($p) => $p->analysis !== null)->count() }}/{{ $document->partitions->count() }} dianalisa</span>
                                            </div>
                                            <div class="space-y-2">
                                                @foreach($document->partitions as $partition)
                                                    @php $pa = $partition->analysis; @endphp
                                                    <div class="rounded-xl bg-[#f6f8fb] p-3 ring-1 ring-[#e7eaf0]">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-xs font-semibold text-[#071833]">{{ $partition->name }} <span class="text-[10px] text-[#667085] font-normal">(Hal {{ $partition->start_page }}–{{ $partition->end_page }})</span></span>
                                                            @if($pa)
                                                                @php
                                                                    $sc = $pa->compliance_status;
                                                                    $badgeColor = match($sc?->value) {
                                                                        'compliant' => 'emerald',
                                                                        'partially_compliant' => 'amber',
                                                                        'non_compliant' => 'rose',
                                                                        default => 'gray'
                                                                    };
                                                                @endphp
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $badgeColor }}-100 text-{{ $badgeColor }}-700">{{ $sc?->label() ?? 'Terisi' }}</span>
                                                            @else
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Belum Dianalisa</span>
                                                            @endif
                                                        </div>
                                                        @if($pa?->summary)
                                                            <p class="text-xs text-[#4a5568] leading-relaxed">{{ Str::limit($pa->summary, 200) }}</p>
                                                        @endif
                                                        @if($pa?->findings)
                                                            <p class="text-xs text-[#4a5568] leading-relaxed mt-1.5 pt-1.5 border-t border-[#e7eaf0]">
                                                                <span class="font-semibold text-[#071833]">Temuan:</span> {{ Str::limit($pa->findings, 150) }}
                                                            </p>
                                                        @endif
                                                        @if($pa?->compliance_score)
                                                            <div class="mt-1.5 text-[10px] text-[#667085]">
                                                                Skor Kepatuhan: <span class="font-bold text-[#071833]">{{ $pa->compliance_score }}/100</span>
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

                        @if($activePrompt)
                            <div class="border-t border-[#e7eaf0] pt-5" x-data="{ showSource: false }">
                                <button @click="showSource = !showSource" type="button" class="inline-flex items-center gap-2 text-sm font-semibold text-[#667085] hover:text-[#071833] transition">
                                    <svg class="w-4 h-4" :class="showSource ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    <span x-text="showSource ? 'Sembunyikan' : 'Lihat'"></span> Sumber Perbandingan
                                </button>
                                <div x-show="showSource" x-collapse class="mt-4 space-y-4">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085] mb-2">Prompt yang Digunakan</p>
                                        <div class="bg-[#f6f8fb] rounded-xl p-4 ring-1 ring-[#e7eaf0]">
                                            <pre class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap font-mono">{{ $activePrompt->prompt_text }}</pre>
                                        </div>
                                    </div>
                                    @if($summary->prompt_text && $summary->prompt_text !== $activePrompt->prompt_text)
                                        <div>
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085] mb-2">Prompt Tersimpan (saat generate)</p>
                                            <div class="bg-[#f6f8fb] rounded-xl p-4 ring-1 ring-[#e7eaf0]">
                                                <pre class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap font-mono">{{ $summary->prompt_text }}</pre>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-14">
                        <div class="mx-auto w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z"/></svg>
                        </div>
                        <p class="mt-4 text-base font-bold text-[#071833]">Belum ada hasil perbandingan</p>
                        <p class="mt-1 text-sm text-[#667085]">Pilih jenis analisis di atas lalu klik "Generate AI" untuk memulai perbandingan dokumen dengan regulasi.</p>
                    </div>
                @endif
            </x-card>

            {{-- Per-Partition Manual Analysis Results --}}
            @if($document->partitions->isNotEmpty())
                <x-card>
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-[#071833]">Analisa Per-Partisi</h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $document->partitions->filter(fn($p) => $p->analysis !== null)->count() }}/{{ $document->partitions->count() }} dianalisa</span>
                        </div>
                    </x-slot>
                    <div x-data="{ expandedPartition: null }" class="space-y-3">
                        @foreach($document->partitions as $partition)
                            @php $analysis = $partition->analysis; @endphp
                            <div class="rounded-2xl border border-[#e7eaf0] overflow-hidden">
                                <button
                                    @click="expandedPartition = expandedPartition === {{ $partition->id }} ? null : {{ $partition->id }}"
                                    class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-[#f6f8fb] transition">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-[#c99a3e] shrink-0 transition-transform" :class="expandedPartition === {{ $partition->id }} ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                        <div>
                                            <span class="text-sm font-bold text-[#071833]">{{ $partition->name }}</span>
                                            <span class="text-[10px] text-[#667085] ml-1">(Hal {{ $partition->start_page }}–{{ $partition->end_page }})</span>
                                        </div>
                                    </div>
                                    @if($analysis)
                                        @php
                                            $sc = $analysis->compliance_status;
                                            $badgeColor = match($sc?->value) {
                                                'compliant' => 'emerald',
                                                'partially_compliant' => 'amber',
                                                'non_compliant' => 'rose',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $badgeColor }}-100 text-{{ $badgeColor }}-700">{{ $sc?->label() ?? 'Terisi' }}</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Belum Dianalisa</span>
                                    @endif
                                </button>

                                {{-- Preview ringkasan singkat tanpa expand --}}
                                @if($analysis?->summary)
                                    <div class="px-5 pb-2 -mt-1">
                                        <p class="text-xs text-[#667085] line-clamp-2">{{ Str::limit($analysis->summary, 150) }}</p>
                                    </div>
                                @endif

                                <div x-show="expandedPartition === {{ $partition->id }}" x-collapse>
                                    @if($analysis)
                                        <div class="px-5 pb-4 space-y-3">
                                            @if($analysis->summary)
                                                <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0]">
                                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085] mb-1">Ringkasan</p>
                                                    <p class="text-sm text-[#071833] leading-relaxed">{{ $analysis->summary }}</p>
                                                </div>
                                            @endif
                                            @if($analysis->findings)
                                                <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0]">
                                                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085] mb-1">Temuan</p>
                                                    <p class="text-sm text-[#071833] leading-relaxed">{{ $analysis->findings }}</p>
                                                </div>
                                            @endif
                                            @if($analysis->compliance_score)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Skor:</span>
                                                    <span class="text-sm font-bold text-[#071833]">{{ $analysis->compliance_score }}/100</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="px-5 pb-4">
                                            <p class="text-sm text-[#667085]">Belum ada analisa. <a href="{{ route('partitions.index', $document) }}" class="text-[#c99a3e] hover:underline font-semibold">Generate di halaman Partisi.</a></p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <aside class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Detail Perbandingan</h3>
                </x-slot>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Jenis Analisis</dt>
                        <dd class="mt-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-[#c99a3e]/10 text-[#c99a3e] capitalize">
                                {{ $selectedType }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Jumlah Regulasi</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $document->regulations->count() }} regulasi</dd>
                    </div>
                    @if($summary)
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Provider AI</dt>
                            <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $summary->provider_used }}</dd>
                        </div>
                        @if($summary->model_used)
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Model</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $summary->model_used }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Generated At</dt>
                            <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $summary->created_at->format('d F Y · H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            @if($document->partitions->isNotEmpty())
                <x-card>
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#071833]">Partisi Dokumen</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">{{ $document->partitions->count() }}</span>
                        </div>
                    </x-slot>
                    <div class="space-y-2">
                        @foreach($document->partitions as $partition)
                            <div class="flex items-center justify-between rounded-xl bg-[#f6f8fb] p-3 ring-1 ring-[#e7eaf0]">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-[#071833] truncate">{{ $partition->name }}</p>
                                    @if($partition->description)
                                        <p class="text-[10px] text-[#667085] mt-0.5 truncate">{{ $partition->description }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-[#c99a3e]/10 text-[#c99a3e] font-bold text-[10px]">
                                    h.{{ $partition->start_page }}–{{ $partition->end_page }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if($document->total_pages)
                        <div class="mt-3 pt-3 border-t border-[#e7eaf0] flex items-center justify-between text-[10px]">
                            <span class="font-bold text-[#667085] uppercase tracking-wider">Total Halaman</span>
                            <span class="font-bold text-[#071833]">{{ $document->partitions->sum(fn($p) => ($p->end_page - $p->start_page) + 1) }} / {{ $document->total_pages }} hlm</span>
                        </div>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('partitions.index', $document) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#c99a3e] hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6 9.75-9.75M15 3h6v6"/></svg>
                            Kelola Partisi
                        </a>
                    </div>
                </x-card>
            @endif

            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Regulasi Pembanding</h3>
                </x-slot>
                @if($document->regulations->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($document->regulations as $reg)
                            <div class="rounded-xl bg-[#f6f8fb] p-3.5 ring-1 ring-[#e7eaf0]">
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-md bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0 mt-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-[#071833] leading-snug">{{ $reg->regulation_number }}</p>
                                        <p class="mt-1 text-xs text-[#667085] leading-relaxed">{{ $reg->title }}</p>
                                        @if($reg->type)
                                            <span class="mt-1.5 inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-[#c99a3e]/10 text-[#c99a3e]">{{ $reg->type->name }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($reg->documents->isNotEmpty())
                                    <div class="mt-3 pt-3 border-t border-[#e7eaf0] space-y-1">
                                        @foreach($reg->documents as $doc)
                                            <div class="flex items-center gap-1.5 text-[11px] text-[#667085]">
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                                <span class="truncate">{{ $doc->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if($reg->file_path)
                                    <div class="mt-2 flex items-center gap-1.5 text-[11px] text-emerald-600 font-medium">
                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        File tersedia untuk analisa
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[#667085]">Belum ada regulasi yang dipilih untuk dokumen ini. Pilih regulasi terlebih dahulu untuk melakukan perbandingan.</p>
                @endif
            </x-card>

            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Actions</h3>
                </x-slot>
                <div class="space-y-2.5">
                    <a href="{{ route('ai-summaries.index', $document) }}" class="inline-flex items-center gap-2 w-full px-4 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition justify-start">
                        <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z"/></svg>
                        AI Summaries
                    </a>
                    <a href="{{ route('review-documents.show', $document) }}" class="inline-flex items-center gap-2 w-full px-4 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition justify-start">
                        <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        View Document
                    </a>
                </div>
            </x-card>
        </aside>
    </div>
    </div>{{-- end analysis tab --}}

        {{-- Tab: Parse PDF --}}
        <div x-show="mainTab === 'parsed'" x-cloak>
            @if($parsedTexts->isNotEmpty())
                <div x-data="{ parsedSubTab: 'document' }">
                    <div class="flex gap-1 bg-[#f6f8fb] rounded-2xl p-1.5 ring-1 ring-[#e7eaf0] mb-4">
                        <button @click="parsedSubTab = 'document'"
                                :class="parsedSubTab === 'document' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                            Dokumen
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#c99a3e]/10 text-[#c99a3e]">{{ $parsedTexts->where('source_type', 'document')->count() }}</span>
                        </button>
                        <button @click="parsedSubTab = 'regulation'"
                                :class="parsedSubTab === 'regulation' ? 'bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]' : 'text-[#667085] hover:text-[#071833]'"
                                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                            Regulasi
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">{{ $parsedTexts->where('source_type', 'regulation')->count() }}</span>
                        </button>
                    </div>

                    {{-- Document parsed texts --}}
                    <div x-show="parsedSubTab === 'document'" class="space-y-3">
                        @foreach($parsedTexts->where('source_type', 'document') as $parsed)
                            <x-card>
                                <x-slot name="header">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-bold text-[#071833]">
                                            @if($parsed->source_id)
                                                @php $partition = $document->partitions->firstWhere('id', $parsed->source_id); @endphp
                                                Partisi: {{ $partition?->name ?? 'Unknown' }} (h.{{ $parsed->page }})
                                            @else
                                                Dokumen Lengkap
                                            @endif
                                        </h3>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#f6f8fb] text-[#667085]">{{ number_format($parsed->char_count) }} char</span>
                                    </div>
                                </x-slot>
                                <div x-data="{ expanded: false }">
                                    <div :class="expanded ? '' : 'max-h-48 overflow-hidden relative'">
                                        <pre class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap font-mono break-words">{{ $parsed->parsed_text }}</pre>
                                        <div x-show="!expanded" class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
                                    </div>
                                    <button @click="expanded = !expanded" class="mt-2 text-xs font-semibold text-[#c99a3e] hover:underline" x-text="expanded ? 'Tutup' : 'Lihat Semua'"></button>
                                </div>
                            </x-card>
                        @endforeach
                    </div>

                    {{-- Regulation parsed texts --}}
                    <div x-show="parsedSubTab === 'regulation'" x-cloak class="space-y-3">
                        @foreach($parsedTexts->where('source_type', 'regulation') as $parsed)
                            @php $reg = $document->regulations->firstWhere('id', $parsed->source_id); @endphp
                            <x-card>
                                <x-slot name="header">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-bold text-[#071833]">{{ $reg?->regulation_number ?? 'Regulasi' }} — {{ $reg?->title ?? '' }}</h3>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">{{ number_format($parsed->char_count) }} char</span>
                                    </div>
                                </x-slot>
                                <div x-data="{ expanded: false }">
                                    <div :class="expanded ? '' : 'max-h-48 overflow-hidden relative'">
                                        <pre class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap font-mono break-words">{{ $parsed->parsed_text }}</pre>
                                        <div x-show="!expanded" class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
                                    </div>
                                    <button @click="expanded = !expanded" class="mt-2 text-xs font-semibold text-[#c99a3e] hover:underline" x-text="expanded ? 'Tutup' : 'Lihat Semua'"></button>
                                </div>
                            </x-card>
                        @endforeach
                    </div>
                </div>
            @else
                <x-card>
                    <div class="text-center py-14">
                        <div class="mx-auto w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <p class="mt-4 text-base font-bold text-[#071833]">Belum ada data parse</p>
                        <p class="mt-1 text-sm text-[#667085]">Data parse PDF akan tersimpan otomatis saat Anda melakukan "Generate AI". Klik Generate AI terlebih dahulu.</p>
                    </div>
                </x-card>
            @endif
        </div>
    </div>{{-- end x-data mainTab --}}
@endsection
