@extends('layouts.app')

@section('title', 'Master Paket')
@section('header', 'Master Paket')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-[#667085]">Kelola paket & harga yang tampil di landing page.</p>
        <a href="{{ route('packages.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                    stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Tambah Paket
        </a>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#e7eaf0]">
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Nama</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Harga</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Benefit</th>
                        <th class="text-center py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Populer</th>
                        <th class="text-center py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Status</th>
                        <th class="text-right py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7eaf0]">
                    @forelse($packages as $package)
                        <tr class="hover:bg-[#f6f8fb] transition">
                            <td class="py-3.5 px-4 font-semibold text-[#071833]">{{ $package->name }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-[#071833]">Rp{{ $package->price }}</span>
                                <span class="text-xs text-[#667085]">{{ $package->price_period }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-[#667085] max-w-xs truncate">
                                {{ implode(' | ', $package->benefits ?? []) ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($package->is_popular)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gold/15 text-[#8c6a25]">Paling
                                        Populer</span>
                                @else
                                    <span class="text-[#e7eaf0]">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($package->is_active)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600">Aktif</span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('packages.edit', $package) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('packages.destroy', $package) }}" class="inline"
                                    onsubmit="return confirm('Hapus paket {{ $package->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-rose-600 bg-rose-50 ring-1 ring-rose-100 hover:bg-rose-100 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-[#667085]">Belum ada paket. Tambahkan paket baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection