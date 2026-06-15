@extends('layouts.app')

@section('title', 'Edit Review')
@section('header', 'Edit Review')

@section('content')
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/18 blur-3xl"></div>
        <div class="relative">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                Editing Review
            </span>
            <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight">{{ $review->reviewDocument->title }}</h2>
            <p class="mt-3 text-white/70">Refine summaries and findings before finalizing this compliance review.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('reviews.update', $review) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <x-card>
            <x-slot name="header">
                <h3 class="text-lg font-bold text-[#071833]">Review Summary</h3>
            </x-slot>
            <div class="space-y-5">
                <div>
                    <label for="summary" class="block text-sm font-semibold text-[#071833] mb-2">Summary</label>
                    <textarea name="summary" id="summary" rows="4" class="input-premium input-textarea">{{ old('summary', $review->summary) }}</textarea>
                    @error('summary')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="notes" class="block text-sm font-semibold text-[#071833] mb-2">Internal Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="input-premium input-textarea">{{ old('notes', $review->notes) }}</textarea>
                    @error('notes')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[#071833]">Findings per Regulation</h3>
                    <span class="px-3 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $review->reviewDocument->categories->count() }} categories</span>
                </div>
            </x-slot>

            @php $existingFindings = $review->findings->keyBy(fn ($f) => $f->category_id); @endphp
            <div class="space-y-5">
                @foreach($review->reviewDocument->categories as $index => $category)
                    @php $existing = $existingFindings->get($category->id); @endphp
                    <article class="rounded-2xl border border-[#e7eaf0] bg-[#f6f8fb]/40 p-5">
                        <header class="mb-4">
                            <input type="hidden" name="findings[{{ $index }}][category_id]" value="{{ $category->id }}">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#c99a3e]">Regulation #{{ $index + 1 }}</p>
                            <h4 class="mt-1 text-base font-bold text-[#071833]">{{ $category->name }}</h4>
                            @if($category->description)
                                <p class="mt-1 text-xs text-[#667085]">{{ $category->description }}</p>
                            @endif
                        </header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-[#071833] mb-2">Compliance Status</label>
                                <select name="findings[{{ $index }}][compliance_status]" class="select-premium">
                                    <option value="">Select status…</option>
                                    @foreach($complianceStatuses as $status)
                                        <option value="{{ $status->value }}" {{ old("findings.$index.compliance_status", $existing?->compliance_status?->value) === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                @error("findings.$index.compliance_status")<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#071833] mb-2">Findings</label>
                                <textarea name="findings[{{ $index }}][findings]" rows="3" class="input-premium input-textarea">{{ old("findings.$index.findings", $existing?->findings) }}</textarea>
                                @error("findings.$index.findings")<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#071833] mb-2">Recommendations</label>
                                <textarea name="findings[{{ $index }}][recommendations]" rows="3" class="input-premium input-textarea">{{ old("findings.$index.recommendations", $existing?->recommendations) }}</textarea>
                                @error("findings.$index.recommendations")<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </x-card>

        <div class="flex flex-col sm:flex-row gap-3">
            <x-button type="submit" variant="primary" size="lg">Update Review</x-button>
            <x-button href="{{ route('reviews.show', $review) }}" variant="outline" size="lg">Cancel</x-button>
        </div>
    </form>
@endsection
