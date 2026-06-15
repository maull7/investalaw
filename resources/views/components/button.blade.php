@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
    $baseClasses = 'group relative inline-flex items-center justify-center gap-2 font-semibold rounded-full whitespace-nowrap transition-all duration-300 focus:outline-none focus-visible:ring-4 disabled:opacity-50 disabled:pointer-events-none';

    $variantClasses = match($variant) {
        'primary' => 'text-white bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] hover:from-[#e6c06a] hover:to-[#f3dca3] hover:text-[#071833] btn-gold-glow focus-visible:ring-[#c99a3e]/30 hover:-translate-y-0.5',
        'secondary' => 'text-white bg-[#071b3a] hover:bg-[#0b2a55] btn-navy-glow focus-visible:ring-[#071b3a]/25 hover:-translate-y-0.5',
        'success' => 'text-white bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 shadow-[0_10px_24px_rgba(16,185,129,.28)] focus-visible:ring-emerald-500/30 hover:-translate-y-0.5',
        'danger' => 'text-white bg-gradient-to-br from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 shadow-[0_10px_24px_rgba(244,63,94,.28)] focus-visible:ring-rose-500/30 hover:-translate-y-0.5',
        'warning' => 'text-[#071833] bg-gradient-to-br from-amber-300 to-amber-400 hover:from-amber-400 hover:to-amber-500 shadow-[0_10px_24px_rgba(245,158,11,.28)] focus-visible:ring-amber-400/30 hover:-translate-y-0.5',
        'outline' => 'bg-white text-[#071833] border border-[#e7eaf0] hover:border-[#c99a3e]/60 hover:bg-[#f6f8fb] focus-visible:ring-[#c99a3e]/20 hover:-translate-y-0.5',
        'ghost' => 'bg-transparent text-[#071833] hover:bg-[#f6f8fb] focus-visible:ring-[#071b3a]/15',
        default => 'text-white bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] hover:brightness-110 btn-gold-glow focus-visible:ring-[#c99a3e]/30',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-7 py-3.5 text-sm',
        'xl' => 'px-8 py-4 text-base',
        default => 'px-5 py-2.5 text-sm',
    };

    $classes = "$baseClasses $variantClasses $sizeClasses";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
