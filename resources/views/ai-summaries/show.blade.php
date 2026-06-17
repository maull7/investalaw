@extends('layouts.app')

@section('title', 'AI Summary - ' . ucfirst($summary->type))
@section('header', 'AI Summary - ' . ucfirst($summary->type))

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        AI Summary · {{ ucfirst($summary->type) }}
                    </span>
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-white/10 text-white/80 uppercase tracking-wider">{{ $summary->provider_used }}</span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('ai-summaries.check-prompt', [$document, $summary]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                    Check AI Prompt
                </a>
                <a href="{{ route('ai-summaries.index', $document) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back
                </a>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#071833]">Summary Output</h3>
                        <span class="text-xs text-[#667085]">Generated {{ $summary->created_at->diffForHumans() }}</span>
                    </div>
                </x-slot>
                <div class="prose prose-sm max-w-none text-[#071833] leading-relaxed whitespace-pre-wrap">{{ $summary->summary }}</div>
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
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833] capitalize">{{ $summary->type }}</dd>
                    </div>
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
                </dl>
            </x-card>

            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Actions</h3>
                </x-slot>
                <div class="space-y-2.5">
                    <a href="{{ route('ai-summaries.check-prompt', [$document, $summary]) }}" class="inline-flex items-center gap-2 w-full px-4 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition justify-start">
                        <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        Check AI Prompt
                    </a>
                    <form method="POST" action="{{ route('ai-summaries.generate', $document) }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $summary->type }}">
                        <button type="submit" class="inline-flex items-center gap-2 w-full px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition justify-start">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                            Generate Ulang
                        </button>
                    </form>
                </div>
            </x-card>
        </aside>
    </div>
@endsection
