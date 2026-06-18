<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\AiSummary;
use App\Models\ReviewDocument;
use App\Services\AiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiPreviewController extends Controller
{
    public function __construct(
        private readonly AiService $aiService
    ) {}

    public function show(Request $request, ReviewDocument $reviewDocument): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        $selectedType = $request->query('type', 'analisa');
        $prompts = AiPrompt::active()->get();

        $summary = AiSummary::where('review_document_id', $reviewDocument->id)
            ->where('type', $selectedType)
            ->latest()
            ->first();

        return view('ai-preview.show', [
            'document' => $reviewDocument,
            'prompts' => $prompts,
            'selectedType' => $selectedType,
            'summary' => $summary,
        ]);
    }

    public function generate(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        $request->validate([
            'type' => ['required', 'string', 'in:analisa,review,rekomendasi,validitas'],
        ]);

        try {
            $this->aiService->generateSummary($reviewDocument, $request->input('type'));

            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $request->input('type')])
                ->with('success', 'AI Preview berhasil digenerate.');
        } catch (\Exception $e) {
            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $request->input('type')])
                ->with('error', 'Gagal generate AI Preview: '.$e->getMessage());
        }
    }
}
