@extends('layouts.app')

@section('title', 'Type Prompts')
@section('header', 'Type Prompts')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-[#667085]">Kelola tipe prompt yang digunakan oleh AI prompts.</p>
        <a href="{{ route('type-prompts.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] hover:brightness-110 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Type Prompt
        </a>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#e7eaf0]">
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Name</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Slug</th>
                        <th class="text-left py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Description</th>
                        <th class="text-center py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Prompts</th>
                        <th class="text-center py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Active</th>
                        <th class="text-right py-3.5 px-4 text-[11px] font-bold uppercase tracking-wider text-[#667085]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7eaf0]">
                    @forelse($types as $type)
                        <tr class="hover:bg-[#f6f8fb] transition">
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#071833] capitalize">{{ $type->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-[#667085]">{{ $type->slug }}</td>
                            <td class="py-3.5 px-4 text-[#667085] max-w-xs truncate">{{ $type->description ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center font-semibold text-[#071833]">{{ $type->prompts_count }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($type->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600">Active</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-500">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('type-prompts.edit', $type) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-[#071833] bg-[#f6f8fb] ring-1 ring-[#e7eaf0] hover:bg-[#e7eaf0] transition">Edit</a>
                                <form method="POST" action="{{ route('type-prompts.destroy', $type) }}" class="inline" onsubmit="return confirm('Hapus type prompt {{ $type->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-rose-600 bg-rose-50 ring-1 ring-rose-200 hover:bg-rose-100 transition">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-[#667085]">No type prompts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection