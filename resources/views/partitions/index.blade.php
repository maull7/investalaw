@extends('layouts.app')

@section('title', 'Partisi Dokumen')
@section('header', 'Partisi Dokumen')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/15 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                        <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                        Partisi Dokumen
                    </span>
                    <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-white/10 text-white/80 uppercase tracking-wider">{{ $totalPages }} halaman</span>
                </div>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight leading-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70 max-w-3xl leading-relaxed">Definisikan struktur partisi halaman PDF, lalu lakukan analisa kepatuhan per-partisi terhadap regulasi acuan.</p>
            </div>
            <div class="shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('review-documents.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white border border-white/15 bg-white/5 hover:bg-white/10 backdrop-blur transition">
                    Back to Documents
                </a>
            </div>
        </div>
    </section>

    <div class="mt-6">
        {{-- Tab Navigation --}}
        <div class="flex gap-1 bg-[#f6f8fb] rounded-2xl p-1.5 ring-1 ring-[#e7eaf0] mb-6">
            <span class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold bg-white shadow-sm ring-1 ring-[#e7eaf0] text-[#071833]">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                    Partisi & Analisa
                </span>
            </span>
            <a href="{{ route('partitions.parsed-text', $document) }}"
               class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold transition text-[#667085] hover:text-[#071833] text-center">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Parse PDF
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#c99a3e]/10 text-[#c99a3e]">{{ $totalPages }} hlm</span>
                </span>
            </a>
        </div>

        {{-- Partitions Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Sidebar --}}
        <div class="space-y-6">
            {{-- Partition Configuration --}}
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[#071833]">Partisi Dokumen</h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $document->partitions->count() }}</span>
                    </div>
                </x-slot>

                <div x-data="partitionForm({{ json_encode($document->partitions->map(fn($p) => ['name' => $p->name, 'start_page' => $p->start_page, 'end_page' => $p->end_page, 'description' => $p->description ?? ''])->values()) }}, {{ $totalPages }})">
                    <form method="POST" action="{{ route('partitions.store', $document) }}">
                        @csrf
                        <div class="space-y-3">
                            <template x-for="(partition, index) in partitions" :key="index">
                                <div class="rounded-xl bg-[#f6f8fb] p-3 ring-1 ring-[#e7eaf0]">
                                    <div class="flex items-center justify-between mb-2">
                                        <input type="text"
                                               :name="`partitions[${index}][name]`"
                                               x-model="partition.name"
                                               placeholder="Nama Partisi"
                                               class="input-premium !py-1.5 !text-xs flex-1 mr-2">
                                        <button type="button" @click="removePartition(index)" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition" x-show="partitions.length > 1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Hal. Awal</label>
                                            <input type="number"
                                                   :name="`partitions[${index}][start_page]`"
                                                   x-model.number="partition.start_page"
                                                   min="1"
                                                   :max="totalPages"
                                                   class="input-premium !py-1.5 !text-xs mt-0.5">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Hal. Akhir</label>
                                            <input type="number"
                                                   :name="`partitions[${index}][end_page]`"
                                                   x-model.number="partition.end_page"
                                                   min="1"
                                                   :max="totalPages"
                                                   class="input-premium !py-1.5 !text-xs mt-0.5">
                                        </div>
                                    </div>
                                    <input type="text"
                                           :name="`partitions[${index}][description]`"
                                           x-model="partition.description"
                                           placeholder="Deskripsi (opsional)"
                                           class="input-premium !py-1.5 !text-xs mt-2 w-full">
                                </div>
                            </template>
                        </div>

                        <template x-if="errorMessage">
                            <div class="mt-3 p-2.5 rounded-xl bg-rose-50 text-rose-700 text-xs font-medium" x-text="errorMessage"></div>
                        </template>

                        <div class="mt-4 flex gap-2">
                            <button type="button" @click="addPartition()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#c99a3e] bg-[#c99a3e]/10 hover:bg-[#c99a3e]/20 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Tambah Partisi
                            </button>
                            <x-button type="submit" variant="primary" size="sm">
                                Simpan Partisi
                            </x-button>
                        </div>
                    </form>
                </div>
            </x-card>

            {{-- Saved Partitions with Analysis --}}
            @if($document->partitions->isNotEmpty())
                <x-card :padding="false">
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#071833]">Data Partisi & Analisa</h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">{{ $document->partitions->count() }} partisi</span>
                        </div>
                    </x-slot>

                    <div x-data="{ openPartition: null, openAnalysis: null }">
                        @foreach($document->partitions as $index => $partition)
                            @php $analysis = $partition->analysis; @endphp
                            <div class="border-b border-[#e7eaf0] last:border-0">
                                {{-- Partition Header Row --}}
                                <div class="flex items-center justify-between px-4 py-3 hover:bg-[#f6f8fb]/40 transition">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <button @click="openPartition = openPartition === {{ $index }} ? null : {{ $index }}" class="flex items-center gap-2 min-w-0 flex-1 text-left">
                                            <svg class="w-3.5 h-3.5 text-[#667085] shrink-0 transition-transform" :class="openPartition === {{ $index }} ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                            <span class="font-semibold text-[#071833] text-sm truncate">{{ $partition->name }}</span>
                                            <span class="text-[10px] text-[#667085] font-normal shrink-0">h.{{ $partition->start_page }}–{{ $partition->end_page }} ({{ ($partition->end_page - $partition->start_page) + 1 }}hlm)</span>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 ml-2">
                                        @if($analysis)
                                            @php
                                                $statusColor = match($analysis->compliance_status?->value) {
                                                    'compliant' => 'emerald',
                                                    'partially_compliant' => 'amber',
                                                    'non_compliant' => 'rose',
                                                    default => 'gray'
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                                                {{ $analysis->compliance_status?->label() ?? 'Terisi' }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Belum</span>
                                        @endif
                                        <button @click="openAnalysis = openAnalysis === {{ $index }} ? null : {{ $index }}" class="p-1.5 rounded-lg text-[#c99a3e] hover:bg-[#c99a3e]/10 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Partition Detail (expandable) --}}
                                <div x-show="openPartition === {{ $index }}" x-collapse>
                                    <div class="px-4 pb-3 space-y-2">
                                        @if($partition->description)
                                            <p class="text-xs text-[#667085]">{{ $partition->description }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 text-[10px] text-[#667085]">
                                            <span>Halaman: <strong class="text-[#071833]">{{ $partition->start_page }} – {{ $partition->end_page }}</strong></span>
                                            <span>Jumlah: <strong class="text-[#071833]">{{ ($partition->end_page - $partition->start_page) + 1 }} hlm</strong></span>
                                        </div>
                                        <button
                                            @click="openAnalysis = openAnalysis === {{ $index }} ? null : {{ $index }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#c99a3e] bg-[#c99a3e]/10 hover:bg-[#c99a3e]/20 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                            {{ $analysis ? 'Edit Analisa' : 'Buat Analisa' }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Analysis Form (expandable) --}}
                                <div x-show="openAnalysis === {{ $index }}" x-collapse>
                                    <div class="px-4 pb-4">
                                        <form method="POST" action="{{ route('partitions.save-analysis', [$document, $partition]) }}">
                                            @csrf
                                            <div class="space-y-3 rounded-xl bg-[#f6f8fb] p-4 ring-1 ring-[#e7eaf0]">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Analisa Partisi: {{ $partition->name }}</p>

                                                <div>
                                                    <label class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Ringkasan</label>
                                                    <textarea name="summary" rows="3" class="input-premium !text-xs mt-1 w-full">{{ $analysis?->summary ?? '' }}</textarea>
                                                </div>

                                                <div>
                                                    <label class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Temuan / Catatan</label>
                                                    <textarea name="findings" rows="3" class="input-premium !text-xs mt-1 w-full" placeholder="Deskripsikan temuan kepatuhan partisi ini terhadap regulasi acuan...">{{ $analysis?->findings ?? '' }}</textarea>
                                                </div>

                                                <div>
                                                    <label class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Status Kepatuhan</label>
                                                    <select name="compliance_status" class="select-premium !text-xs mt-1 w-full">
                                                        <option value="">— Pilih Status —</option>
                                                        <option value="compliant" {{ $analysis?->compliance_status?->value === 'compliant' ? 'selected' : '' }}>Compliant</option>
                                                        <option value="partially_compliant" {{ $analysis?->compliance_status?->value === 'partially_compliant' ? 'selected' : '' }}>Partially Compliant</option>
                                                        <option value="non_compliant" {{ $analysis?->compliance_status?->value === 'non_compliant' ? 'selected' : '' }}>Non-Compliant</option>
                                                    </select>
                                                </div>

                                                <div class="flex justify-end">
                                                    <x-button type="submit" variant="primary" size="sm">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                        Simpan Analisa
                                                    </x-button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Summary Footer --}}
                        <div class="bg-[#f6f8fb]/60 px-4 py-2.5 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-[#667085] uppercase tracking-wider">Total Halaman Terpartisi</span>
                            <span class="text-xs font-bold text-[#071833]">
                                {{ $document->partitions->sum(fn($p) => ($p->end_page - $p->start_page) + 1) }} / {{ $totalPages }} hlm
                            </span>
                        </div>
                    </div>
                </x-card>

                {{-- Generate AI Analysis --}}
                <x-card>
                    <x-slot name="header">
                        <h3 class="text-base font-bold text-[#071833]">Analisa AI Per-Partisi</h3>
                    </x-slot>
                    <p class="text-sm text-[#667085] mb-4">AI akan menganalisa setiap partisi secara terpisah terhadap regulasi acuan.</p>
                    <form method="POST" action="{{ route('partitions.analyse', $document) }}" x-data="{ aiType: 'analisa' }">
                        @csrf
                        <div class="flex gap-2">
                            <select name="type" x-model="aiType" class="select-premium flex-1 !text-xs">
                                <option value="analisa">Analisa</option>
                                <option value="review">Review</option>
                                <option value="rekomendasi">Rekomendasi</option>
                                <option value="validitas">Validitas</option>
                            </select>
                            <x-button type="submit" variant="primary" size="sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                                Generate AI
                            </x-button>
                        </div>
                    </form>
                </x-card>

                {{-- Regulasi Acuan --}}
                @if($document->regulations->isNotEmpty())
                    <x-card>
                        <x-slot name="header">
                            <h3 class="text-base font-bold text-[#071833]">Regulasi Acuan</h3>
                        </x-slot>
                        <div class="space-y-2">
                            @foreach($document->regulations as $reg)
                                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-[#071833] truncate">{{ $reg->regulation_number }}</p>
                                        <p class="text-[10px] text-[#667085] truncate">{{ $reg->title }} ({{ $reg->year }})</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endif
            @endif
        </div>

        {{-- Right: PDF Viewer --}}
        <div class="lg:col-span-2">
            <x-card :padding="false">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[#071833]">PDF Viewer</h3>
                    </div>
                </x-slot>
                <div x-data="pdfViewer('{{ route('review-documents.view-file', $document) }}')"
                     @go-to-page.window="goToPage($event.detail.page)"
                     class="p-4">
                    {{-- Controls --}}
                    <div class="flex items-center justify-between mb-4 px-2">
                        <div class="flex items-center gap-2">
                            <button @click="prevPage()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                            </button>
                            <span class="text-sm font-semibold text-[#071833]" x-text="`${currentPage} / ${totalPages}`"></span>
                            <button @click="nextPage()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="zoomOut()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM13.5 7.5h-6"/></svg>
                            </button>
                            <span class="text-xs font-semibold text-[#667085]" x-text="`${Math.round(scale * 100)}%`"></span>
                            <button @click="zoomIn()" class="p-2 rounded-xl text-[#667085] hover:bg-[#f6f8fb] hover:text-[#071833] transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Canvas --}}
                    <div class="overflow-auto max-h-[80vh] bg-[#e7eaf0] rounded-xl p-2 flex justify-center">
                        <canvas x-ref="pdfCanvas" class="shadow-lg"></canvas>
                    </div>

                    {{-- Page Jump --}}
                    <div class="mt-3 flex items-center gap-2 px-2">
                        <label class="text-xs font-semibold text-[#667085]">Ke halaman:</label>
                        <input type="number" min="1" :max="totalPages" @change="goToPage(parseInt($event.target.value))" class="input-premium !py-1.5 !text-xs w-20">
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
function partitionForm(existingPartitions, totalPages) {
    return {
        partitions: existingPartitions.length > 0
            ? existingPartitions
            : [{ name: '', start_page: 1, end_page: 1, description: '' }],
        totalPages: totalPages,
        errorMessage: '',

        addPartition() {
            const last = this.partitions[this.partitions.length - 1];
            this.partitions.push({
                name: '',
                start_page: last ? last.end_page + 1 : 1,
                end_page: last ? last.end_page + 1 : 1,
                description: '',
            });
        },

        removePartition(index) {
            if (this.partitions.length > 1) {
                this.partitions.splice(index, 1);
            }
        },
    };
}
</script>
@endpush
