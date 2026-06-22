<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\AiSummary;
use App\Models\DocumentParsedText;
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

        $reviewDocument->load([
            'regulations.documents',
            'regulations.type',
            'partitions' => fn ($q) => $q->ordered(),
            'partitions.analysis' => fn ($q) => $q->where('type', $selectedType),
        ]);

        $summary = AiSummary::where('review_document_id', $reviewDocument->id)
            ->where('type', $selectedType)
            ->latest()
            ->first();

        $activePrompt = AiPrompt::active()->where('type', $selectedType)->first();

        // Load parsed texts from DB (saved during generate)
        $parsedTexts = DocumentParsedText::where('review_document_id', $reviewDocument->id)
            ->orderBy('source_type')
            ->orderBy('page')
            ->get();

        return view('ai-preview.show', [
            'document' => $reviewDocument,
            'prompts' => $prompts,
            'selectedType' => $selectedType,
            'summary' => $summary,
            'activePrompt' => $activePrompt,
            'parsedTexts' => $parsedTexts,
        ]);
    }

    public function generate(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        set_time_limit(300);

        $request->validate([
            'type' => ['required', 'string', 'in:analisa,review,rekomendasi,validitas'],
        ]);

        $type = $request->input('type');

        try {
            $this->aiService->generateSummary($reviewDocument, $type);

            if ($reviewDocument->partitions()->exists()) {
                $this->aiService->generateAllPartitionAnalyses($reviewDocument, $type);
            }

            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $type])
                ->with('success', 'AI Preview dan analisa per-partisi berhasil digenerate.');
        } catch (\Exception $e) {
            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $type])
                ->with('error', 'Gagal generate AI: '.$e->getMessage());
        }
    }
}
