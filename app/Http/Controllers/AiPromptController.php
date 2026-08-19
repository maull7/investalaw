<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\TypePrompt;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AiPromptController extends Controller
{
    public function index(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        $prompts = AiPrompt::with('typePrompt')->orderBy('type')->get();

        return view('ai-prompts.index', compact('prompts'));
    }

    public function create(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        $typePrompts = TypePrompt::orderBy('name')->get();

        return view('ai-prompts.create', compact('typePrompts'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin() && ! $request->user()->hasPermission('manage_prompts'), 403);

        $validated = $request->validate([
            'type_prompt_id' => ['required', 'exists:type_prompts,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'prompt_text' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $typePrompt = TypePrompt::findOrFail($validated['type_prompt_id']);
        $slug = Str::slug($validated['title']);

        $prompt = AiPrompt::create([
            'type_prompt_id' => $typePrompt->id,
            'type' => $slug,
            'title' => $validated['title'] ?? null,
            'prompt_text' => $validated['prompt_text'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        UserActivityLog::log('created', AiPrompt::class, $prompt->id, "Menambahkan prompt AI {$prompt->type}");

        return redirect()->route('ai-prompts.index')
            ->with('success', 'Prompt berhasil ditambahkan.');
    }

    public function edit(AiPrompt $aiPrompt): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        $typePrompts = TypePrompt::where('is_active', true)->orderBy('name')->get();

        return view('ai-prompts.edit', compact('aiPrompt', 'typePrompts'));
    }

    public function update(Request $request, AiPrompt $aiPrompt): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin() && ! $request->user()->hasPermission('manage_prompts'), 403);

        $validated = $request->validate([
            'type_prompt_id' => ['required', 'exists:type_prompts,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'prompt_text' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $typePrompt = TypePrompt::findOrFail($validated['type_prompt_id']);
        $slug = Str::slug($validated['title']);

        $aiPrompt->update([
            'type_prompt_id' => $typePrompt->id,
            'type' => $slug,
            'title' => $validated['title'] ?? null,
            'prompt_text' => $validated['prompt_text'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        UserActivityLog::log('updated', AiPrompt::class, $aiPrompt->id, "Memperbarui prompt AI {$aiPrompt->type}");

        return redirect()->route('ai-prompts.index')
            ->with('success', 'Prompt berhasil diperbarui.');
    }

    public function destroy(AiPrompt $aiPrompt): RedirectResponse
    {
        abort_if(request()->user()->isSubAdmin() && ! request()->user()->hasPermission('manage_prompts'), 403);

        $type = $aiPrompt->type;
        $aiPrompt->delete();

        UserActivityLog::log('deleted', AiPrompt::class, null, "Menghapus prompt AI {$type}");

        return redirect()->route('ai-prompts.index')
            ->with('success', 'Prompt berhasil dihapus.');
    }
}
