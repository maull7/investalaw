<?php

namespace App\Http\Controllers;

use App\Models\AiSummary;
use App\Models\ReviewDocument;
use App\Services\AiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSummaryController extends Controller
{
    public function __construct(
        private readonly AiService $aiService
    ) {}

    public function index(ReviewDocument $reviewDocument): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        $reviewDocument->load('aiSummaries');

        return view('ai-summaries.index', [
            'document' => $reviewDocument,
            'summaries' => $reviewDocument->aiSummaries->keyBy('type'),
        ]);
    }

    public function generate(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        $request->validate(['type' => ['required', 'string', 'in:analisa,review,rekomendasi,validitas']]);

        $type = $request->input('type');

        if (! $reviewDocument->isParsed()) {
            return redirect()->route('ai-summaries.index', $reviewDocument)
                ->with('error', 'Dokumen belum di-parse. Silakan lakukan Parse PDF terlebih dahulu di menu Partisi.');
        }

        $reviewDocument->load('regulations');
        $unparsedRegs = $reviewDocument->regulations->reject(fn ($r) => $r->isParsed());
        if ($unparsedRegs->isNotEmpty()) {
            return redirect()->route('ai-summaries.index', $reviewDocument)
                ->with('error', 'Regulasi berikut belum diparse: '.$unparsedRegs->pluck('regulation_number')->implode(', ').'. Parse terlebih dahulu di menu Regulasi.');
        }

        try {
            $summary = $this->aiService->generateSummary($reviewDocument, $type);

            return redirect()->route('ai-summaries.show', [$reviewDocument, $summary])
                ->with('success', 'AI Summary berhasil digenerate menggunakan '.$summary->provider_used.'.');
        } catch (\Exception $e) {
            return redirect()->route('ai-summaries.index', $reviewDocument)
                ->with('error', 'Gagal generate AI Summary: '.$e->getMessage());
        }
    }

    public function show(ReviewDocument $reviewDocument, AiSummary $aiSummary): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        return view('ai-summaries.show', [
            'document' => $reviewDocument,
            'summary' => $aiSummary,
        ]);
    }

    public function checkPrompt(ReviewDocument $reviewDocument, AiSummary $aiSummary): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        return view('ai-summaries.check-prompt', [
            'document' => $reviewDocument,
            'summary' => $aiSummary,
        ]);
    }
}
