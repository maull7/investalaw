@props(['header' => null, 'footer' => null, 'padding' => true, 'hoverable' => false])

@php
    $base = 'card-premium overflow-hidden';
    if ($hoverable) {
        $base .= ' card-premium-hover';
    }
@endphp

<div {{ $attributes->merge(['class' => $base]) }}>
    @if($header)
        <div class="px-6 sm:px-8 py-5 border-b border-[#e7eaf0] bg-gradient-to-b from-white to-[#f6f8fb]/40">
            {{ $header }}
        </div>
    @endif
    <div class="{{ $padding ? 'p-6 sm:p-8' : '' }}">
        {{ $slot }}
    </div>
    @if($footer)
        <div class="px-6 sm:px-8 py-4 border-t border-[#e7eaf0] bg-[#f6f8fb]/60">
            {{ $footer }}
        </div>
    @endif
</div>
