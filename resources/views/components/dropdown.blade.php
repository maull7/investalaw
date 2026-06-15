@props([
    'align' => 'right',
    'width' => '56',
])

@php
    $alignClass = match($align) {
        'left' => 'origin-top-left left-0',
        'right' => 'origin-top-right right-0',
        default => 'origin-top-right right-0',
    };

    $widthClass = match($width) {
        '32' => 'w-32',
        '40' => 'w-40',
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        '72' => 'w-72',
        default => 'w-56',
    };
@endphp

<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="absolute z-50 mt-3 {{ $alignClass }} {{ $widthClass }} bg-white rounded-2xl shadow-[0_18px_50px_rgba(7,27,58,.18)] ring-1 ring-[#e7eaf0] overflow-hidden"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
