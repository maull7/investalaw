@extends('layouts.app')

@section('title', 'Reviews')
@section('header', 'Reviews')

@section('content')
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Compliance Workspace</p>
            <h2 class="mt-2 text-3xl font-bold text-[#071833] tracking-tight">Reviews</h2>
            <p class="mt-1.5 text-sm text-[#667085]">Track every compliance review performed on submitted documents.</p>
        </div>
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="p-5 sm:p-6 border-b border-[#e7eaf0]">
            <form method="GET" action="{{ route('reviews.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-9">
                    <select name="review_document_id" class="select-premium">
                        <option value="">All Documents</option>
                        @foreach($documents as $doc)
                            <option value="{{ $doc->id }}" {{ request('review_document_id') == $doc->id ? 'selected' : '' }}>{{ $doc->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <x-button type="submit" variant="secondary" class="w-full">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                        Apply Filter
                    </x-button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Reviewer</th>
                        <th>Doc Status</th>
                        <th>Findings</th>
                        <th>Completion</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>
                                <a href="{{ route('review-documents.show', $review->reviewDocument) }}" class="flex items-center gap-3 group">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#f6f8fb] to-white ring-1 ring-[#e7eaf0] flex items-center justify-center text-[#c99a3e]">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </div>
                                    <span class="font-semibold text-[#071833] group-hover:text-[#c99a3e] transition truncate max-w-xs">{{ $review->reviewDocument->title }}</span>
                                </a>
                            </td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] text-white text-[11px] font-bold flex items-center justify-center">{{ strtoupper(substr($review->reviewer->name, 0, 1)) }}</div>
                                    <span class="text-sm">{{ $review->reviewer->name }}</span>
                                </div>
                            </td>
                            <td><x-badge :color="$review->reviewDocument->status->color()">{{ $review->reviewDocument->status->label() }}</x-badge></td>
                            <td>
                                <span class="inline-flex items-center justify-center min-w-[28px] px-2.5 h-7 rounded-full bg-[#f6f8fb] ring-1 ring-[#e7eaf0] text-xs font-bold text-[#071833]">
                                    {{ $review->findings->count() }}
                                </span>
                            </td>
                            <td>
                                @if($review->isCompleted())
                                    <x-badge color="green">Completed</x-badge>
                                @else
                                    <x-badge color="yellow">In Progress</x-badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('reviews.show', $review) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition" title="View">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </a>
                                    <a href="{{ route('reports.show', $review) }}" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#071833] bg-gradient-to-br from-[#c99a3e]/15 to-[#e6c06a]/15 ring-1 ring-[#c99a3e]/30 hover:from-[#c99a3e]/25 hover:to-[#e6c06a]/25 transition" title="Report">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                                        Report
                                    </a>
                                    @can('update', $review)
                                        @unless($review->isCompleted())
                                            <a href="{{ route('reviews.edit', $review) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#c99a3e] transition" title="Edit">
                                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                                            </a>
                                        @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-[#667085]">
                                    <div class="w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center">
                                        <svg class="w-8 h-8 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                    <p class="text-base font-bold text-[#071833]">No reviews yet</p>
                                    <p class="text-sm">Reviews will appear here as submissions are processed.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="p-5 border-t border-[#e7eaf0]">
                {{ $reviews->links() }}
            </div>
        @endif
    </x-card>
@endsection
