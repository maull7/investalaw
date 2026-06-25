@props(['filters', 'field', 'label'])

@php
    $direction = 'asc';
    $icon = '';

    if (($filters['sort'] ?? 'year') === $field) {
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
        $icon = ($filters['direction'] ?? 'desc') === 'asc'
            ? '<svg class="w-3 h-3 inline-block ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>'
            : '<svg class="w-3 h-3 inline-block ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>';
    }

    $queryParams = array_merge(request()->query(), [
        'sort' => $field,
        'direction' => $direction,
    ]);
    $url = url()->current() . '?' . http_build_query($queryParams);
@endphp

<a href="{{ $url }}" class="inline-flex items-center gap-1 hover:text-[#c99a3e] transition whitespace-nowrap">
    {{ $label }}
    {!! $icon !!}
</a>
