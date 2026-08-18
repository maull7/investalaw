@php
    $pickerOpen = $categories
        ->filter(fn ($category) => $category->regulations->isNotEmpty())
        ->mapWithKeys(fn ($category) => [$category->id => true])
        ->all();
@endphp
<div
    x-data="{
        query: '',
        open: {{ Js::from($pickerOpen) }},
        allOpen: {{ Js::from($pickerOpen) }},
        init() {
            this.$watch('query', (value, previous) => {
                if (value && !previous) this.open = { ...this.allOpen };
            });
        },
        matchesQuery(el, q) {
            const tokens = (q || '').toLowerCase().match(/[a-z0-9]+/g) || [];
            if (!tokens.length) return true;
            const key = (el.dataset.search || '');
            return tokens.every(t => key.includes(t));
        }
    }"
>
    <input
        type="search"
        placeholder="Cari nomor atau judul regulasi…"
        @input.debounce.300ms="query = $event.target.value.toLowerCase()"
        class="input-premium mb-4"
    >

    <div class="rounded-2xl border border-[#e7eaf0] bg-[#f6f8fb]/40 p-4 max-h-96 overflow-y-auto space-y-3">
        @forelse($categories as $category)
            @if($category->regulations->isNotEmpty())
                <div class="rounded-xl bg-white ring-1 ring-[#e7eaf0] overflow-hidden">
                    <button
                        type="button"
                        @click="open['{{ $category->id }}'] = !open['{{ $category->id }}']"
                        class="w-full flex items-center justify-between gap-3 p-3 cursor-pointer hover:bg-[#f6f8fb] transition text-left"
                    >
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#c99a3e]">
                            {{ $category->name }}
                            <span class="text-[#667085] normal-case font-semibold">({{ $category->regulations->count() }})</span>
                        </span>
                        <svg class="w-4 h-4 text-[#667085] shrink-0 transition-transform" :class="open['{{ $category->id }}'] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open['{{ $category->id }}']" x-collapse>
                        <div class="px-3 pb-3 space-y-2">
                            @foreach($category->regulations as $regulation)
                                @php $searchKey = preg_replace('/[^a-z0-9]+/u', '', mb_strtolower(($regulation->regulation_number ?? '').' '.($regulation->title ?? '').' '.($regulation->year ?? ''))); @endphp
                                <label data-search="{{ $searchKey }}" x-show="matchesQuery($el, query)" class="flex items-start gap-3 p-3 rounded-xl bg-white ring-1 ring-[#e7eaf0] hover:ring-[#c99a3e]/40 cursor-pointer transition">
                                    <input type="checkbox" name="regulation_ids[]" value="{{ $regulation->id }}" {{ in_array($regulation->id, $selectedIds) ? 'checked' : '' }} class="checkbox-premium mt-0.5">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-[#071833]">{{ $regulation->regulation_number }}</p>
                                        <p class="text-xs text-[#667085] mt-0.5 line-clamp-1">{{ $regulation->title }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            @if($regulation->type)
                                                <x-badge :color="$regulation->type->levelBadgeColor()">Lv{{ $regulation->type->level }}</x-badge>
                                            @endif
                                            <span class="text-[10px] text-[#667085]">{{ $regulation->year }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="text-center py-8 text-sm text-[#667085]">Belum ada regulasi. Silakan tambahkan regulasi terlebih dahulu.</div>
        @endforelse
    </div>
</div>