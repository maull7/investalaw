@extends('layouts.app')

@section('title', 'AI Preview')
@section('header', 'AI Preview')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        AI Preview
                    </span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">Pilih jenis analisis AI lalu generate untuk melihat hasil preview dokumen.</p>
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
                            <option value="analisa" {{ $selectedType === 'analisa' ? 'selected' : '' }}>Analisa</option>
                            <option value="review" {{ $selectedType === 'review' ? 'selected' : '' }}>Review</option>
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
                        <h3 class="text-lg font-bold text-[#071833]">Hasil AI Preview</h3>
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

                @if($summary)
                    <div class="prose prose-sm max-w-none text-[#071833] leading-relaxed whitespace-pre-wrap">{{ $summary->summary }}</div>
                @else
                    <div class="text-center py-14">
                        <div class="mx-auto w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z"/></svg>
                        </div>
                        <p class="mt-4 text-base font-bold text-[#071833]">Belum ada hasil AI</p>
                        <p class="mt-1 text-sm text-[#667085]">Pilih jenis analisis di atas lalu klik "Generate AI" untuk memulai.</p>
                    </div>
                @endif
            </x-card>
        </div>

        <aside class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Details</h3>
                </x-slot>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Type</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833] capitalize">{{ $selectedType }}</dd>
                    </div>
                    @if($summary)
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Provider</dt>
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
