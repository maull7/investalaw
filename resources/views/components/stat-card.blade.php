@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'color' => 'gold',
    'trend' => null,
    'trendLabel' => null,
    'href' => null,
    'subtitle' => null,
])

@php
    $palette = match($color) {
        'gold' => [
            'iconBg' => 'bg-gradient-to-br from-[#c99a3e] to-[#e6c06a]',
            'iconText' => 'text-white',
            'accent' => 'from-[#c99a3e]/70 via-[#e6c06a]/70 to-transparent',
            'ring' => 'ring-[#c99a3e]/15',
        ],
        'navy' => [
            'iconBg' => 'bg-gradient-to-br from-[#071b3a] to-[#0b2a55]',
            'iconText' => 'text-white',
            'accent' => 'from-[#071b3a]/60 via-[#0b2a55]/60 to-transparent',
            'ring' => 'ring-[#071b3a]/10',
        ],
        'green' => [
            'iconBg' => 'bg-gradient-to-br from-emerald-500 to-emerald-600',
            'iconText' => 'text-white',
            'accent' => 'from-emerald-400/60 via-emerald-500/60 to-transparent',
            'ring' => 'ring-emerald-200/40',
        ],
        'yellow' => [
            'iconBg' => 'bg-gradient-to-br from-amber-400 to-amber-500',
            'iconText' => 'text-white',
            'accent' => 'from-amber-300/70 via-amber-400/70 to-transparent',
            'ring' => 'ring-amber-200/40',
        ],
        'red' => [
            'iconBg' => 'bg-gradient-to-br from-rose-500 to-rose-600',
            'iconText' => 'text-white',
            'accent' => 'from-rose-400/60 via-rose-500/60 to-transparent',
            'ring' => 'ring-rose-200/40',
        ],
        'blue' => [
            'iconBg' => 'bg-gradient-to-br from-sky-500 to-sky-600',
            'iconText' => 'text-white',
            'accent' => 'from-sky-400/60 via-sky-500/60 to-transparent',
            'ring' => 'ring-sky-200/40',
        ],
        'purple' => [
            'iconBg' => 'bg-gradient-to-br from-violet-500 to-violet-600',
            'iconText' => 'text-white',
            'accent' => 'from-violet-400/60 via-violet-500/60 to-transparent',
            'ring' => 'ring-violet-200/40',
        ],
        default => [
            'iconBg' => 'bg-gradient-to-br from-[#c99a3e] to-[#e6c06a]',
            'iconText' => 'text-white',
            'accent' => 'from-[#c99a3e]/70 via-[#e6c06a]/70 to-transparent',
            'ring' => 'ring-[#c99a3e]/15',
        ],
    };

    $tag = $href ? 'a' : 'div';
    $hrefAttr = $href ? "href=\"$href\"" : '';
@endphp

<{!! $tag !!} {!! $hrefAttr !!} {{ $attributes->merge(['class' => 'group relative card-premium card-premium-hover overflow-hidden block p-6 sm:p-7']) }}>
    {{-- Decorative gradient bar --}}
    <span class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r {{ $palette['accent'] }}"></span>

    {{-- Floating decorative blob --}}
    <span class="pointer-events-none absolute -bottom-12 -right-12 w-40 h-40 rounded-full bg-gradient-to-br from-[#c99a3e]/10 to-transparent blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></span>

    <div class="relative flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#667085]">{{ $title }}</p>
            <p class="mt-3 text-3xl sm:text-4xl font-bold tracking-tight text-[#071833] leading-none">{{ $value }}</p>

            @if($subtitle)
                <p class="mt-2 text-xs text-[#667085]">{{ $subtitle }}</p>
            @endif

            @if($trend !== null)
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $trend >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            @if($trend >= 0)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            @endif
                        </svg>
                        {{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
                    </span>
                    @if($trendLabel)
                        <span class="text-[11px] text-[#667085]">{{ $trendLabel }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if($icon)
            <div class="shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center ring-1 ring-inset {{ $palette['iconBg'] }} {{ $palette['iconText'] }} {{ $palette['ring'] }} shadow-[0_8px_20px_rgba(7,27,58,.12)] group-hover:scale-105 transition-transform duration-300">
                {{ $icon }}
            </div>
        @endif
    </div>
</{!! $tag !!}>
