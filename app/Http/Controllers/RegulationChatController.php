<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use App\Models\RegulationChatMessage;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegulationChatController extends Controller
{
    public function __construct(
        private readonly AiService $aiService,
    ) {}

    public function ask(Request $request, Regulation $regulation): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
        ]);

        $userMessage = RegulationChatMessage::create([
            'user_id' => $request->user()->id,
            'regulation_id' => $regulation->id,
            'role' => 'user',
            'content' => $validated['question'],
        ]);

        $history = RegulationChatMessage::where('regulation_id', $regulation->id)
            ->where('user_id', $request->user()->id)
            ->where('id', '<', $userMessage->id)
            ->latest()
            ->limit(6)
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        try {
            $reply = $this->aiService->askRegulation($regulation, $validated['question'], $history, $request->user());

            RegulationChatMessage::create([
                'user_id' => $request->user()->id,
                'regulation_id' => $regulation->id,
                'role' => 'assistant',
                'content' => $reply,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['reply' => $reply]);
            }

            return redirect()->route('regulations.show', [$regulation, 'tab' => 'vesa'])
                ->with('success', 'Jawaban Kak Vesa berhasil diproses.');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Maaf, Kak Vesa sedang tidak dapat dihubungi. Coba lagi beberapa saat.'], 500);
            }

            return redirect()->route('regulations.show', [$regulation, 'tab' => 'vesa'])
                ->with('error', 'Maaf, Kak Vesa sedang tidak dapat dihubungi. Coba lagi beberapa saat.');
        }
    }
}
