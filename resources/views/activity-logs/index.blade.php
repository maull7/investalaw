@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('header', 'Log Aktivitas')

@section('content')
    <div>
        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Compliance</p>
        <h2 class="mt-2 text-3xl font-bold text-[#071833] tracking-tight">Log Aktivitas</h2>
        <p class="mt-1.5 text-sm text-[#667085]">Riwayat aktivitas penting seluruh role selain user.</p>
    </div>

    <x-card class="mt-6">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid gap-3 md:grid-cols-2 lg:grid-cols-5">
            <input type="text" name="search" value="{{ request('search') }}" class="input-premium lg:col-span-2"
                placeholder="Cari pelaku, aksi, atau deskripsi...">
            <select name="role" class="input-premium">
                <option value="">Semua role</option>
                @foreach (['admin' => 'Admin', 'sub_admin' => 'Subadmin', 'reviewer' => 'Reviewer'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="action" class="input-premium">
                <option value="">Semua aksi</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-premium min-w-0" title="Dari tanggal">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-premium min-w-0" title="Sampai tanggal">
            </div>
            <div class="flex gap-3 md:col-span-2 lg:col-span-5">
                <x-button type="submit" variant="primary" size="md">Terapkan Filter</x-button>
                <x-button href="{{ route('activity-logs.index') }}" variant="outline" size="md">Reset</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false" class="mt-6">
        @if ($logs->isEmpty())
            <div class="py-14 text-center text-sm text-[#667085]">Belum ada aktivitas yang tercatat.</div>
        @else
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pelaku</th>
                            <th>Role</th>
                            <th>Aksi</th>
                            <th>Objek</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap text-xs text-[#667085]">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td class="font-semibold text-[#071833]">{{ $log->user?->name ?? '-' }}</td>
                                <td><x-badge color="{{ $log->user?->role === 'admin' ? 'yellow' : 'blue' }}">{{ str_replace('_', ' ', ucfirst($log->user?->role ?? '-')) }}</x-badge></td>
                                <td class="font-semibold uppercase text-xs text-[#071833]">{{ $log->action }}</td>
                                <td class="text-xs text-[#667085]">{{ class_basename($log->subject_type ?? '-') }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}</td>
                                <td class="text-sm text-[#667085]">{{ $log->description ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-[#e7eaf0] px-6 py-4">{{ $logs->links() }}</div>
            @endif
        @endif
    </x-card>
@endsection
