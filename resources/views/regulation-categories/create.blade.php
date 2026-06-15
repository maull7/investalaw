@extends('layouts.app')

@section('title', 'Create Category')
@section('header', 'Create Category')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card>
                <x-slot name="header">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Master Data</p>
                        <h3 class="mt-1 text-xl font-bold text-[#071833]">Create Regulation Category</h3>
                        <p class="text-sm text-[#667085] mt-1">Add a new regulatory framework to use during compliance reviews.</p>
                    </div>
                </x-slot>

                <form method="POST" action="{{ route('regulation-categories.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#071833] mb-2">Name <span class="text-[#c99a3e]">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-premium" placeholder="e.g. OJK Regulation No. 33/POJK.04/2014">
                        @error('name')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-[#071833] mb-2">Description</label>
                        <textarea name="description" id="description" rows="4" class="input-premium input-textarea" placeholder="Briefly describe the scope of this regulation…">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-[#e7eaf0]">
                        <x-button type="submit" variant="primary" size="lg">Create Category</x-button>
                        <x-button href="{{ route('regulation-categories.index') }}" variant="outline" size="lg">Cancel</x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <aside>
            <x-card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-[#071833]">Good Practice</h3>
                </x-slot>
                <p class="text-sm text-[#667085] leading-relaxed">Use a clear, descriptive name that includes the regulation number or short title. Detailed descriptions help reviewers select the correct context.</p>
            </x-card>
        </aside>
    </div>
@endsection
