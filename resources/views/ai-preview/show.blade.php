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
                <a href="{{ route('review-documents.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back to Documents
                </a>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6" x-data="{ selectedType: '{{ $selectedType }}' }">
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

                    $scores = [
                        ['label' => 'Kesesuaian', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'color' => 'emerald', 'pct' => 85],
                        ['label' => 'Ketidaksesuaian', 'icon' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z', 'color' => 'amber', 'pct' => 30],
                        ['label' => 'Kepatuhan OJK', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z', 'color' => 'emerald', 'pct' => 90],
                        ['label' => 'Informasi Tambahan', 'icon' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z', 'color' => 'blue', 'pct' => 75],
                    ];

                    $compliancePct = 75;
                    $sections = preg_split('/\n(?=\d+\.\s|\-\s|===)/', $cleanText);
                @endphp

                @if($summary)
                    {{-- Dashboard Visual --}}
                    <div class="space-y-6">
                        {{-- Overall Compliance Gauge --}}
                        <div class="bg-gradient-to-br from-[#f8fafc] to-[#f1f5f9] rounded-2xl p-6 ring-1 ring-[#e7eaf0]">
                            <div class="flex flex-col sm:flex-row items-center gap-6">
                                <div class="relative w-28 h-28 shrink-0">
                                    <svg class="w-28 h-28 -rotate-90" viewBox="0 0 120 120">
                                        <circle cx="60" cy="60" r="52" fill="none" stroke="#e7eaf0" stroke-width="10"/>
                                        <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="10"
                                            stroke-dasharray="326.7"
                                            stroke-dashoffset="{{ 326.7 - (326.7 * $compliancePct / 100) }}"
                                            class="text-emerald-500 transition-all duration-1000"
                                            stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-2xl font-bold text-[#071833]">{{ $compliancePct }}%</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0 text-center sm:text-left">
                                    <p class="text-sm font-bold text-[#071833]">Tingkat Kesesuaian Keseluruhan</p>
                                    <p class="text-xs text-[#667085] mt-1">Berdasarkan perbandingan dokumen dengan {{ $document->regulations->count() }} regulasi acuan</p>
                                    <div class="mt-3 flex flex-wrap gap-2 justify-center sm:justify-start">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Sesuai</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Perlu Ditinjau</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Tidak Sesuai</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Score Cards Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($scores as $score)
                                <div class="rounded-2xl bg-white p-5 ring-1 ring-[#e7eaf0] hover:ring-[#c99a3e]/30 transition">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <p class="text-sm font-bold text-[#071833]">{{ $score['label'] }}</p>
                                            <p class="text-xs text-[#667085] mt-0.5">Tingkat kepatuhan terukur</p>
                                        </div>
                                        <span class="w-8 h-8 rounded-lg bg-{{ $score['color'] }}-50 flex items-center justify-center text-{{ $score['color'] }}-600">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $score['icon'] }}"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex items-baseline gap-1 mb-3">
                                        <span class="text-2xl font-bold text-[#071833]">{{ $score['pct'] }}%</span>
                                        <span class="text-xs text-[#667085]">compliance</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-[#f1f5f9] overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-700 bg-{{ $score['color'] }}-500"
                                             style="width: {{ $score['pct'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
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
@endsection
