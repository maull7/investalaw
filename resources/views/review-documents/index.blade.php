@extends('layouts.app')

@section('title', 'Review Documents')
@section('header', 'Review Documents')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Compliance Workspace</p>
            <h2 class="mt-2 text-3xl font-bold text-[#071833] tracking-tight">Review Documents</h2>
            <p class="mt-1.5 text-sm text-[#667085]">Manage every uploaded document, monitor review status, and orchestrate compliance.</p>
        </div>
        <x-button href="{{ route('review-documents.create') }}" variant="primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Upload Document
        </x-button>
    </div>

    <x-card :padding="false" class="mt-6">
        {{-- Filter --}}
        <div class="p-5 sm:p-6 border-b border-[#e7eaf0]">
            <form method="GET" action="{{ route('review-documents.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-7">
                    <label class="relative block">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#667085]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title, description…" class="input-premium pl-11">
                    </label>
                </div>
                <div class="md:col-span-3">
                    <select name="status" class="select-premium">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <x-button type="submit" variant="secondary" class="w-full">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                        Apply Filter
                    </x-button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Uploaded By</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr>
                            <td>
                                <a href="{{ route('review-documents.show', $document) }}" class="flex items-center gap-3 group">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#f6f8fb] to-white ring-1 ring-[#e7eaf0] flex items-center justify-center text-[#c99a3e] shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-[#071833] group-hover:text-[#c99a3e] transition truncate max-w-xs">{{ $document->title }}</p>
                                        @if($document->description)
                                            <p class="text-xs text-[#667085] truncate max-w-xs">{{ Str::limit($document->description, 60) }}</p>
                                        @endif
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] text-white text-[11px] font-bold flex items-center justify-center">{{ strtoupper(substr($document->user->name, 0, 1)) }}</div>
                                    <span class="text-sm">{{ $document->user->name }}</span>
                                </div>
                            </td>
                            <td><x-badge :color="$document->status->color()">{{ $document->status->label() }}</x-badge></td>
                            <td class="text-[#667085]">{{ $document->submitted_at ? $document->submitted_at->format('d M Y') : '—' }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('review-documents.show', $document) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition" title="View">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </a>
                                    <a href="{{ route('review-documents.view-file', $document) }}" target="_blank" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition" title="Open file">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6 9.75-9.75M15 3h6v6"/></svg>
                                    </a>
                                    @can('update', $document)
                                        <a href="{{ route('review-documents.edit', $document) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#c99a3e] transition" title="Edit">
                                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                                        </a>
                                    @endcan
                                    @can('review', $document)
                                        <a href="{{ route('reviews.create', $document) }}" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#071833] bg-gradient-to-br from-[#c99a3e]/15 to-[#e6c06a]/15 ring-1 ring-[#c99a3e]/30 hover:from-[#c99a3e]/25 hover:to-[#e6c06a]/25 transition" title="Start review">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            Review
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-[#667085]">
                                    <div class="w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center">
                                        <svg class="w-8 h-8 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </div>
                                    <p class="text-base font-bold text-[#071833]">No documents found</p>
                                    <p class="text-sm">Try adjusting your filter or upload a new document.</p>
                                    <x-button href="{{ route('review-documents.create') }}" variant="primary" size="sm" class="mt-2">Upload Document</x-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="p-5 border-t border-[#e7eaf0]">
                {{ $documents->links() }}
            </div>
        @endif
    </x-card>
@endsection
