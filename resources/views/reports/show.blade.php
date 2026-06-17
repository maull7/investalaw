@extends('layouts.app')

@section('title', 'Review Report')
@section('header', 'Review Report')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-10">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]" style="background-image: linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px); background-size: 48px 48px;"></div>

        <div class="relative grid lg:grid-cols-5 gap-8 items-center">
            <div class="lg:col-span-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                    <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                    Compliance Review Report
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-2xl">An institutional-grade compliance assessment with detailed findings, recommendations, and a quantified compliance rate.</p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="{{ route('reports.pdf', $review) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition btn-gold-glow">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                        Export PDF
                    </a>
                    <a href="{{ route('reviews.show', $review) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                        Back to Review
                    </a>
                </div>
            </div>

            {{-- Compliance Rate ring --}}
            <div class="lg:col-span-2 rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-6">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold tracking-[0.16em] uppercase text-white/55">Compliance Rate</p>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 text-[10.5px] font-bold">
                        <span class="w-1 h-1 rounded-full bg-emerald-400"></span>
                        Verified
                    </span>
                </div>
                @php $offset = 251.2 * (1 - $summary['compliance_rate'] / 100); @endphp
                <div class="mt-4 flex items-center gap-5">
                    <div class="relative w-28 h-28">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="9"/>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="url(#repGoldGrad)" stroke-width="9" stroke-linecap="round" stroke-dasharray="251.2" stroke-dashoffset="{{ $offset }}"/>
                            <defs>
                                <linearGradient id="repGoldGrad" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#c99a3e"/>
                                    <stop offset="100%" stop-color="#e6c06a"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-bold text-white">{{ $summary['compliance_rate'] }}%</span>
                            <span class="text-[10px] uppercase tracking-wider text-white/55">Compliant</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs text-white/65"><span class="font-bold text-white">{{ $summary['compliant'] }}</span> of {{ $summary['total_regulations'] }} regulations</p>
                        <p class="text-xs text-white/65"><span class="font-bold text-emerald-300">{{ $summary['compliant'] }}</span> compliant</p>
                        <p class="text-xs text-white/65"><span class="font-bold text-amber-300">{{ $summary['partially_compliant'] }}</span> partial</p>
                        <p class="text-xs text-white/65"><span class="font-bold text-rose-300">{{ $summary['non_compliant'] }}</span> non-compliant</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Summary stats --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <x-stat-card title="Total Regulasi" :value="$summary['total_regulations']" color="navy" subtitle="Reviewed regulations">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Compliant" :value="$summary['compliant']" color="green" subtitle="Fully aligned">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Partially Compliant" :value="$summary['partially_compliant']" color="yellow" subtitle="Improvements needed">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Non-Compliant" :value="$summary['non_compliant']" color="red" subtitle="Critical gaps">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            </x-slot>
        </x-stat-card>
    </section>

    {{-- Compliance rate bar --}}
    <x-card class="mt-6">
        <x-slot name="header">
            <div>
                <h3 class="text-lg font-bold text-[#071833]">Compliance Distribution</h3>
                <p class="text-xs text-[#667085] mt-0.5">Visual breakdown of evaluated regulations</p>
            </div>
        </x-slot>
        @php
            $totalCat = max($summary['total_regulations'], 1);
            $compPct = round($summary['compliant'] / $totalCat * 100);
            $partPct = round($summary['partially_compliant'] / $totalCat * 100);
            $nonPct = round($summary['non_compliant'] / $totalCat * 100);
        @endphp
        <div class="space-y-5">
            <div class="flex h-3 rounded-full overflow-hidden bg-[#f6f8fb]">
                <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all" style="width: {{ $compPct }}%"></div>
                <div class="h-full bg-gradient-to-r from-amber-300 to-amber-500 transition-all" style="width: {{ $partPct }}%"></div>
                <div class="h-full bg-gradient-to-r from-rose-400 to-rose-600 transition-all" style="width: {{ $nonPct }}%"></div>
            </div>
            <div class="grid grid-cols-3 gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[#667085]">Compliant</span>
                    <span class="ml-auto font-bold text-[#071833]">{{ $compPct }}%</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-[#667085]">Partial</span>
                    <span class="ml-auto font-bold text-[#071833]">{{ $partPct }}%</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <span class="text-[#667085]">Non-Compliant</span>
                    <span class="ml-auto font-bold text-[#071833]">{{ $nonPct }}%</span>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Document & Reviewer --}}
    <x-card class="mt-6">
        <x-slot name="header">
            <h3 class="text-lg font-bold text-[#071833]">Document &amp; Reviewer</h3>
        </x-slot>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Document Title</dt>
                <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $document->title }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Reviewer</dt>
                <dd class="mt-1.5 flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] text-white text-[11px] font-bold flex items-center justify-center">{{ strtoupper(substr($reviewer->name, 0, 1)) }}</div>
                    <span class="text-sm font-semibold text-[#071833]">{{ $reviewer->name }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Document Status</dt>
                <dd class="mt-1.5"><x-badge :color="$document->status->color()">{{ $document->status->label() }}</x-badge></dd>
            </div>
            <div>
                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Review Date</dt>
                <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $review->created_at->format('d F Y') }}</dd>
            </div>
        </dl>
    </x-card>

    {{-- Detailed Findings --}}
    <x-card class="mt-6">
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-[#071833]">Detailed Findings</h3>
                    <p class="text-xs text-[#667085] mt-0.5">Per-regulation deep dive</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $findings->count() }} findings</span>
            </div>
        </x-slot>
        @if($findings->isEmpty())
            <p class="text-sm text-[#667085]">No findings recorded.</p>
        @else
            <div class="space-y-4">
                @foreach($findings as $finding)
                    <article class="rounded-2xl border border-[#e7eaf0] p-5 hover:border-[#c99a3e]/40 transition">
                        <header class="flex items-start justify-between gap-4 mb-3">
                            <div class="min-w-0">
                                @if($finding->regulation)
                                    <p class="text-sm font-bold text-[#071833]">{{ $finding->regulation->regulation_number }}</p>
                                    <p class="text-xs text-[#667085] mt-0.5 line-clamp-1">{{ $finding->regulation->title }}</p>
                                @elseif($finding->category)
                                    <p class="text-sm font-bold text-[#071833]">{{ $finding->category->name }}</p>
                                    @if($finding->category->description)
                                        <p class="text-xs text-[#667085] mt-0.5">{{ $finding->category->description }}</p>
                                    @endif
                                @endif
                            </div>
                            <x-badge :color="$finding->compliance_status->color()">{{ $finding->compliance_status->label() }}</x-badge>
                        </header>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            @if($finding->findings)
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#c99a3e]">Findings</p>
                                    <p class="mt-1 text-sm text-[#071833] leading-relaxed">{{ $finding->findings }}</p>
                                </div>
                            @endif
                            @if($finding->recommendations)
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#c99a3e]">Recommendations</p>
                                    <p class="mt-1 text-sm text-[#071833] leading-relaxed">{{ $finding->recommendations }}</p>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </x-card>
@endsection
