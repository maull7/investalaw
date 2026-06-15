@extends('layouts.app')

@section('title', 'Review Details')
@section('header', 'Review Details')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/18 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Compliance Review
                    </span>
                    @if($review->isCompleted())
                        <x-badge color="green">Completed</x-badge>
                    @else
                        <x-badge color="yellow">In Progress</x-badge>
                    @endif
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $review->reviewDocument->title }}</h2>
                <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-white/65">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-[#e6c06a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        Reviewer <span class="font-semibold text-white">{{ $review->reviewer->name }}</span>
                    </span>
                    @if($review->completed_at)
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-[#e6c06a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Completed {{ $review->completed_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('reports.show', $review) }}" class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                View Report
            </a>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Review info --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-[#071833]">Review Information</h3>
                </x-slot>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Document</dt>
                        <dd class="mt-1.5"><a href="{{ route('review-documents.show', $review->reviewDocument) }}" class="text-sm font-semibold text-[#071833] hover:text-[#c99a3e] transition">{{ $review->reviewDocument->title }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Document Status</dt>
                        <dd class="mt-1.5"><x-badge :color="$review->reviewDocument->status->color()">{{ $review->reviewDocument->status->label() }}</x-badge></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Reviewer</dt>
                        <dd class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] text-white text-[11px] font-bold flex items-center justify-center">{{ strtoupper(substr($review->reviewer->name, 0, 1)) }}</div>
                            <span class="text-sm font-semibold text-[#071833]">{{ $review->reviewer->name }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Review Status</dt>
                        <dd class="mt-1.5">
                            @if($review->isCompleted())
                                <x-badge color="green">Completed</x-badge>
                            @else
                                <x-badge color="yellow">In Progress</x-badge>
                            @endif
                        </dd>
                    </div>
                    @if($review->completed_at)
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Completed At</dt>
                            <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $review->completed_at->format('d F Y · H:i') }}</dd>
                        </div>
                    @endif
                </dl>

                @if($review->summary)
                    <div class="mt-6 pt-6 border-t border-[#e7eaf0]">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Summary</p>
                        <p class="mt-2 text-sm text-[#071833] leading-relaxed">{{ $review->summary }}</p>
                    </div>
                @endif
                @if($review->notes)
                    <div class="mt-5 p-4 rounded-2xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0]">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Internal Notes</p>
                        <p class="mt-1.5 text-sm text-[#071833] leading-relaxed">{{ $review->notes }}</p>
                    </div>
                @endif
            </x-card>

            {{-- Findings --}}
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#071833]">Findings</h3>
                            <p class="text-xs text-[#667085] mt-0.5">Per-regulation compliance assessments</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $review->findings->count() }} findings</span>
                    </div>
                </x-slot>

                @if($review->findings->isEmpty())
                    <p class="text-sm text-[#667085]">No findings recorded.</p>
                @else
                    <div class="space-y-4">
                        @foreach($review->findings as $finding)
                            <article class="rounded-2xl border border-[#e7eaf0] p-5 hover:border-[#c99a3e]/40 transition">
                                <header class="flex items-start justify-between gap-4 mb-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[#071833]">{{ $finding->category->name }}</p>
                                        @if($finding->category->description)
                                            <p class="text-xs text-[#667085] mt-0.5">{{ $finding->category->description }}</p>
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
        </div>

        {{-- Actions sidebar --}}
        <aside>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Actions</h3>
                </x-slot>
                <div class="space-y-3">
                    @can('update', $review)
                        @unless($review->isCompleted())
                            <x-button href="{{ route('reviews.edit', $review) }}" variant="outline" class="w-full justify-start">
                                <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                                Edit Review
                            </x-button>

                            <form method="POST" action="{{ route('reviews.complete', $review) }}" id="complete-form">
                                @csrf
                                <x-button type="button" variant="success" class="w-full justify-start" @click="$dispatch('open-modal-confirm-complete')">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    Complete &amp; Approve
                                </x-button>
                            </form>

                            <div class="pt-3 mt-3 border-t border-[#e7eaf0] space-y-3">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Reject / Revise</p>

                                <form method="POST" action="{{ route('reviews.request-revision', $review) }}" class="space-y-2">
                                    @csrf
                                    <textarea name="notes" rows="3" placeholder="Revision notes…" required class="input-premium input-textarea text-sm"></textarea>
                                    <x-button type="submit" variant="warning" size="sm" class="w-full">Request Revision</x-button>
                                </form>

                                <form method="POST" action="{{ route('reviews.reject', $review) }}" class="space-y-2">
                                    @csrf
                                    <textarea name="notes" rows="3" placeholder="Rejection notes…" required class="input-premium input-textarea text-sm"></textarea>
                                    <x-button type="submit" variant="danger" size="sm" class="w-full">Reject Document</x-button>
                                </form>
                            </div>
                        @endunless
                    @endcan

                    <x-button href="{{ route('reports.show', $review) }}" variant="primary" class="w-full justify-start">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        View Report
                    </x-button>
                    <x-button href="{{ route('reviews.index') }}" variant="ghost" class="w-full justify-start">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Back to List
                    </x-button>
                </div>
            </x-card>
        </aside>
    </div>

    <x-modal name="confirm-complete" title="Complete Review" maxWidth="md">
        <div class="flex items-start gap-4">
            <span class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-emerald-50 text-emerald-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#071833]">Complete This Review</p>
                <p class="mt-1 text-sm text-[#667085] leading-relaxed">Are you sure you want to complete this review and approve the document? This action cannot be undone.</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-button type="button" variant="outline" @click="$dispatch('close-modal-confirm-complete')">Cancel</x-button>
            <x-button type="button" variant="success" onclick="document.getElementById('complete-form').submit()">Complete &amp; Approve</x-button>
        </x-slot>
    </x-modal>
@endsection
