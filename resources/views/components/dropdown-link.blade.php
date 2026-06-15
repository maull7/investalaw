@props(['href' => '#'])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'block px-4 py-2.5 text-sm text-[#071833] hover:bg-[#f6f8fb] hover:text-[#c99a3e] transition']) }}
>
    {{ $slot }}
</a>
