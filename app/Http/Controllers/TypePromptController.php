<?php

namespace App\Http\Controllers;

use App\Models\TypePrompt;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TypePromptController extends Controller
{
    public function index(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        $types = TypePrompt::withCount('prompts')->orderBy('name')->get();

        return view('type-prompts.index', compact('types'));
    }

    public function create(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        return view('type-prompts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin() && ! $request->user()->hasPermission('manage_prompts'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $type = TypePrompt::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        UserActivityLog::log('created', TypePrompt::class, $type->id, "Menambahkan type prompt {$type->name}");

        return redirect()->route('type-prompts.index')
            ->with('success', 'Type prompt berhasil ditambahkan.');
    }

    public function edit(TypePrompt $typePrompt): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        return view('type-prompts.edit', compact('typePrompt'));
    }

    public function update(Request $request, TypePrompt $typePrompt): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin() && ! $request->user()->hasPermission('manage_prompts'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $typePrompt->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $typePrompt->id),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        UserActivityLog::log('updated', TypePrompt::class, $typePrompt->id, "Memperbarui type prompt {$typePrompt->name}");

        return redirect()->route('type-prompts.index')
            ->with('success', 'Type prompt berhasil diperbarui.');
    }

    public function destroy(TypePrompt $typePrompt): RedirectResponse
    {
        abort_if(request()->user()->isSubAdmin() && ! request()->user()->hasPermission('manage_prompts'), 403);

        $name = $typePrompt->name;
        $typePrompt->delete();

        UserActivityLog::log('deleted', TypePrompt::class, null, "Menghapus type prompt {$name}");

        return redirect()->route('type-prompts.index')
            ->with('success', 'Type prompt berhasil dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (TypePrompt::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
