@extends('layouts.app')

@section('title', 'Category Details')
@section('header', $category->name)

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/18 blur-3xl"></div>

        <div class="relative grid lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                    <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                    Regulation Category
                </span>
                <h2 class="mt-4 text-3xl font-bold tracking-tight">{{ $category->name }}</h2>
                @if($category->description)
                    <p class="mt-3 text-white/70 leading-relaxed max-w-3xl">{{ $category->description }}</p>
                @endif
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-5">
                <p class="text-[11px] font-semibold tracking-[0.16em] uppercase text-white/55">Reference Files</p>
                <div class="mt-3 flex items-baseline gap-2">
                    <p class="text-4xl font-bold text-white">{{ $category->files->count() }}</p>
                    <span class="text-sm text-white/65">PDF document(s)</span>
                </div>
                <p class="text-xs text-white/55 mt-2">Curated regulatory references for compliance teams.</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-[#071833]">Category Information</h3>
                </x-slot>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Name</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $category->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Created</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $category->created_at->format('d F Y') }}</dd>
                    </div>
                </dl>
                @if($category->description)
                    <div class="mt-6 pt-6 border-t border-[#e7eaf0]">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Description</p>
                        <p class="mt-2 text-sm text-[#071833] leading-relaxed">{{ $category->description }}</p>
                    </div>
                @endif
            </x-card>

            <x-card :padding="false">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#071833]">Reference Files</h3>
                            <p class="text-xs text-[#667085] mt-0.5">PDF documents linked to this category</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $category->files->count() }} files</span>
                    </div>
                </x-slot>

                @if($category->files->isEmpty())
                    <div class="text-center py-14">
                        <div class="mx-auto w-14 h-14 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <p class="mt-3 text-sm font-bold text-[#071833]">No reference files yet</p>
                        <p class="text-xs text-[#667085] mt-1">Use the upload panel on the right to add PDF references.</p>
                    </div>
                @else
                    <ul class="divide-y divide-[#f0f3f8]">
                        @foreach($category->files as $file)
                            <li class="flex items-center gap-4 px-6 py-4 hover:bg-[#f6f8fb]/60 transition">
                                <div class="shrink-0 w-11 h-11 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 3.5L18.5 8H14V3.5zM6 20V4h7v5h5v11H6z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-[#071833] truncate">{{ $file->filename }}</p>
                                    <p class="text-xs text-[#667085] mt-0.5">Uploaded {{ $file->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('regulation-categories.view-file', $file) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6 9.75-9.75M15 3h6v6"/></svg>
                                        View
                                    </a>
                                    <form method="POST" action="{{ route('regulation-categories.delete-file', $file) }}" id="delete-file-form-{{ $file->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-rose-600 hover:bg-rose-50 transition" title="Delete file" onclick="window._deleteFileId={{ $file->id }}" @click="$dispatch('open-modal-confirm-delete-file')">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        <aside class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Upload Reference Files</h3>
                </x-slot>
                <form method="POST" action="{{ route('regulation-categories.upload-file', $category) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="files" class="block text-sm font-semibold text-[#071833] mb-2">Select PDF Files</label>
                        <input type="file" name="files[]" id="files" multiple accept=".pdf" required class="file-premium">
                        <p class="mt-1.5 text-xs text-[#667085]">You may select multiple PDFs at once.</p>
                        @error('files')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        @error('files.*')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <x-button type="submit" variant="primary" class="w-full">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Upload
                    </x-button>
                </form>
            </x-card>

            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Actions</h3>
                </x-slot>
                <div class="space-y-2.5">
                    <x-button href="{{ route('regulation-categories.edit', $category) }}" variant="outline" class="w-full justify-start">
                        <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                        Edit Category
                    </x-button>
                    <form method="POST" action="{{ route('regulation-categories.destroy', $category) }}" id="delete-category-form">
                        @csrf
                        @method('DELETE')
                        <x-button type="button" variant="danger" class="w-full justify-start" @click="$dispatch('open-modal-confirm-delete-category')">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            Delete Category
                        </x-button>
                    </form>
                    <x-button href="{{ route('regulation-categories.index') }}" variant="ghost" class="w-full justify-start">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Back to List
                    </x-button>
                </div>
            </x-card>
        </aside>
    </div>

    <x-modal name="confirm-delete-file" title="Delete File" maxWidth="md">
        <div class="flex items-start gap-4">
            <span class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-rose-50 text-rose-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#071833]">Delete File</p>
                <p class="mt-1 text-sm text-[#667085] leading-relaxed">Are you sure you want to delete this file?</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-button type="button" variant="outline" @click="$dispatch('close-modal-confirm-delete-file')">Cancel</x-button>
            <x-button type="button" variant="danger" onclick="document.getElementById('delete-file-form-' + window._deleteFileId).submit()">Delete</x-button>
        </x-slot>
    </x-modal>

    <x-modal name="confirm-delete-category" title="Delete Category" maxWidth="md">
        <div class="flex items-start gap-4">
            <span class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-rose-50 text-rose-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#071833]">Delete Category</p>
                <p class="mt-1 text-sm text-[#667085] leading-relaxed">Are you sure you want to delete this category and all its files? This action cannot be undone.</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-button type="button" variant="outline" @click="$dispatch('close-modal-confirm-delete-category')">Cancel</x-button>
            <x-button type="button" variant="danger" onclick="document.getElementById('delete-category-form').submit()">Delete</x-button>
        </x-slot>
    </x-modal>
@endsection
