@extends('layouts.app')

@section('title', 'Edit Document')
@section('header', 'Edit Document')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Editing</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">{{ $reviewDocument->title }}</h3>
                        <p class="text-sm text-[#667085] mt-1">Refine document details or replace the attached file.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('review-documents.update', $reviewDocument) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="title" class="block text-sm font-semibold text-[#071833] mb-2">Document Title <span class="text-[#c99a3e]">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title', $reviewDocument->title) }}" required class="input-premium">
                        @error('title')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-[#071833] mb-2">Description</label>
                        <textarea name="description" id="description" rows="4" class="input-premium input-textarea">{{ old('description', $reviewDocument->description) }}</textarea>
                        @error('description')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="file" class="block text-sm font-semibold text-[#071833] mb-2">Document File</label>
                        <div class="mb-2 flex items-center gap-3 p-3 rounded-xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0]">
                            <div class="w-9 h-9 rounded-lg bg-white ring-1 ring-[#e7eaf0] flex items-center justify-center text-[#c99a3e]">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] uppercase tracking-wider text-[#667085] font-semibold">Current file</p>
                                <p class="text-sm font-semibold text-[#071833] truncate">{{ basename($reviewDocument->file_path) }}</p>
                            </div>
                        </div>
                        <input type="file" name="file" id="file" accept=".pdf,.doc,.docx" class="file-premium">
                        <p class="mt-1.5 text-xs text-[#667085]">Leave empty to keep the current file. Accepted: PDF, DOC, DOCX.</p>
                        @error('file')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#071833] mb-2">Regulation Categories</label>
                        <div class="rounded-2xl border border-[#e7eaf0] bg-[#f6f8fb]/40 p-4 max-h-72 overflow-y-auto space-y-2">
                            @php $selectedIds = old('category_ids', $reviewDocument->categories->pluck('id')->toArray()); @endphp
                            @foreach($categories as $category)
                                <label class="flex items-start gap-3 p-3 rounded-xl bg-white ring-1 ring-[#e7eaf0] hover:ring-[#c99a3e]/40 cursor-pointer transition">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ in_array($category->id, $selectedIds) ? 'checked' : '' }} class="checkbox-premium mt-0.5">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-[#071833]">{{ $category->name }}</p>
                                        @if($category->description)
                                            <p class="text-xs text-[#667085] mt-0.5">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('category_ids')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Update Document</x-button>
                        <x-button href="{{ route('review-documents.show', $reviewDocument) }}" variant="outline" size="lg">Cancel</x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <aside>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Document Status</h3>
                </x-slot>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#667085]">Current Status</span>
                        <x-badge :color="$reviewDocument->status->color()">{{ $reviewDocument->status->label() }}</x-badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#667085]">Last Updated</span>
                        <span class="text-xs font-semibold text-[#071833]">{{ $reviewDocument->updated_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#667085]">Uploaded By</span>
                        <span class="text-xs font-semibold text-[#071833]">{{ $reviewDocument->user->name }}</span>
                    </div>
                </div>
            </x-card>
        </aside>
    </div>
@endsection
