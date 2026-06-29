@extends('layouts.app')

@section('title', 'Analisis Regulasi — ' . $regulation->regulation_number)
@section('header', 'Analisis Regulasi')

@section('content')
    @php $isSaved = $analysis && (($analysis->metadata['is_saved'] ?? false) || ($analysis->metadata['saved'] ?? false)); @endphp
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
                        @if(($stats['page_offset'] ?? 0) > 0)
                            <div class="mt-2 rounded-xl bg-blue-50 p-3 ring-1 ring-blue-200 text-center">
                                <p class="text-xs font-semibold text-blue-700">Konten regulasi dimulai dari halaman {{ ($stats['content_start_page'] ?? 1) }} (offset {{ $stats['page_offset'] }})</p>
                            </div>
                        @endif
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

            @php $relatedData = $analysis?->related_data ?? []; @endphp
            @if(! $analysis)
                <x-card>
                    <x-slot name="header">
                        <h3 class="text-lg font-bold text-[#071833]">Analisis Perbandingan</h3>
                    </x-slot>
                    <div class="text-center py-10">
                        <div class="mx-auto w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e] mb-4">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0 5.25 5.25M13.5 3.75h4.5m-4.5 0v4.5m0-4.5 5.25 5.25M3.75 13.5h4.5m-4.5 0v4.5m0-4.5 5.25-5.25M13.5 20.25h4.5m-4.5 0v-4.5m0 4.5 5.25-5.25"/></svg>
                        </div>
                        <p class="text-sm font-bold text-[#071833]">Belum Ada Analisis</p>
                        <p class="text-xs text-[#667085] mt-1">Generate analisis untuk membandingkan regulasi ini dengan regulasi terkait.</p>
                        <form method="POST" action="{{ route('regulations.analyze.generate', $regulation) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#c99a3e] text-sm font-bold text-white hover:bg-[#b88a2e] transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0 5.25 5.25M13.5 3.75h4.5m-4.5 0v4.5m0-4.5 5.25 5.25M3.75 13.5h4.5m-4.5 0v4.5m0-4.5 5.25-5.25M13.5 20.25h4.5m-4.5 0v-4.5m0 4.5 5.25-5.25"/></svg>
                                Generate AI Analysis
                            </button>
                        </form>
                    </div>
                </x-card>
            @else
                @php
                    $pasalList = $analysis->pasal;
                    $references = $analysis->references;
                    $matchedRefs = app(App\Services\RegulationAnalysisService::class)->matchReferencesWithDb($analysis);
                @endphp

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
                                @if($isSaved)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">Tersimpan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-[10px] font-bold text-amber-700">Belum Disimpan</span>
                                @endif
                            </div>
                        </div>
                    </x-slot>

                    <div class="space-y-5">
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
                            <div x-data="{ allSelected: false }">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.04a4.5 4.5 0 0 0-1.242-7.244l-4.5-4.5a4.5 4.5 0 0 0-6.364 6.364L4.34 8.598"/></svg>
                                    <h4 class="text-sm font-bold text-[#071833]">Referensi Regulasi Lain (dari teks)</h4>
                                    <span class="px-2 py-0.5 rounded-full bg-[#f6f8fb] text-[10px] font-bold text-[#667085]">{{ $references->count() }}</span>
                                </div>
                                <form method="POST" action="{{ route('regulations.analyze.connect-references', $regulation) }}" class="space-y-2">
                                    @csrf
                                    @foreach($matchedRefs as $idx => $item)
                                        @php
                                            $ref = $item['reference'];
                                            $match = $item['match'];
                                            $isAvailable = $item['is_available'];
                                            $confidence = $item['confidence'];
                                            $relBadge = match($ref->relationship) {
                                                'diubah' => ['bg-amber-100 text-amber-700', 'Diubah'],
                                                'dicabut' => ['bg-rose-100 text-rose-700', 'Dicabut'],
                                                'dirujuk' => ['bg-blue-100 text-blue-700', 'Dirujuk'],
                                                default => ['bg-gray-100 text-gray-600', $ref->relationship],
                                            };
                                            $alreadyConnected = $match && $regulation->relatedRegulations->contains('id', $match->id);
                                        @endphp
                                        <div class="flex items-start gap-3 p-3 rounded-xl {{ $isAvailable ? 'bg-emerald-50/50 ring-1 ring-emerald-200' : 'bg-[#f6f8fb] ring-1 ring-[#e7eaf0]' }}">
                                            @if($isAvailable && !$alreadyConnected)
                                                <div class="shrink-0 pt-1">
                                                    <input type="checkbox" name="reference_ids[]" value="{{ $ref->id }}" class="rounded border-[#d0d5dd] text-[#c99a3e] focus:ring-[#c99a3e]">
                                                </div>
                                            @endif
                                            <div class="shrink-0 w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[10px] font-bold text-[#667085] ring-1 ring-[#e7eaf0]">
                                                {{ $loop->iteration }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-[#071833]">{{ $ref->name }}</p>
                                                <p class="text-xs text-[#667085] mt-0.5">
                                                    <span class="font-semibold">Nomor:</span> {{ $ref->number }}
                                                    @if($ref->year) · <span class="font-semibold">Tahun:</span> {{ $ref->year }} @endif
                                                </p>
                                                <div class="flex items-center gap-2 mt-1.5">
                                                    @if($isAvailable)
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                            Available
                                                        </span>
                                                        @if($alreadyConnected)
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">
                                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.04a4.5 4.5 0 0 0-1.242-7.244l-4.5-4.5a4.5 4.5 0 0 0-6.364 6.364L4.34 8.598"/></svg>
                                                                Terhubung
                                                            </span>
                                                        @endif
                                                        @if($confidence === 'fuzzy')
                                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-700">Fuzzy Match</span>
                                                        @endif
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">
                                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                                            Not Available
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($match)
                                                    <p class="text-[10px] text-[#667085] mt-1">
                                                        Linked: {{ $match->type?->name ?? '' }} {{ $match->regulation_number }} ({{ $match->year }})
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="shrink-0">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $relBadge[0] }}">{{ $relBadge[1] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(collect($matchedRefs)->some(fn($i) => $i['is_available'] && !$regulation->relatedRegulations->contains('id', $i['match']->id)))
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#c99a3e] text-xs font-bold text-white hover:bg-[#b88a2e] transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.04a4.5 4.5 0 0 0-1.242-7.244l-4.5-4.5a4.5 4.5 0 0 0-6.364 6.364L4.34 8.598"/></svg>
                                            Connect Selected
                                        </button>
                                    @endif
                                </form>
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

                    @if($analysis && ($analysis->metadata['ai_generated'] ?? false))
                        <p class="text-[11px] text-amber-600 italic mt-4 border-t border-[#e7eaf0] pt-4">
                            Analisis ini dihasilkan oleh AI dan mungkin mengandung ketidakakuratan. Harap verifikasi informasi penting dengan sumber resmi.
                        </p>
                    @endif
                </x-card>
            @endif

            @if($regulation->isParsed())
                <x-card x-data="babCard()">
                    <x-slot name="header">
                        <div>
                            <h3 class="text-lg font-bold text-[#071833]">Analisis Per Bab</h3>
                            <p class="text-xs text-[#667085] mt-0.5">Analisis pasal dan referensi per bab secara bertahap</p>
                        </div>
                    </x-slot>

                    <div x-show="!babs.length && !loading && !error" class="text-center py-6">
                        <button @click="fetchBabs" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#c99a3e] text-sm font-bold text-white hover:bg-[#b88a2e] transition">
                            Muat Daftar Bab
                        </button>
                    </div>

                    <div x-show="loading && !analyzing" class="text-center py-6">
                        <svg class="animate-spin mx-auto w-6 h-6 text-[#c99a3e]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <p class="text-xs text-[#667085] mt-2">Memuat daftar bab...</p>
                    </div>

                    <div x-show="error" class="text-center py-6">
                        <p class="text-xs text-rose-600" x-text="error"></p>
                    </div>

                    <template x-if="babs.length">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-[#667085]">
                                    Ditemukan <strong x-text="babs.length"></strong> bab
                                    <span x-show="analyzing" class="text-amber-600">(sedang menganalisis...)</span>
                                </p>
                                <button @click="analyzeAll" x-show="!analyzing && !done"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#c99a3e] text-xs font-bold text-white hover:bg-[#b88a2e] transition">
                                    Analisis Semua Bab
                                </button>
                            </div>

                            <div x-show="analyzing" class="w-full bg-[#f0f3f8] rounded-full h-2">
                                <div class="bg-[#c99a3e] h-2 rounded-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                            </div>

                            <template x-for="(bab, i) in babs" :key="i">
                                <div class="rounded-xl border border-[#e7eaf0] overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-2.5 bg-[#fafbfc] border-b border-[#e7eaf0]">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-[#071833]" x-text="bab.label"></span>
                                            <span x-show="bab.status === 'done'" class="px-1.5 py-0.5 rounded bg-emerald-100 text-[10px] font-bold text-emerald-700">
                                                <span x-text="bab.pasal_count + ' pasal'"></span>
                                            </span>
                                            <span x-show="bab.status === 'processing'" class="px-1.5 py-0.5 rounded bg-amber-100 text-[10px] font-bold text-amber-700">Processing</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span x-show="bab.compliance_assessment === 'Sesuai'" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">Sesuai</span>
                                            <span x-show="bab.compliance_assessment === 'Perlu Penyesuaian'" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">Perlu Penyesuaian</span>
                                            <span x-show="bab.compliance_assessment === 'Tidak Sesuai'" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Tidak Sesuai</span>
                                            <span x-show="bab.status === 'done'" class="text-emerald-500">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div x-show="bab.status === 'done'" class="px-4 py-2.5 space-y-3">
                                        <div x-show="bab.insights" class="rounded-xl bg-[#f6f8fb] p-3 ring-1 ring-[#e7eaf0]">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085] mb-1">Insight</p>
                                            <p class="text-xs text-[#071833] leading-relaxed" x-text="bab.insights"></p>
                                        </div>
                                        <div x-show="bab.key_findings?.length" class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Temuan</p>
                                            <template x-for="(f, fi) in bab.key_findings" :key="fi">
                                                <div class="flex items-start gap-2 text-xs">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                                                    <span class="text-[#4a5568]" x-text="f"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="bab.pasal?.length" class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Pasal</p>
                                            <template x-for="(p, pi) in bab.pasal" :key="pi">
                                                <div class="flex items-start gap-2 text-xs">
                                                    <span class="font-semibold text-[#071833]" x-text="p.pasal"></span>
                                                    <span class="text-[#667085]" x-text="p.content"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="bab.references?.length" class="space-y-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Referensi</p>
                                            <template x-for="(r, ri) in bab.references" :key="ri">
                                                <div class="text-xs text-[#667085]" x-text="r.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </x-card>
            @endif

            @if($analysis && ! empty($relatedData['related']))
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
                                        @php
                                            $psLabel = $rel['parse_status_label'] ?? ($rel['is_parsed'] ? 'Complete' : 'Not Parsed');
                                            $psColor = $rel['parse_status_color'] ?? ($rel['is_parsed'] ? 'emerald' : 'gray');
                                            $psColors = ['emerald' => 'bg-emerald-100 text-emerald-700', 'amber' => 'bg-amber-100 text-amber-700', 'gray' => 'bg-gray-100 text-gray-600'];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $psColors[$psColor] ?? $psColors['gray'] }}">
                                            @if($psColor === 'gray')
                                                Not Parsed
                                            @elseif($psColor === 'amber')
                                                Parsed Incomplete
                                            @else
                                                Parsed Complete
                                            @endif
                                        </span>
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
                @if($analysis)
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
                        <span class="text-[#667085]">Status</span>
                        <span class="font-semibold {{ $isSaved ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $isSaved ? 'Tersimpan' : 'Belum Disimpan' }}
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
                @else
                <p class="text-sm text-[#667085]">Belum ada analisis.</p>
                @endif
            </x-card>

            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Aksi</h3>
                </x-slot>
                <div class="space-y-2.5">
                    @if(! $analysis)
                        <form method="POST" action="{{ route('regulations.analyze.generate', $regulation) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-[#c99a3e] text-sm font-bold text-white hover:bg-[#b88a2e] transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0 5.25 5.25M13.5 3.75h4.5m-4.5 0v4.5m0-4.5 5.25 5.25M3.75 13.5h4.5m-4.5 0v4.5m0-4.5 5.25-5.25M13.5 20.25h4.5m-4.5 0v-4.5m0 4.5 5.25-5.25"/></svg>
                                Generate AI Analysis
                            </button>
                        </form>
                    @elseif(! $isSaved)
                        <form method="POST" action="{{ route('regulations.analyze.save', $regulation) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-emerald-600 text-sm font-bold text-white hover:bg-emerald-700 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z"/></svg>
                                Simpan Analisis
                            </button>
                        </form>
                        <form method="POST" action="{{ route('regulations.reanalyze', $regulation) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-[#f6f8fb] text-sm font-semibold text-[#071833] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                                Regenerate
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('regulations.reanalyze', $regulation) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center gap-2 w-full h-11 rounded-xl bg-[#c99a3e] text-sm font-bold text-white hover:bg-[#b88a2e] transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                                Generate AI Again
                            </button>
                        </form>
                    @endif
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
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('babCard', () => ({
            babs: [],
            loading: false,
            analyzing: false,
            done: false,
            error: null,

            get progress() {
                if (!this.babs.length) return 0;
                return Math.round((this.babs.filter(b => b.status === 'done').length / this.babs.length) * 100);
            },

            async fetchBabs() {
                this.loading = true;
                this.error = null;
                try {
                    const res = await fetch('{{ route("regulations.analyze.babs-list", $regulation) }}');
                    const data = await res.json();
                    this.babs = (data.babs || []).map(b => ({ ...b, status: 'idle', pasal: [], references: [], pasal_count: 0, ref_count: 0 }));
                } catch (e) {
                    this.error = 'Gagal memuat daftar bab.';
                } finally {
                    this.loading = false;
                }
            },

            async analyzeAll() {
                this.analyzing = true;
                this.error = null;
                for (let i = 0; i < this.babs.length; i++) {
                    this.babs[i].status = 'processing';
                    try {
                        const res = await fetch('/regulations/{{ $regulation->id }}/analyze/bab/' + i, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                        const result = await res.json();
                        this.babs[i].status = 'done';
                        this.babs[i].pasal = result.pasal || [];
                        this.babs[i].references = result.references || [];
                        this.babs[i].pasal_count = result.pasal_count || 0;
                        this.babs[i].ref_count = result.ref_count || 0;
                    } catch (e) {
                        this.babs[i].status = 'done';
                    }
                }
                this.done = true;
                this.analyzing = false;
            },
        }));
    });
</script>
@endpush
@endsection