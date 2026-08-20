<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use App\Models\RegulationChatMessage;
use App\Services\AiService;
use App\Services\TokenLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegulationChatController extends Controller
{
    public function __construct(
        private readonly AiService $aiService,
        private readonly TokenLimitService $tokenLimit,
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

        if (! $this->tokenLimit->canSend($request->user()->id)) {
            $remaining = $this->tokenLimit->remaining($request->user()->id);
            $daily = $this->tokenLimit->dailyLimit();

            if ($request->wantsJson()) {
                return response()->json(['message' => "Batas token harian ({$daily}) tercapai. Tersisa {$remaining} token. Coba lagi besok."], 429);
            }

            return redirect()->route('regulations.show', [$regulation, 'tab' => 'vesa'])
                ->with('error', "Batas token harian ({$daily}) tercapai. Coba lagi besok.");
        }

        try {
            $result = $this->aiService->askRegulation($regulation, $validated['question'], $history, $request->user());
            $reply = $result['content'];

            $this->tokenLimit->record(
                $request->user()->id,
                $result['total_tokens'] ?? 0,
                'regulation_chat',
                $regulation->id
            );

            RegulationChatMessage::create([
                'user_id' => $request->user()->id,
                'regulation_id' => $regulation->id,
                'role' => 'assistant',
                'content' => $reply,
                'citations' => $result['citations'] ?? [],
                'confidence' => $result['confidence'] ?? 'low',
                'prompt_text' => $result['prompt_text'] ?? null,
                'provider_used' => $result['provider'] ?? null,
                'model_used' => $result['model'] ?? null,
                'context_hash' => $result['context_hash'] ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'reply' => $reply,
                    'citations' => $result['citations'] ?? [],
                    'confidence' => $result['confidence'] ?? 'low',
                ]);
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
