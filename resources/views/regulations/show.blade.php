@extends('layouts.app')

@section('title', $regulation->regulation_number)
@section('header', $regulation->regulation_number)

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/18 blur-3xl"></div>

        <div class="relative grid lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Regulasi
                    </span>
                    @if($regulation->type)
                        <x-badge :color="$regulation->type->levelBadgeColor()">{{ $regulation->type->name }} — Level {{ $regulation->type->level }}</x-badge>
                    @endif
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight">{{ $regulation->title }}</h2>
                <p class="mt-2 text-white/70 text-sm">{{ $regulation->regulation_number }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    <p class="text-[11px] font-semibold tracking-[0.16em] uppercase text-white/55">Tahun</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ $regulation->year }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    <p class="text-[11px] font-semibold tracking-[0.16em] uppercase text-white/55">Dokumen</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ $regulation->documents->count() }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Metadata --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-[#071833]">Informasi Regulasi</h3>
                </x-slot>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Nomor Regulasi</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $regulation->regulation_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Tahun</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $regulation->year }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Tanggal Berlaku</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $regulation->effective_date?->format('d F Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Jenis Regulasi</dt>
                        <dd class="mt-1.5">
                            @if($regulation->type)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-[#071833]">{{ $regulation->type->name }}</span>
                                    <x-badge :color="$regulation->type->levelBadgeColor()">Level {{ $regulation->type->level }}</x-badge>
                                </div>
                            @else
                                <span class="text-sm text-[#667085]">-</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Category</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $regulation->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Dibuat</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $regulation->created_at->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[#667085]">Terakhir Diperbarui</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-[#071833]">{{ $regulation->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </x-card>

            {{-- Sub Categories --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-lg font-bold text-[#071833]">Sub Category</h3>
                </x-slot>
                @if($regulation->subCategories->isEmpty())
                    <p class="text-sm text-[#667085]">Belum ada sub category yang dipilih.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($regulation->subCategories as $sub)
                            <x-badge :color="$sub->is_active ? 'gold' : 'gray'">{{ $sub->name }}</x-badge>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Related Regulations --}}
            <x-card :padding="false">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#071833]">Peraturan Terkait</h3>
                            <p class="text-xs text-[#667085] mt-0.5">Regulasi yang saling berkaitan</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $regulation->relatedRegulations->count() }} item</span>
                    </div>
                </x-slot>
                @if($regulation->relatedRegulations->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-sm text-[#667085]">Belum ada peraturan terkait.</p>
                    </div>
                @else
                    <ul class="divide-y divide-[#f0f3f8]">
                        @foreach($regulation->relatedRegulations as $related)
                            <li class="flex items-center gap-4 px-6 py-4 hover:bg-[#f6f8fb]/60 transition">
                                <div class="shrink-0 w-10 h-10 rounded-xl bg-[#f6f8fb] text-[#c99a3e] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.04a4.5 4.5 0 0 0-1.242-7.244l-4.5-4.5a4.5 4.5 0 0 0-6.364 6.364L4.34 8.598"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('regulations.show', $related) }}" class="text-sm font-semibold text-[#071833] hover:text-[#c99a3e] transition">{{ $related->regulation_number }}</a>
                                    <p class="text-xs text-[#667085] mt-0.5 line-clamp-1">{{ $related->title }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($related->type)
                                        <x-badge :color="$related->type->levelBadgeColor()">Lv{{ $related->type->level }}</x-badge>
                                    @endif
                                    <span class="text-xs font-semibold text-[#667085]">{{ $related->year }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            {{-- Documents --}}
            <x-card :padding="false">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#071833]">Dokumen Tambahan</h3>
                            <p class="text-xs text-[#667085] mt-0.5">Dokumen pendukung untuk regulasi ini</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $regulation->documents->count() }} file</span>
                    </div>
                </x-slot>
                @if($regulation->documents->isEmpty())
                    <div class="text-center py-10">
                        <div class="mx-auto w-14 h-14 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <p class="mt-3 text-sm font-bold text-[#071833]">Belum ada dokumen tambahan</p>
                        <p class="text-xs text-[#667085] mt-1">Upload dokumen pendukung melalui halaman edit.</p>
                    </div>
                @else
                    <ul class="divide-y divide-[#f0f3f8]">
                        @foreach($regulation->documents as $doc)
                            <li class="flex items-center gap-4 px-6 py-4 hover:bg-[#f6f8fb]/60 transition">
                                @php
                                    $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                                    $iconColor = match($ext) {
                                        'pdf' => 'bg-rose-50 text-rose-500',
                                        'docx', 'doc' => 'bg-blue-50 text-blue-500',
                                        'xlsx', 'xls' => 'bg-emerald-50 text-emerald-500',
                                        'pptx', 'ppt' => 'bg-orange-50 text-orange-500',
                                        default => 'bg-[#f6f8fb] text-[#667085]',
                                    };
                                @endphp
                                <div class="shrink-0 w-11 h-11 rounded-xl {{ $iconColor }} flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 3.5L18.5 8H14V3.5zM6 20V4h7v5h5v11H6z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-[#071833] truncate">{{ $doc->name }}</p>
                                    <p class="text-xs text-[#667085] mt-0.5">{{ $doc->document_type }} &middot; {{ strtoupper($ext) }}</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('regulations.documents.view', $doc) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        Preview
                                    </a>
                                    <a href="{{ route('regulations.documents.view', $doc) }}" download class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        <aside class="space-y-6">
            {{-- File Regulasi --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">File Regulasi</h3>
                </x-slot>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-[#f6f8fb]">
                    <div class="shrink-0 w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 3.5L18.5 8H14V3.5zM6 20V4h7v5h5v11H6z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-[#071833] truncate">File PDF</p>
                        <p class="text-xs text-[#667085]">Regulasi utama</p>
                    </div>
                </div>
                <a href="{{ route('regulations.show', $regulation) }}" class="mt-3 flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-[#f6f8fb] text-sm font-semibold text-[#071833] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Download PDF
                </a>
            </x-card>

            {{-- Actions --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Aksi</h3>
                </x-slot>
                <div class="space-y-2.5">
                    <x-button href="{{ route('regulations.edit', $regulation) }}" variant="outline" class="w-full justify-start">
                        <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                        Edit Regulasi
                    </x-button>
                    <form method="POST" action="{{ route('regulations.destroy', $regulation) }}" id="delete-regulation-form">
                        @csrf
                        @method('DELETE')
                        <x-button type="button" variant="danger" class="w-full justify-start" @click="$dispatch('open-modal-confirm-delete-regulation')">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            Hapus Regulasi
                        </x-button>
                    </form>
                    <x-button href="{{ route('regulations.index') }}" variant="ghost" class="w-full justify-start">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Kembali ke Daftar
                    </x-button>
                </div>
            </x-card>

            {{-- Hierarchy Info --}}
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Hierarki Regulasi</h3>
                </x-slot>
                @if($regulation->type)
                    <div class="space-y-2">
                        @for($i = 1; $i <= 5; $i++)
                            @php
                                $colors = [1 => 'red', 2 => 'orange', 3 => 'yellow', 4 => 'blue', 5 => 'green'];
                                $isActive = $regulation->type->level === $i;
                            @endphp
                            <div class="flex items-center gap-3 p-2 rounded-lg {{ $isActive ? 'bg-[#f6f8fb] ring-1 ring-[#c99a3e]/30' : '' }}">
                                <x-badge :color="$colors[$i]">Lv {{ $i }}</x-badge>
                                <span class="text-xs {{ $isActive ? 'font-bold text-[#071833]' : 'text-[#667085]' }}">
                                    {{ $i === 1 ? 'Tertinggi' : ($i === 5 ? 'Terendah' : '') }}
                                    @if($isActive)← Regulasi ini @endif
                                </span>
                            </div>
                        @endfor
                    </div>
                @endif
            </x-card>
        </aside>
    </div>

    <x-modal name="confirm-delete-regulation" title="Hapus Regulasi" maxWidth="md">
        <div class="flex items-start gap-4">
            <span class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-rose-50 text-rose-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#071833]">Hapus Regulasi</p>
                <p class="mt-1 text-sm text-[#667085] leading-relaxed">Apakah Anda yakin ingin menghapus regulasi ini beserta seluruh dokumen terkait? Aksi ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-button type="button" variant="outline" @click="$dispatch('close-modal-confirm-delete-regulation')">Batal</x-button>
            <x-button type="button" variant="danger" onclick="document.getElementById('delete-regulation-form').submit()">Hapus</x-button>
        </x-slot>
    </x-modal>
@endsection
