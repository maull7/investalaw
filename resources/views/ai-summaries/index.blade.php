@extends('layouts.app')

@section('title', 'AI Summaries')
@section('header', 'AI Summaries')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        AI Summary
                    </span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">Generate AI-powered analysis based on the document and its related regulations.</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('review-documents.show', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back to Document
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
        @php
            $types = [
                'analisa' => ['label' => 'Analisa', 'desc' => 'Analisa dokumen terhadap peraturan terkait', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                'review' => ['label' => 'Review', 'desc' => 'Review kesesuaian dokumen dengan peraturan', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                'rekomendasi' => ['label' => 'Rekomendasi', 'desc' => 'Review dan rekomendasi penambahan klausul', 'icon' => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.153.212 2.25.52 3.117.938M18.75 4.97V12m0 0v7.03m0-7.03c0 1.544-1.227 2.797-2.25 3.02M4.5 4.97V12m0 0v7.03m0-7.03c0-1.544 1.227-2.797 2.25-3.02'],
                'validitas' => ['label' => 'Validitas', 'desc' => 'Validasi dokumen berdasarkan ketentuan', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z'],
            ];
        @endphp

        @foreach($types as $key => $type)
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0] flex items-center justify-center text-[#c99a3e]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $type['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#071833]">{{ $type['label'] }}</h3>
                            <p class="text-xs text-[#667085]">{{ $type['desc'] }}</p>
                        </div>
                    </div>
                </x-slot>

                <div>
                    @if(isset($summaries[$key]))
                        @php $summary = $summaries[$key]; @endphp
                        <div class="text-sm text-[#667085] leading-relaxed line-clamp-4">{{ Str::limit($summary->summary, 300) }}</div>
                        <div class="mt-3 flex items-center gap-2 text-xs text-[#667085]">
                            <span class="px-2 py-0.5 rounded-full bg-[#f6f8fb] text-[10px] font-semibold uppercase">{{ $summary->provider_used }}</span>
                            <span>{{ $summary->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('ai-summaries.show', [$document, $summary]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                Lihat
                            </a>
                            <form method="POST" action="{{ route('ai-summaries.generate', $document) }}" class="inline">
                                @csrf
                                <input type="hidden" name="type" value="{{ $key }}">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-white bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                                    Generate Ulang
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-sm text-[#667085]">Belum ada summary untuk {{ $type['label'] }}.</p>
                        <div class="mt-4">
                            <form method="POST" action="{{ route('ai-summaries.generate', $document) }}" class="inline">
                                @csrf
                                <input type="hidden" name="type" value="{{ $key }}">
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-white bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Generate {{ $type['label'] }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </x-card>
        @endforeach
    </div>
@endsection
