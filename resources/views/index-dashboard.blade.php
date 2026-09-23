@extends('layouts.app')

@section('title', 'Platform Informasi Regulasi & Kepatuhan Hukum Indonesia')
@section('header', 'Investalawco')
@section('meta_description',
    'Investalawco adalah platform informasi regulasi dan kepatuhan hukum Indonesia untuk
    pencarian peraturan, analisis dokumen, serta konsultasi hukum berbasis AI.')
@section('canonical', route('index-dash'))
@section('robots', $hasFilters || $showAllRegulations ? 'noindex, follow' : 'index, follow, max-image-preview:large')
@section('og_title', 'Investalawco — Platform Informasi Regulasi & Kepatuhan Hukum')

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([
            chr(64).'context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => route('index-dash').'#organization',
                    'name' => 'InvestaLawCo',
                    'alternateName' => 'Investalawco',
                    'url' => route('index-dash'),
                    'logo' => asset('favicon.svg'),
                    'description' => 'Platform informasi regulasi, analisis kepatuhan, dan solusi hukum investasi serta pasar modal Indonesia.',
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => route('index-dash').'#website',
                    'name' => 'Investalawco',
                    'url' => route('index-dash'),
                    'publisher' => ['@id' => route('index-dash').'#organization'],
                    'inLanguage' => 'id-ID',
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => route('index-dash').'?search={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
@endpush

@section('content')
    {{-- Hero / Welcome panel --}}


    <section
        class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9 shadow-[0_18px_50px_rgba(7,27,58,.18)]">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
            style="background-image: linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px); background-size: 48px 48px;">
        </div>


        <div class="relative grid lg:grid-cols-3 gap-8 items-center">
            <div class="lg:col-span-2">
                <span
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#c99a3e]/15 ring-1 ring-[#c99a3e]/30 text-[11px] font-semibold tracking-wider uppercase text-[#e6c06a]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#e6c06a]"></span>
                    {{ now()->format('l, d F Y') }}
                </span>
                <h1 class="mt-4 text-3xl sm:text-4xl font-bold tracking-tight leading-tight">
                    Investalawco: Informasi Regulasi & Kepatuhan Hukum Indonesia
                </h1>
                <p class="mt-3 text-white/70 max-w-xl">Temukan peraturan Indonesia, pantau kepatuhan, analisis dokumen,
                    dan dapatkan dukungan hukum investasi serta pasar modal dalam satu platform.</p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-button href="{{ route('review-documents.create') }}" variant="primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Upload Document
                    </x-button>
                    <a href="{{ route('reviews.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                        View Reviews
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="relative rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-5">
                <p class="text-[11px] font-semibold tracking-[0.16em] uppercase text-white/55">Compliance Health</p>
                @php
                    $rate =
                        $stats['total_documents'] > 0
                            ? round(($stats['approved_documents'] / max($stats['total_documents'], 1)) * 100)
                            : 0;
                    $offset = 251.2 * (1 - $rate / 100);
                @endphp
                <div class="mt-4 flex items-center gap-4">
                    <div class="relative w-24 h-24">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,.12)"
                                stroke-width="9" />
                            <circle cx="50" cy="50" r="40" fill="none" stroke="url(#goldGrad)"
                                stroke-width="9" stroke-linecap="round" stroke-dasharray="251.2"
                                stroke-dashoffset="{{ $offset }}" />
                            <defs>
                                <linearGradient id="goldGrad" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#c99a3e" />
                                    <stop offset="100%" stop-color="#e6c06a" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xl font-bold text-white">{{ $rate }}%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Approval Rate</p>
                        <p class="text-[11px] text-white/60 mt-0.5">{{ $stats['approved_documents'] }} of
                            {{ $stats['total_documents'] }} documents</p>
                        <div class="mt-3 flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 text-[10.5px] font-bold">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                </svg>
                                Live
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- AI Search --}}
    <x-card class="mt-7">
        <form method="GET" action="{{ route('regulations.ai-search') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="q" minlength="3" required class="input-premium flex-1"
                placeholder="Pencarian AI: tanya dalam bahasa natural, mis. sanksi untuk emiten yang terlambat lapor keuangan?">
            <x-button type="submit" variant="primary" size="lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                </svg>
                Pencarian AI
            </x-button>
        </form>
    </x-card>

    {{-- Peraturan Terkini --}}
    <x-card :padding="false" class="mt-7">
        <x-slot name="header">
            <div class="flex flex-wrap gap-3 justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-[#071833]">
                        {{ $hasFilters ? 'Hasil Pencarian Regulasi' : ($showAllRegulations ? 'Semua Regulasi' : 'Peraturan Terkini') }}
                    </h3>
                    <p class="text-xs text-[#667085] mt-0.5">
                        {{ $hasFilters ? 'Hasil pencarian regulasi' : ($showAllRegulations ? 'Daftar seluruh regulasi' : '5 regulasi terbaru yang diundangkan') }}
                    </p>
                </div>
                <x-button href="{{ route('index-dash', ['all' => 1]) }}" variant="outline" size="sm">
                    Semua Regulasi
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </x-button>
            </div>
        </x-slot>

        <form method="GET" action="{{ route('index-dash') }}" class="px-6 pb-5 border-b mt-5 border-[#e7eaf0]">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input type="search" name="search" value="{{ $search }}"
                    placeholder="Cari nomor atau judul regulasi..." class="input-premium">
                <select name="category_id" class="select-premium">
                    <option value="">Semua Kategori</option>
                    @foreach ($regulationFilterOptions['categories'] as $category)
                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="year" class="select-premium">
                    <option value="">Semua Tahun</option>
                    @foreach ($regulationFilterOptions['years'] as $regulationYear)
                        <option value="{{ $regulationYear }}" @selected($year == $regulationYear)>
                            {{ $regulationYear }}
                        </option>
                    @endforeach
                </select>
                <x-button type="submit" variant="primary" size="md">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Cari
                </x-button>
            </div>
        </form>

        @if ($latestRegulations->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th class="text-left">No. Regulasi</th>
                            <th class="text-left">Judul</th>
                            <th class="text-center">Jenis</th>
                            <th class="text-center">Kategori</th>
                            <th class="text-center">Tahun</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestRegulations as $reg)
                            <tr>
                                <td>
                                    <a href="{{ route('regulations.show', $reg) }}"
                                        class="font-semibold text-[#071833] hover:text-[#c99a3e] transition">{{ $reg->regulation_number }}</a>
                                </td>
                                <td>
                                    <a href="{{ route('regulations.show', $reg) }}"
                                        class="text-sm font-medium text-[#071833] hover:text-[#c99a3e] transition line-clamp-2">{{ $reg->title }}</a>
                                </td>
                                <td class="text-center">
                                    @if ($reg->type)
                                        <x-badge :color="$reg->type->levelBadgeColor()">{{ $reg->type->name }}</x-badge>
                                    @else
                                        <span class="text-xs text-[#667085]">-</span>
                                    @endif
                                </td>
                                <td class="text-center text-sm text-[#667085]">{{ $reg->category?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="font-semibold text-[#071833]">{{ $reg->year }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('regulations.show', $reg) }}"
                                        class="inline-flex items-center gap-1.5 px-3 h-9 rounded-xl text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-white hover:ring-[#c99a3e]/40 transition">
                                        Detail
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($showAllRegulations || $hasFilters)
                <div class="border-t border-[#e7eaf0] px-6 py-4">
                    {{ $latestRegulations->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-10 text-center">
                <p class="text-sm font-semibold text-[#071833]">Tidak ada regulasi ditemukan.</p>
            </div>
        @endif
    </x-card>

    {{-- Peraturan Terkait --}}
    @if ($regulationRelated->isNotEmpty())
        <x-card :padding="false" class="mt-6">
            <x-slot name="header">
                <div>
                    <h3 class="text-lg font-bold text-[#071833]">Peraturan Terkait</h3>
                    <p class="text-xs text-[#667085] mt-0.5">5 peraturan terkait terbaru berdasarkan data linkage
                    </p>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th class="text-left">Sumber Regulasi</th>
                            <th class="text-left">Nama Terkait</th>
                            <th class="text-center">Nomor</th>
                            <th class="text-center">Tahun</th>
                            <th class="text-center">Hubungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($regulationRelated as $ref)
                            <tr>
                                <td>
                                    <a href="{{ route('regulations.show', $ref->regulation) }}"
                                        class="font-semibold text-[#071833] hover:text-[#c99a3e] transition">{{ $ref->regulation->regulation_number }}</a>
                                </td>
                                <td class="text-sm font-medium text-[#071833]">{{ $ref->name }}</td>
                                <td class="text-center text-sm text-[#667085]">{{ $ref->number ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="font-semibold text-[#071833]">{{ $ref->year ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <x-badge :color="match ($ref->relationship) {
                                        'diubah' => 'amber',
                                        'dicabut' => 'rose',
                                        default => 'blue',
                                    }">{{ $ref->relationship }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    {{-- Stat grid --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mt-7">
        <x-stat-card title="Total Documents" :value="number_format($stats['total_documents'])" color="navy" subtitle="All time uploads">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card title="Pending Review" :value="number_format($stats['pending_documents'])" color="yellow" subtitle="Awaiting compliance review">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card title="Approved" :value="number_format($stats['approved_documents'])" color="green" subtitle="Cleared by reviewers">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card title="Total Reviews" :value="number_format($stats['total_reviews'])" color="gold" subtitle="Compliance assessments">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 3h4m1.5 6H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.4 48.4 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586M8.25 8.25H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
            </x-slot>
        </x-stat-card>
    </section>

    {{-- Main content grid --}}

@endsection

@push('floating')
    {{-- WhatsApp Float --}}
    <a class="fixed right-[22px] bottom-[22px] z-30 w-[58px] h-[58px] rounded-full grid place-items-center bg-[#22c55e] text-white shadow-[0_18px_34px_rgba(34,197,94,.35)] hover:scale-105 transition-transform"
        href="https://wa.me/6285385106788?text=Halo%20InvestaLawCo,%20saya%20ingin%20konsultasi%20Investasi%20dan%20Pasar%20Modal"
        target="_blank" rel="noopener" aria-label="WhatsApp InvestaLawco">
        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
    </a>
@endpush
