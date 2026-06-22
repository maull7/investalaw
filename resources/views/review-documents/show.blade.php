@extends('layouts.app')

@section('title', 'Document Details')
@section('header', 'Document Details')

@section('content')
    {{-- Document hero --}}
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Document
                    </span>
                    <x-badge :color="$document->status->color()">{{ $document->status->label() }}</x-badge>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                @if($document->description)
                    <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">{{ $document->description }}</p>
                @endif
                <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-white/65">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-[#e6c06a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        Uploaded by <span class="font-semibold text-white">{{ $document->user->name }}</span>
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-[#e6c06a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        {{ $document->created_at->format('d F Y') }}
                    </span>
                    @if($document->submitted_at)
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-[#e6c06a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Submitted {{ $document->submitted_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('review-documents.view-file', $document) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6 9.75-9.75M15 3h6v6"/></svg>
                    Open File
                </a>
                <a href="{{ route('review-documents.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back
                </a>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Document Information --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-[#071833]">Document Information</h3>
                </x-slot>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Status</dt>
                        <dd class="mt-1.5"><x-badge :color="$document->status->color()">{{ $document->status->label() }}</x-badge></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Uploaded By</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $document->user->name }}</dd>
                    </div>
                    @if($document->submitted_at)
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Submitted At</dt>
                            <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $document->submitted_at->format('d F Y, H:i') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Created At</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $document->created_at->format('d F Y, H:i') }}</dd>
                    </div>
                </dl>

                @if($document->description)
                    <div class="mt-6 pt-6 border-t border-[#e7eaf0]">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Description</p>
                        <p class="mt-2 text-sm text-[#071833] leading-relaxed">{{ $document->description }}</p>
                    </div>
                @endif
            </x-card>

            {{-- Regulations --}}
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#071833]">Regulasi Terkait</h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $document->regulations->count() }}</span>
                    </div>
                </x-slot>
                @if($document->regulations->isEmpty())
                    <p class="text-sm text-[#667085]">Belum ada regulasi yang dipilih.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($document->regulations as $regulation)
                            <li class="flex items-start gap-3 p-4 rounded-2xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0]">
                                <div class="w-9 h-9 rounded-xl bg-white ring-1 ring-[#e7eaf0] flex items-center justify-center text-[#c99a3e] shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('regulations.show', $regulation) }}" class="text-sm font-semibold text-[#071833] hover:text-[#c99a3e] transition">{{ $regulation->regulation_number }}</a>
                                    <p class="text-xs text-[#667085] mt-0.5 line-clamp-1">{{ $regulation->title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($regulation->type)
                                            <x-badge :color="$regulation->type->levelBadgeColor()">{{ $regulation->type->name }} Lv{{ $regulation->type->level }}</x-badge>
                                        @endif
                                        <span class="text-[10px] text-[#667085]">{{ $regulation->year }}</span>
                                        @if($regulation->category)
                                            <span class="text-[10px] text-[#667085]">&middot; {{ $regulation->category->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            {{-- Review --}}
            @if($document->review)
                <x-card>
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-[#071833]">Review</h3>
                            <x-button href="{{ route('reviews.show', $document->review) }}" variant="outline" size="sm">View Full Review</x-button>
                        </div>
                    </x-slot>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Reviewer</dt>
                            <dd class="mt-1.5 flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] text-white text-xs font-bold flex items-center justify-center">{{ strtoupper(substr($document->review->reviewer->name, 0, 1)) }}</div>
                                <span class="text-sm font-semibold text-[#071833]">{{ $document->review->reviewer->name }}</span>
                            </dd>
                        </div>
                        @if($document->review->summary)
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Summary</dt>
                                <dd class="mt-1.5 text-sm text-[#071833] leading-relaxed">{{ $document->review->summary }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>
            @endif
        </div>

        <aside class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Actions</h3>
                </x-slot>
                <div class="space-y-2.5">
                    @can('update', $document)
                        <x-button href="{{ route('review-documents.edit', $document) }}" variant="outline" class="w-full justify-start">
                            <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                            Edit Document
                        </x-button>
                    @endcan
                    @can('submit', $document)
                        <form method="POST" action="{{ route('review-documents.submit', $document) }}" id="submit-form">
                            @csrf
                            <x-button type="button" variant="primary" class="w-full justify-start" @click="$dispatch('open-modal-confirm-submit')">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                Submit for Review
                            </x-button>
                        </form>
                    @endcan
                    @can('review', $document)
                        <x-button href="{{ route('reviews.create', $document) }}" variant="secondary" class="w-full justify-start">
                            <svg class="w-4 h-4 text-[#e6c06a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Start Review
                        </x-button>
                    @endcan
                    @can('delete', $document)
                        <form method="POST" action="{{ route('review-documents.destroy', $document) }}" id="delete-form">
                            @csrf
                            @method('DELETE')
                            <x-button type="button" variant="danger" class="w-full justify-start" @click="$dispatch('open-modal-confirm-delete')">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                Delete Document
                            </x-button>
                        </form>
                    @endcan
                    <a href="{{ route('ai-summaries.index', $document) }}" class="inline-flex items-center gap-2 w-full px-4 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition justify-start">
                        <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z"/></svg>
                        AI Summary
                    </a>
                    <a href="{{ route('partitions.index', $document) }}" class="inline-flex items-center gap-2 w-full px-4 py-2.5 rounded-full text-sm font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition justify-start">
                        <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                        Partisi Dokumen
                    </a>
                </div>
            </x-card>

            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Timeline</h3>
                </x-slot>
                <ol class="relative border-l border-[#e7eaf0] ml-3 space-y-5">
                    <li class="pl-5 relative">
                        <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] ring-4 ring-white"></span>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#667085]">Uploaded</p>
                        <p class="text-sm font-semibold text-[#071833] mt-0.5">{{ $document->created_at->format('d M Y · H:i') }}</p>
                    </li>
                    @if($document->submitted_at)
                        <li class="pl-5 relative">
                            <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full bg-sky-500 ring-4 ring-white"></span>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#667085]">Submitted</p>
                            <p class="text-sm font-semibold text-[#071833] mt-0.5">{{ $document->submitted_at->format('d M Y · H:i') }}</p>
                        </li>
                    @endif
                    @if($document->review && $document->review->completed_at)
                        <li class="pl-5 relative">
                            <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#667085]">Review Completed</p>
                            <p class="text-sm font-semibold text-[#071833] mt-0.5">{{ $document->review->completed_at->format('d M Y · H:i') }}</p>
                        </li>
                    @endif
                </ol>
            </x-card>
        </aside>
    </div>

    <x-modal name="confirm-submit" title="Submit for Review" maxWidth="md">
        <div class="flex items-start gap-4">
            <span class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-amber-50 text-amber-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#071833]">Submit for Review</p>
                <p class="mt-1 text-sm text-[#667085] leading-relaxed">Are you sure you want to submit this document for review? Once submitted, you won't be able to edit it.</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-button type="button" variant="outline" @click="$dispatch('close-modal-confirm-submit')">Cancel</x-button>
            <x-button type="button" variant="primary" onclick="document.getElementById('submit-form').submit()">Submit</x-button>
        </x-slot>
    </x-modal>

    <x-modal name="confirm-delete" title="Delete Document" maxWidth="md">
        <div class="flex items-start gap-4">
            <span class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-rose-50 text-rose-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#071833]">Delete Document</p>
                <p class="mt-1 text-sm text-[#667085] leading-relaxed">Are you sure you want to delete this document permanently? This action cannot be undone.</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-button type="button" variant="outline" @click="$dispatch('close-modal-confirm-delete')">Cancel</x-button>
            <x-button type="button" variant="danger" onclick="document.getElementById('delete-form').submit()">Delete</x-button>
        </x-slot>
    </x-modal>
@endsection
