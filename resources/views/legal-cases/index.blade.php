@extends('layouts.app')

@section('title', 'Analisa Kasus')
@section('header', 'Analisa Kasus')

@section('content')
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Compliance Workspace</p>
            <h2 class="mt-2 text-3xl font-bold text-[#071833] tracking-tight">Analisa Kasus</h2>
            <p class="mt-1.5 text-sm text-[#667085]">Upload materi gugatan/perkara dan dapatkan analisa berdasarkan regulasi.</p>
        </div>
        <x-button href="{{ route('legal-cases.create') }}" variant="primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Kasus
        </x-button>
    </div>

    <x-card :padding="false" class="mt-6">
        @if($cases->isEmpty())
            <div class="text-center py-14">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </div>
                <p class="mt-4 text-base font-bold text-[#071833]">Belum ada kasus</p>
                <p class="mt-1 text-sm text-[#667085]">Tambahkan kasus untuk mulai analisa hukum.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Kasus</th>
                            <th>Nomor / Pengadilan</th>
                            <th>Status Perkara</th>
                            <th>Parse</th>
                            <th>Analisa</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cases as $case)
                            <tr>
                                <td>
                                    <a href="{{ route('legal-cases.show', $case) }}" class="font-semibold text-[#071833] hover:underline">{{ $case->title }}</a>
                                    <p class="text-xs text-[#667085]">{{ $case->user->name }}</p>
                                </td>
                                <td class="text-sm text-[#667085]">{{ $case->case_number ?: '-' }} / {{ $case->court ?: '-' }}</td>
                                <td>
                                    <x-badge :color="$case->status_case === 'ongoing' ? 'blue' : 'gray'">
                                        {{ $case->status_case === 'ongoing' ? 'Berlangsung' : 'Selesai' }}
                                    </x-badge>
                                </td>
                                <td>
                                    @if($case->isParsed())
                                        <x-badge color="emerald">Parsed</x-badge>
                                    @else
                                        <x-badge color="gray">Belum</x-badge>
                                    @endif
                                </td>
                                <td>
                                    @if($case->isAnalyzed())
                                        <x-badge color="emerald">Selesai</x-badge>
                                    @elseif($case->isAiProcessing('analysis'))
                                        <x-badge color="blue">Diproses</x-badge>
                                    @else
                                        <x-badge color="gray">Belum</x-badge>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('legal-cases.show', $case) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-[#667085] hover:bg-[#f6f8fb] transition" title="Lihat">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                                        </a>
                                        <a href="{{ route('legal-cases.edit', $case) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-[#667085] hover:bg-[#f6f8fb] transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a2.121 2.121 0 1 1 3 3L19.863 7.487m-3-3L8.25 13.1l-1.5 4.5 4.5-1.5 8.613-8.613m-3-3 3 3"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('legal-cases.destroy', $case) }}" onsubmit="return confirm('Hapus kasus ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($cases->hasPages())
                <div class="px-6 py-4 border-t border-[#e7eaf0]">
                    {{ $cases->links() }}
                </div>
            @endif
        @endif
    </x-card>
@endsection