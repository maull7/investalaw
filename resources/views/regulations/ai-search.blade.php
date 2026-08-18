@extends('layouts.app')

@section('title', 'Pencarian AI Regulasi')
@section('header', 'Pencarian AI Regulasi')

@section('content')
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Regulasi</p>
            <h2 class="mt-2 text-3xl font-bold text-[#071833] tracking-tight">Pencarian AI Regulasi</h2>
            <p class="mt-1.5 text-sm text-[#667085]">AI mencari regulasi relevan dari database berdasarkan pertanyaan Anda.</p>
        </div>
        <x-button href="{{ route('regulations.index') }}" variant="outline">Kembali ke Daftar Regulasi</x-button>
    </div>

    <x-card class="mt-6">
        <form method="GET" action="{{ route('regulations.ai-search') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="q" value="{{ $query ?? '' }}" minlength="3" required class="input-premium flex-1"
                placeholder="Contoh: sanksi apa untuk emiten yang terlambat lapor keuangan?">
            <x-button type="submit" variant="primary" size="lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                Pencarian AI
            </x-button>
        </form>
    </x-card>

    @if (isset($results))
        <x-card :padding="false" class="mt-6">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-[#071833]">Hasil Pencarian AI</h3>
                        <p class="text-xs text-[#667085] mt-0.5">Pertanyaan: <strong class="text-[#071833]">"{{ $query }}"</strong></p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $results->count() }} regulasi</span>
                </div>
            </x-slot>

            @if ($results->isEmpty())
                <div class="text-center py-12">
                    <p class="text-sm text-[#667085]">AI tidak menemukan regulasi yang relevan di database. Coba ubah kata kunci.</p>
                </div>
            @else
                <div class="divide-y divide-[#e7eaf0]">
                    @foreach ($results as $index => $result)
                        <a href="{{ route('regulations.show', $result) }}" class="block px-6 py-4 hover:bg-[#f6f8fb] transition">
                            <div class="flex items-start gap-3">
                                <span class="shrink-0 w-7 h-7 rounded-xl bg-[#c99a3e]/10 text-[#c99a3e] flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-bold text-[#071833]">{{ $result->regulation_number }}</p>
                                        @if ($result->type)
                                            <x-badge color="gray">{{ $result->type->name }}</x-badge>
                                        @endif
                                        <span class="text-xs text-[#667085]">{{ $result->year }}</span>
                                    </div>
                                    <p class="text-xs text-[#667085] mt-0.5">{{ $result->title }}</p>
                                    <p class="mt-2 text-xs text-[#4a5568] leading-relaxed bg-[#f6f8fb] rounded-lg px-3 py-2 ring-1 ring-[#e7eaf0]">
                                        <span class="font-semibold text-[#c99a3e]">Alasan AI: </span>{{ $result->ai_reason }}
                                    </p>
                                    @if ($result->ai_snippet)
                                        <p class="mt-1.5 text-xs text-[#667085] leading-relaxed">…{{ $result->ai_snippet }}…</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>
    @endif

    @if (session('error'))
        <div class="mt-6 rounded-2xl bg-rose-50 ring-1 ring-rose-200 px-5 py-4 text-sm font-medium text-rose-700">
            {{ session('error') }}
        </div>
    @endif
@endsection