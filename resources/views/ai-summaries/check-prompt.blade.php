@extends('layouts.app')

@section('title', 'Check AI Prompt')
@section('header', 'Check AI Prompt')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        AI Prompt · {{ ucfirst($summary->type) }}
                    </span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">Prompt yang digunakan saat generate AI Summary.</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('ai-summaries.show', [$document, $summary]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back to Summary
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6">
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[#071833]">Prompt Text</h3>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085] capitalize">{{ $summary->type }}</span>
                </div>
            </x-slot>
            <div class="bg-[#f6f8fb] rounded-2xl p-5 ring-1 ring-[#e7eaf0]">
                <pre class="text-sm text-[#071833] leading-relaxed whitespace-pre-wrap font-mono">{{ $summary->prompt_text }}</pre>
            </div>
            <div class="mt-4 flex items-center gap-4 text-xs text-[#667085]">
                <span>Provider: <span class="font-semibold text-[#071833]">{{ $summary->provider_used }}</span></span>
                @if($summary->model_used)
                    <span>Model: <span class="font-semibold text-[#071833]">{{ $summary->model_used }}</span></span>
                @endif
                <span>Generated: <span class="font-semibold text-[#071833]">{{ $summary->created_at->format('d M Y · H:i') }}</span></span>
            </div>
        </x-card>
    </div>
@endsection
