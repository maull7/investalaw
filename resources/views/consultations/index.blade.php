@extends('layouts.app')

@section('title', 'Konsultasi Kak Vesta')
@section('header', 'Konsultasi Kak Vesta')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Buat Sesi Baru</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Pilih Regulasi (maks 10)</h3>
                        <p class="text-sm text-[#667085] mt-1">Pilih regulasi yang ingin Anda konsutansi. Kak Vesta akan menganalisa semua regulasi terpilih.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('consultations.store') }}">
                    @csrf

                    @include('review-documents._regulation-picker', ['selectedIds' => [], 'categories' => $categories])

                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-xs text-[#667085]">Pilih minimal 1, maksimal 10 regulasi.</p>
                        <x-button type="submit" variant="primary" size="lg">Mulai Sesi Konsultasi</x-button>
                    </div>

                    @error('regulation_ids')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </form>
            </x-card>
        </div>

        <div>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Sesi Saya</h3>
                </x-slot>

                <div class="divide-y divide-[#e7eaf0] max-h-[32rem] overflow-y-auto">
                    @forelse($sessions as $s)
                        <a href="{{ route('consultations.show', $s) }}" class="block p-4 hover:bg-[#f6f8fb] transition">
                            <p class="text-sm font-semibold text-[#071833]">{{ $s->title }}</p>
                            <p class="mt-0.5 text-xs text-[#667085]">
                                {{ $s->regulations_count }} regulasi ·
                                {{ $s->created_at->diffForHumans() }}
                            </p>
                        </a>
                    @empty
                        <div class="py-10 text-center text-sm text-[#667085]">
                            Belum ada sesi konsultasi.
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
@endsection