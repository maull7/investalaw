@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran')
@section('header', 'Konfirmasi Pembayaran')

@section('content')
    <div class="mb-6">
        <p class="text-sm text-[#667085]">Verifikasi bukti pembayaran sebelum paket user diaktifkan.</p>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#e7eaf0]">
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">User</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Paket</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Bukti</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Dikirim</th>
                        <th class="text-right py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7eaf0]">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-[#f6f8fb] transition">
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-[#071833]">{{ $payment->user->name }}</p>
                                <p class="text-xs text-[#667085]">{{ $payment->user->email }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-[#071833]">{{ $payment->package->name }}</p>
                                <p class="text-xs text-[#667085]">Rp{{ $payment->package->price }} {{ $payment->package->price_period }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($payment->payment_proof)
                                    <a href="{{ asset('storage/'.$payment->payment_proof) }}" target="_blank" rel="noopener">
                                        <img src="{{ asset('storage/'.$payment->payment_proof) }}" alt="Bukti"
                                            class="w-16 h-16 object-contain rounded-lg ring-1 ring-[#e7eaf0] bg-white">
                                    </a>
                                @else
                                    <span class="text-xs text-[#667085]">Belum upload</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-[#667085]">{{ $payment->updated_at->diffForHumans() }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <form method="POST" action="{{ route('packages.payment.confirm', $payment) }}" class="inline"
                                    onsubmit="return confirm('Konfirmasi pembayaran {{ $payment->user->name }}?')">
                                    @csrf
                                    <button type="submit" @disabled(!$payment->payment_proof)
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-100 hover:bg-emerald-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        Konfirmasi
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-[#667085]">Tidak ada pembayaran menunggu konfirmasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection