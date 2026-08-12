@extends('layouts.app')

@section('title', 'Kebutuhan Hukum')
@section('header', 'Kebutuhan Hukum')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-[#667085]">Pesan yang dikirim user dari landing page (Legal Risk Quick Check & Konsultasi).</p>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#e7eaf0]">
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Nama</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Email</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Telepon</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Kegiatan</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Status</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Nilai</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Pesan</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Dikirim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7eaf0]">
                    @forelse($requests as $request)
                        <tr class="hover:bg-[#f6f8fb] transition">
                            <td class="py-3.5 px-4 font-semibold text-[#071833]">{{ $request->name }}</td>
                            <td class="py-3.5 px-4">
                                <a href="mailto:{{ $request->email }}" class="text-[#667085] hover:text-[#c99a3e] underline decoration-dotted">{{ $request->email }}</a>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="https://wa.me/{{ preg_replace('/\D+/', '', $request->phone) }}" target="_blank" rel="noopener" class="text-[#667085] hover:text-[#c99a3e] underline decoration-dotted">{{ $request->phone }}</a>
                            </td>
                            <td class="py-3.5 px-4 text-[#667085] capitalize">{{ ucwords(str_replace('_', ' ', $request->legal_activities ?? '')) ?: '-' }}</td>
                            <td class="py-3.5 px-4 text-[#667085] capitalize">{{ ucwords(str_replace('_', ' ', $request->status_company ?? '')) ?: '-' }}</td>
                            <td class="py-3.5 px-4 text-[#667085]">{{ $request->value_trx ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-[#667085] max-w-xs truncate">{{ $request->message ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-[#667085]">{{ $request->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-sm text-[#667085]">Belum ada kebutuhan hukum masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $requests->links() }}</div>
    </x-card>
@endsection