<?php

namespace App\Http\Controllers;

use App\Models\ConsultationChatMessage;
use App\Models\ConsultationSession;
use App\Models\RegulationCategory;
use App\Models\Setting;
use App\Models\UserPackage;
use App\Services\AiService;
use App\Services\TokenLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly AiService $aiService,
        private readonly TokenLimitService $tokenLimit,
    ) {}

    private function requireKakVestaAccess(): ?RedirectResponse
    {
        if (auth()->user()->isAdmin() || auth()->user()->isSubAdmin()) {
            return null;
        }

        $active = UserPackage::where('user_id', auth()->id())
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $active) {
            return redirect()->route('dashboard')
                ->with('error', 'Fitur Konsultasi Kak Vesta hanya tersedia untuk pengguna dengan paket aktif.');
        }

        if ($active->type === 'paid') {
            return null;
        }

        if (! $active->kak_vesta_started_at) {
            $active->update(['kak_vesta_started_at' => now()]);

            return null;
        }

        $cap = (int) Setting::get('trial_max_hours', 48);
        $hours = (int) $active->package?->duration_hours ?: $cap;
        $allowedUntil = $active->kak_vesta_started_at->addHours(min($hours, $cap));

        if ($allowedUntil->lte(now())) {
            return redirect()->route('dashboard')
                ->with('error', 'Masa aktif trial konsultasi Kak Vesta Anda telah berakhir. Upgrade ke paket berbayar untuk melanjutkan.');
        }

        return null;
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $sessions = ConsultationSession::where('user_id', auth()->id())
            ->withCount('regulations')
            ->latest()
            ->get();

        $categories = RegulationCategory::with(['regulations' => function ($q) {
            $q->whereHas('documents');
        }])->orderBy('name')->get();

        return view('consultations.index', compact('sessions', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'regulation_ids' => ['required', 'array', 'min:1', 'max:10'],
            'regulation_ids.*' => ['integer', 'exists:regulations,id'],
        ]);

        $session = ConsultationSession::create([
            'user_id' => $request->user()->id,
            'title' => 'Konsultasi '.Carbon::now()->format('d M Y, H:i'),
        ]);

        $session->regulations()->attach($validated['regulation_ids']);

        return redirect()->route('consultations.show', $session)
            ->with('success', 'Sesi konsultasi dibuat. Silakan mulai bertanya.');
    }

    public function show(ConsultationSession $session): View|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            Log::warning("Consultation show 403: session_user={$session->user_id} auth_user=".auth()->id()." session_id={$session->id}");
            abort(403);
        }

        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $session->load(['messages', 'regulations']);

        $categories = RegulationCategory::with(['regulations' => function ($q) {
            $q->whereHas('documents');
        }])->orderBy('name')->get();

        $selectedIds = $session->regulations->pluck('id')->all();

        return view('consultations.show', compact('session', 'categories', 'selectedIds'));
    }

    public function ask(Request $request, ConsultationSession $session): JsonResponse|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            Log::warning("Consultation ask 403: session_user={$session->user_id} auth_user=".auth()->id());
            abort(403);
        }

        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
        ]);

        ConsultationChatMessage::create([
            'consultation_session_id' => $session->id,
            'user_id' => $request->user()->id,
            'role' => 'user',
            'content' => $validated['question'],
        ]);

        $history = ConsultationChatMessage::where('consultation_session_id', $session->id)
            ->where('user_id', $request->user()->id)
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

            return redirect()->route('consultations.show', $session)
                ->with('error', "Batas token harian ({$daily}) tercapai. Coba lagi besok.");
        }

        try {
            $result = $this->aiService->askConsultation($session, $validated['question'], $history, $request->user());
            $reply = $result['content'];

            $this->tokenLimit->record(
                $request->user()->id,
                $result['total_tokens'] ?? 0,
                'consultation_chat',
                $session->id
            );

            ConsultationChatMessage::create([
                'consultation_session_id' => $session->id,
                'user_id' => $request->user()->id,
                'role' => 'assistant',
                'content' => $reply,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['reply' => $reply]);
            }

            return redirect()->route('consultations.show', $session)
                ->with('success', 'Jawaban Kak Vesa berhasil diproses.');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Maaf, Kak Vesta sedang tidak dapat dihubungi. Coba lagi beberapa saat.'], 500);
            }

            return redirect()->route('consultations.show', $session)
                ->with('error', 'Maaf, Kak Vesta sedang tidak dapat dihubungi. Coba lagi beberapa saat.');
        }
    }

    public function addRegulations(Request $request, ConsultationSession $session): RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            Log::warning("Consultation addReg 403: session_user={$session->user_id} auth_user=".auth()->id());
            abort(403);
        }

        $currentCount = $session->regulations()->count();

        $validated = $request->validate([
            'regulation_ids' => ['required', 'array', 'min:1'],
            'regulation_ids.*' => ['integer', 'exists:regulations,id'],
        ]);

        $newTotal = $currentCount + count($validated['regulation_ids']);

        if ($newTotal > 10) {
            return redirect()->route('consultations.show', $session)
                ->with('error', "Maksimal 10 regulasi per sesi. Saat ini sudah ada {$currentCount} regulasi.");
        }

        $session->regulations()->syncWithoutDetaching($validated['regulation_ids']);

        return redirect()->route('consultations.show', $session)
            ->with('success', 'Regulasi berhasil ditambahkan ke sesi konsultasi.');
    }
}
