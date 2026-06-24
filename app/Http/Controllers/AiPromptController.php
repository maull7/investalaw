<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiPromptController extends Controller
{
    public function index(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        $prompts = AiPrompt::orderBy('type')->get();

        return view('ai-prompts.index', compact('prompts'));
    }

    public function create(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('manage_prompts'), 403);

        return view('ai-prompts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin() && ! $request->user()->hasPermission('manage_prompts'), 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50', 'unique:ai_prompts,type'],
            'title' => ['nullable', 'string', 'max:255'],
            'prompt_text' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $prompt = AiPrompt::create([
            'type' => $validated['type'],
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

        return view('ai-prompts.edit', compact('aiPrompt'));
    }

    public function update(Request $request, AiPrompt $aiPrompt): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin() && ! $request->user()->hasPermission('manage_prompts'), 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50', 'unique:ai_prompts,type,'.$aiPrompt->id],
            'title' => ['nullable', 'string', 'max:255'],
            'prompt_text' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $aiPrompt->update([
            'type' => $validated['type'],
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
