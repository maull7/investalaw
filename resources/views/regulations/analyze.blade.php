@extends('layouts.app')

@section('title', 'Analisis Regulasi — ' . $regulation->regulation_number)
@section('header', 'Analisis Regulasi')

@section('content')
    @php $relatedData = $analysis->related_data ?? []; @endphp
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/18 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Analisis Regulasi
                    </span>
                    @if($regulation->type)
                        <x-badge :color="$regulation->type->levelBadgeColor()">{{ $regulation->type->name }}</x-badge>
                    @endif
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight">{{ $regulation->title }}</h2>
                <p class="mt-2 text-white/70 text-sm">{{ $regulation->regulation_number }} ({{ $regulation->year }})</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('regulations.show', $regulation) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Kembali ke Detail
                </a>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 space-y-6">
            @if($regulation->isParsed())
                <x-card id="hasil-parse">
                    <x-slot name="header">
                        <h3 class="text-lg font-bold text-[#071833]">Hasil Parse Regulasi</h3>
                    </x-slot>
                    <div class="text-xs text-[#071833] leading-relaxed bg-[#f6f8fb] rounded-xl p-4 max-h-96 overflow-y-auto">
                        @formatText($regulation->parsed_text)
                    </div>
                    @php $stats = $regulation->parse_stats; @endphp
                    @if($stats)
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-[#f6f8fb] p-3 text-center">
                                <p class="text-lg font-bold text-[#071833]">{{ $stats['total_pages'] ?? '-' }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Halaman</p>
                            </div>
                            <div class="rounded-xl bg-[#f6f8fb] p-3 text-center">
                                <p class="text-lg font-bold text-[#071833]">{{ number_format($stats['char_total'] ?? 0) }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Karakter</p>
                            </div>
                            <div class="rounded-xl bg-[#f6f8fb] p-3 text-center">
                                <p class="text-lg font-bold text-[#071833]">{{ ($stats['percent_parsed'] ?? 0) . '%' }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Terdeteksi</p>
                            </div>
                        </div>
                    @endif
                </x-card>
            @else
                <x-card>
                    <x-slot name="header">
                        <h3 class="text-lg font-bold text-[#071833]">Hasil Parse Regulasi</h3>
                    </x-slot>
                    <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200 text-center">
                        <p class="text-sm font-semibold text-amber-800">Regulasi Belum Diparse</p>
                        <p class="text-xs text-amber-700 mt-1">Lakukan parser PDF terlebih dahulu untuk melihat teks regulasi.</p>
                    </div>
                </x-card>
            @endif

            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#071833]">Analisis Perbandingan</h3>
                            <p class="text-xs text-[#667085] mt-0.5">Data dari database &mdash; hasil analisis dan ekstraksi AI dari teks regulasi</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($analysis->metadata['ai_generated'] ?? false)
                                <span class="px-2 py-0.5 rounded-full bg-purple-100 text-[10px] font-bold text-purple-700">AI</span>
                            @endif
                            <form method="POST" action="{{ route('regulations.reanalyze', $regulation) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-[#667085] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                                    Regenerate
                                </button>
                            </form>
                        </div>
                    </div>
                </x-slot>

                <div class="space-y-5">
                    @php
                        $relatedData = $analysis->related_data ?? [];
                        $pasalList = $analysis->pasal;
                        $references = $analysis->references;
                    @endphp

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-[#071833]">Ringkasan Regulasi Terkait</h4>
                            <span class="text-xs text-[#667085]">{{ count($relatedData['related'] ?? []) }} item</span>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-[#f6f8fb] p-3 text-center">
                                <p class="text-2xl font-bold text-[#071833]">{{ count($relatedData['related'] ?? []) }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Total Terkait</p>
                            </div>
                            <div class="rounded-xl bg-amber-50 p-3 text-center">
                                <p class="text-2xl font-bold text-amber-700">{{ count($relatedData['amendments'] ?? []) }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Perubahan</p>
                            </div>
                            <div class="rounded-xl bg-rose-50 p-3 text-center">
                                <p class="text-2xl font-bold text-rose-700">{{ count($relatedData['revocations'] ?? []) }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Pencabutan</p>
                            </div>
                        </div>
                    </div>

                    @if($references->isNotEmpty())
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.04a4.5 4.5 0 0 0-1.242-7.244l-4.5-4.5a4.5 4.5 0 0 0-6.364 6.364L4.34 8.598"/></svg>
                                <h4 class="text-sm font-bold text-[#071833]">Referensi Regulasi Lain (dari teks)</h4>
                                <span class="px-2 py-0.5 rounded-full bg-[#f6f8fb] text-[10px] font-bold text-[#667085]">{{ $references->count() }}</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($references as $ref)
                                    <div class="flex items-start gap-3 p-3 rounded-xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0]">
                                        <div class="shrink-0 w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[10px] font-bold text-[#667085] ring-1 ring-[#e7eaf0]">
                                            {{ $loop->iteration }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-[#071833]">{{ $ref->name }}</p>
                                            <p class="text-xs text-[#667085] mt-0.5">
                                                <span class="font-semibold">Nomor:</span> {{ $ref->number }}
                                                @if($ref->year) · <span class="font-semibold">Tahun:</span> {{ $ref->year }} @endif
                                            </p>
                                        </div>
                                        <div class="shrink-0">
                                            @php
                                                $relBadge = match($ref->relationship) {
                                                    'diubah' => ['bg-amber-100 text-amber-700', 'Diubah'],
                                                    'dicabut' => ['bg-rose-100 text-rose-700', 'Dicabut'],
                                                    'dirujuk' => ['bg-blue-100 text-blue-700', 'Dirujuk'],
                                                    default => ['bg-gray-100 text-gray-600', $ref->relationship],
                                                };
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $relBadge[0] }}">{{ $relBadge[1] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($pasalList->isNotEmpty())
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/></svg>
                                <h4 class="text-sm font-bold text-[#071833]">Struktur Pasal (dari teks)</h4>
                                <span class="px-2 py-0.5 rounded-full bg-[#f6f8fb] text-[10px] font-bold text-[#667085]">{{ $pasalList->count() }}</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($pasalList as $pasal)
                                    @php
                                        $typeBadge = match($pasal->type) {
                                            'baru' => ['bg-emerald-100 text-emerald-700', 'Baru'],
                                            'diubah' => ['bg-amber-100 text-amber-700', 'Diubah'],
                                            'dicabut' => ['bg-rose-100 text-rose-700', 'Dicabut'],
                                            'sisipan' => ['bg-blue-100 text-blue-700', 'Sisipan'],
                                            default => ['bg-gray-100 text-gray-600', $pasal->type],
                                        };
                                    @endphp
                                    <div class="rounded-xl border border-[#e7eaf0] overflow-hidden">
                                        <div class="flex items-center justify-between px-4 py-2.5 bg-[#fafbfc] border-b border-[#e7eaf0]">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-[#071833]">{{ $pasal->pasal }}</span>
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $typeBadge[0] }}">{{ $typeBadge[1] }}</span>
                                            </div>
                                        </div>
                                        @if($pasal->content)
                                            <div class="px-4 py-2.5">
                                                <p class="text-xs text-[#071833] leading-relaxed">{{ $pasal->content }}</p>
                                            </div>
                                        @endif
                                        @if($pasal->changes)
                                            <div class="px-4 py-2 bg-amber-50 border-t border-[#e7eaf0]">
                                                <p class="text-[11px] font-semibold text-amber-800">Perubahan: {{ $pasal->changes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($analysis->changes_summary)
                        <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                <h4 class="text-sm font-bold text-amber-800">Ringkasan Perubahan</h4>
                            </div>
                            <p class="text-xs text-amber-800 leading-relaxed">{{ $analysis->changes_summary }}</p>
                        </div>
                    @endif

                    @if($analysis->key_points)
                        <div>
                            <h4 class="text-sm font-bold text-[#071833] mb-2">Poin Penting</h4>
                            <ul class="space-y-1.5">
                                @foreach($analysis->key_points as $point)
                                    <li class="flex items-start gap-2 text-xs text-[#071833]">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($analysis->comparison_insights)
                        <div class="rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0]">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span>
                                <h4 class="text-sm font-bold text-[#071833]">Wawasan Perbandingan</h4>
                            </div>
                            <div class="text-xs text-[#071833] leading-relaxed whitespace-pre-wrap">{{ $analysis->comparison_insights }}</div>
                        </div>
                    @endif

                    @if($analysis->change_analysis)
                        <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">2</span>
                                <h4 class="text-sm font-bold text-amber-800">Analisis Perubahan</h4>
                            </div>
                            <div class="text-xs text-amber-800 leading-relaxed whitespace-pre-wrap">{{ $analysis->change_analysis }}</div>
                        </div>
                    @endif

                    @if($analysis->revocation_analysis)
                        <div class="rounded-xl bg-rose-50 p-4 ring-1 ring-rose-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">3</span>
                                <h4 class="text-sm font-bold text-rose-800">Analisis Pencabutan</h4>
                            </div>
                            <div class="text-xs text-rose-800 leading-relaxed whitespace-pre-wrap">{{ $analysis->revocation_analysis }}</div>
                        </div>
                    @endif
                </div>
            </x-card>

            @if(! empty($relatedData['related']))
                <x-card :padding="false">
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-[#071833]">Detail Regulasi Terkait</h3>
                                <p class="text-xs text-[#667085] mt-0.5">Daftar lengkap regulasi yang saling berkaitan</p>
                            </div>
                        </div>
                    </x-slot>
                    <div class="divide-y divide-[#f0f3f8]">
                        @foreach($relatedData['related'] as $rel)
                            <div class="px-6 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('regulations.show', $rel['id']) }}" class="text-sm font-semibold text-[#071833] hover:text-[#c99a3e] transition">{{ $rel['regulation_number'] }}</a>
                                        <p class="text-xs text-[#667085] mt-0.5">{{ $rel['title'] }} ({{ $rel['year'] }})</p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($rel['type'])
                                            <x-badge :color="$regulation->type?->levelBadgeColor() ?? 'gray'">{{ $rel['type'] }}</x-badge>
                                        @endif
                                        @if($rel['is_parsed'])
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">Parsed</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-[10px] font-bold text-gray-600">No Parse</span>
                                        @endif
                                    </div>
                                </div>
                                @if(in_array($rel['regulation_number'], array_column($relatedData['amendments'] ?? [], 'regulation_number')))
                                    <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        Perubahan
                                    </div>
                                @endif
                                @if(in_array($rel['regulation_number'], array_column($relatedData['revocations'] ?? [], 'regulation_number')))
                                    <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-[10px] font-bold text-rose-700">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        Pencabutan
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <aside class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Informasi Analisis</h3>
                </x-slot>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Dibuat</span>
                        <span class="font-semibold text-[#071833]">{{ $analysis->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Sumber</span>
                        <span class="font-semibold text-[#071833]">
                            {{ $analysis->metadata['ai_generated'] ?? false ? 'AI' : 'Pattern' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Total Terkait</span>
                        <span class="font-semibold text-[#071833]">{{ $analysis->metadata['total_related'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Perubahan</span>
                        <span class="font-semibold text-amber-700">{{ $analysis->metadata['total_amendments'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Pencabutan</span>
                        <span class="font-semibold text-rose-700">{{ $analysis->metadata['total_revocations'] ?? 0 }}</span>
                    </div>
                    @if($analysis->references->isNotEmpty() || $analysis->pasal->isNotEmpty())
                    <div class="border-t border-[#e7eaf0] pt-3">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#667085] mb-2">Hasil Ekstraksi AI</p>
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Referensi Ditemukan</span>
                            <span class="font-semibold text-[#071833]">{{ $analysis->references->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-[#667085]">Pasal Terdeteksi</span>
                            <span class="font-semibold text-[#071833]">{{ $analysis->pasal->count() }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>

            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Aksi</h3>
                </x-slot>
                <div class="space-y-2.5">
                    <a href="{{ route('regulations.show', $regulation) }}" class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-[#c99a3e] text-sm font-bold text-white hover:bg-[#b88a2e] transition">
                        Detail Regulasi
                    </a>
                    <a href="{{ route('regulations.edit', $regulation) }}" class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-[#f6f8fb] text-sm font-semibold text-[#071833] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                        Edit Regulasi
                    </a>
                    <a href="{{ route('regulations.index') }}" class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl text-sm font-semibold text-[#667085] hover:text-[#071833] transition">
                        Kembali ke Daftar
                    </a>
                </div>
            </x-card>
        </aside>
    </div>
@endsection