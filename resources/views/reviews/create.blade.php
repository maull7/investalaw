@extends('layouts.app')

@section('title', 'Create Review')
@section('header', 'New Review')

@section('content')
    {{-- Document context --}}
    <section class="relative overflow-hidden rounded-[24px] bg-navy-gradient text-white p-7 sm:p-9">
        <div class="pointer-events-none absolute -top-24 -right-16 w-80 h-80 rounded-full bg-[#c99a3e]/18 blur-3xl"></div>
        <div class="relative grid lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-bold rounded-full bg-[#c99a3e]/20 ring-1 ring-[#c99a3e]/30 text-[#e6c06a] uppercase tracking-wider">
                    <span class="w-1 h-1 rounded-full bg-[#e6c06a]"></span>
                    New Compliance Review
                </span>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight">{{ $document->title }}</h2>
                <p class="mt-3 text-white/70">Provide your assessment per regulation category. Each finding will be saved as part of this review.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-5">
                <p class="text-[11px] font-semibold tracking-[0.16em] uppercase text-white/55">Submitted By</p>
                <div class="mt-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] text-[#071b3a] font-bold flex items-center justify-center">
                        {{ strtoupper(substr($document->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $document->user->name }}</p>
                        <p class="text-xs text-white/60">{{ $document->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('reviews.store') }}" class="mt-6 space-y-6">
        @csrf
        <input type="hidden" name="review_document_id" value="{{ $document->id }}">

        <x-card>
            <x-slot name="header">
                <div>
                    <h3 class="text-lg font-bold text-[#071833]">Review Summary</h3>
                    <p class="text-xs text-[#667085] mt-0.5">Executive overview that frames the entire assessment.</p>
                </div>
            </x-slot>
            <div class="space-y-5">
                <div>
                    <label for="summary" class="block text-sm font-semibold text-[#071833] mb-2">Summary</label>
                    <textarea name="summary" id="summary" rows="4" class="input-premium input-textarea" placeholder="High-level summary of compliance posture…">{{ old('summary') }}</textarea>
                    @error('summary')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="notes" class="block text-sm font-semibold text-[#071833] mb-2">Internal Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="input-premium input-textarea" placeholder="Optional notes for compliance team reference…">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-[#071833]">Findings per Regulation</h3>
                        <p class="text-xs text-[#667085] mt-0.5">Evaluate each linked regulation independently.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-[#f6f8fb] text-xs font-bold text-[#667085]">{{ $document->categories->count() }} categories</span>
                </div>
            </x-slot>

            @if($document->categories->isEmpty())
                <p class="text-sm text-[#667085]">No categories associated with this document.</p>
            @else
                <div class="space-y-5">
                    @foreach($document->categories as $index => $category)
                        <article class="rounded-2xl border border-[#e7eaf0] bg-[#f6f8fb]/40 p-5">
                            <header class="flex items-start justify-between gap-4 mb-4">
                                <div class="min-w-0">
                                    <input type="hidden" name="findings[{{ $index }}][category_id]" value="{{ $category->id }}">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#c99a3e]">Regulation #{{ $index + 1 }}</p>
                                    <h4 class="mt-1 text-base font-bold text-[#071833]">{{ $category->name }}</h4>
                                    @if($category->description)
                                        <p class="mt-1 text-xs text-[#667085]">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </header>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-[#071833] mb-2">Compliance Status</label>
                                    <select name="findings[{{ $index }}][compliance_status]" class="select-premium">
                                        <option value="">Select status…</option>
                                        @foreach($complianceStatuses as $status)
                                            <option value="{{ $status->value }}" {{ old("findings.$index.compliance_status") === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error("findings.$index.compliance_status")<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-[#071833] mb-2">Findings</label>
                                    <textarea name="findings[{{ $index }}][findings]" rows="3" class="input-premium input-textarea" placeholder="What did you find?">{{ old("findings.$index.findings") }}</textarea>
                                    @error("findings.$index.findings")<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-[#071833] mb-2">Recommendations</label>
                                    <textarea name="findings[{{ $index }}][recommendations]" rows="3" class="input-premium input-textarea" placeholder="Suggested remediation or next steps…">{{ old("findings.$index.recommendations") }}</textarea>
                                    @error("findings.$index.recommendations")<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-card>

        <div class="flex flex-col sm:flex-row gap-3">
            <x-button type="submit" variant="primary" size="lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                Submit Review
            </x-button>
            <x-button href="{{ route('review-documents.show', $document) }}" variant="outline" size="lg">Cancel</x-button>
        </div>
    </form>
@endsection
