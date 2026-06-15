@props(['type' => 'success', 'message' => ''])

@php
    $config = match($type) {
        'success' => [
            'bg' => 'from-emerald-50 to-white',
            'border' => 'border-emerald-200/60',
            'iconBg' => 'bg-emerald-100 text-emerald-600',
            'title' => 'text-emerald-900',
            'label' => 'Success',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        ],
        'error' => [
            'bg' => 'from-rose-50 to-white',
            'border' => 'border-rose-200/60',
            'iconBg' => 'bg-rose-100 text-rose-600',
            'title' => 'text-rose-900',
            'label' => 'Error',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>',
        ],
        'warning' => [
            'bg' => 'from-amber-50 to-white',
            'border' => 'border-amber-200/60',
            'iconBg' => 'bg-amber-100 text-amber-600',
            'title' => 'text-amber-900',
            'label' => 'Warning',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
        ],
        default => [
            'bg' => 'from-sky-50 to-white',
            'border' => 'border-sky-200/60',
            'iconBg' => 'bg-sky-100 text-sky-600',
            'title' => 'text-sky-900',
            'label' => 'Info',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>',
        ],
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="relative flex items-start gap-4 p-5 rounded-2xl border bg-gradient-to-r {{ $config['bg'] }} {{ $config['border'] }} shadow-[0_10px_30px_rgba(7,27,58,.06)]"
>
    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center {{ $config['iconBg'] }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $config['icon'] !!}</svg>
    </div>
    <div class="flex-1 min-w-0 pt-0.5">
        <p class="text-xs font-bold uppercase tracking-wider {{ $config['title'] }}">{{ $config['label'] }}</p>
        <p class="mt-0.5 text-sm text-[#071833]">{{ $message }}</p>
    </div>
    <button @click="show = false" type="button" class="shrink-0 -mr-1 p-1.5 rounded-lg text-[#667085] hover:bg-white/60 hover:text-[#071833] transition">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
