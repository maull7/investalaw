<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAiSummary;
use App\Models\AiJobStatus;
use App\Models\AiSummary;
use App\Models\ReviewDocument;
use App\Models\TypePrompt;
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

        $types = TypePrompt::where('is_active', true)->orderBy('name')->get()
            ->mapWithKeys(fn (TypePrompt $type) => [
                $type->slug => [
                    'label' => $type->name,
                    'desc' => $type->description ?? 'Generate analisis tipe '.$type->name,
                ],
            ]);

        return view('ai-summaries.index', [
            'document' => $reviewDocument,
            'summaries' => $reviewDocument->aiSummaries->keyBy('type'),
            'types' => $types,
        ]);
    }

    public function generate(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        $request->validate(['type' => ['required', 'string', 'exists:type_prompts,slug']]);

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

        AiJobStatus::begin($reviewDocument, 'summary:'.$type);
        GenerateAiSummary::dispatch($reviewDocument, $type);

        return redirect()->route('ai-summaries.index', $reviewDocument)
            ->with('info', 'AI Summary sedang diproses di background. Halaman akan refresh otomatis saat selesai.');
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
