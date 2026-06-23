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
            'partitions' => fn ($q) => $q->roots(),
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
            'partition_ids' => ['nullable', 'array'],
            'partition_ids.*' => ['integer', 'exists:document_partitions,id'],
        ]);

        $type = $request->input('type');
        $partitionIds = $request->input('partition_ids');

        try {
            $this->aiService->generateSummary($reviewDocument, $type);

            $selectedPartitions = $reviewDocument->partitions()
                ->when($partitionIds, fn ($q) => $q->whereIn('id', $partitionIds))
                ->count();

            if ($selectedPartitions > 0) {
                $this->aiService->generateAllPartitionAnalyses($reviewDocument, $type, $partitionIds);
            }

            $count = $partitionIds ? count($partitionIds) : $reviewDocument->partitions()->count();
            $msg = 'AI Preview berhasil digenerate'.($count > 0 ? " ({$count} partisi dianalisa)" : '');

            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $type])
                ->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $type])
                ->with('error', 'Gagal generate AI: '.$e->getMessage());
        }
    }
}
