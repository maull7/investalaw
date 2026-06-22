<?php

namespace App\Http\Controllers;

use App\Models\DocumentPartition;
use App\Models\PartitionAnalysis;
use App\Models\ReviewDocument;
use App\Services\AiService;
use App\Services\DocumentParser;
use App\Services\PartitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentPartitionController extends Controller
{
    public function __construct(
        private readonly PartitionService $partitionService,
        private readonly DocumentParser $documentParser,
        private readonly AiService $aiService
    ) {}

    public function index(ReviewDocument $reviewDocument): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        $totalPages = $this->partitionService->ensureTotalPages($reviewDocument);

        $reviewDocument->load([
            'partitions' => fn ($q) => $q->ordered(),
            'partitions.analysis',
            'regulations',
        ]);

        return view('partitions.index', [
            'document' => $reviewDocument,
            'totalPages' => $totalPages,
        ]);
    }

    public function store(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        $request->validate([
            'partitions' => ['required', 'array', 'min:1'],
            'partitions.*.name' => ['required', 'string', 'max:255'],
            'partitions.*.start_page' => ['required', 'integer', 'min:1'],
            'partitions.*.end_page' => ['required', 'integer', 'min:1'],
            'partitions.*.description' => ['nullable', 'string'],
        ]);

        try {
            $this->partitionService->savePartitions($reviewDocument, $request->input('partitions'));

            return redirect()->route('partitions.index', $reviewDocument)
                ->with('success', 'Partisi dokumen berhasil disimpan.');
        } catch (ValidationException $e) {
            return redirect()->route('partitions.index', $reviewDocument)
                ->with('error', $e->getMessage());
        }
    }

    public function generateAnalysis(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        set_time_limit(300);

        $request->validate(['type' => ['required', 'string', 'in:analisa,review,rekomendasi,validitas']]);

        if ($reviewDocument->partitions()->count() === 0) {
            return redirect()->route('partitions.index', $reviewDocument)
                ->with('error', 'Belum ada partisi. Buat partisi terlebih dahulu.');
        }

        try {
            $this->aiService->generateAllPartitionAnalyses($reviewDocument, $request->input('type'));

            return redirect()->route('partitions.index', $reviewDocument)
                ->with('success', 'Analisa AI per-partisi selesai.');
        } catch (\Exception $e) {
            return redirect()->route('partitions.index', $reviewDocument)
                ->with('error', 'Gagal menjalankan analisa AI: '.$e->getMessage());
        }
    }

    public function saveAnalysis(Request $request, ReviewDocument $reviewDocument, DocumentPartition $documentPartition): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        $validated = $request->validate([
            'summary' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
            'compliance_status' => ['nullable', 'string', 'in:compliant,partially_compliant,non_compliant'],
        ]);

        PartitionAnalysis::updateOrCreate(
            ['document_partition_id' => $documentPartition->id],
            [
                'review_document_id' => $reviewDocument->id,
                'summary' => $validated['summary'] ?? null,
                'findings' => $validated['findings'] ?? null,
                'compliance_status' => $validated['compliance_status'] ?? null,
            ]
        );

        return redirect()->route('partitions.index', $reviewDocument)
            ->with('success', "Analisa partisi \"{$documentPartition->name}\" berhasil disimpan.");
    }

    public function showParsedText(ReviewDocument $reviewDocument): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        $reviewDocument->loadMissing('regulations.documents');

        $docPages = $this->documentParser->extractAllPagesText($reviewDocument->file_path);
        $docTotalChars = array_sum(array_column($docPages, 'char_count'));

        $regulations = [];
        foreach ($reviewDocument->regulations as $reg) {
            $regData = [
                'regulation_number' => $reg->regulation_number,
                'title' => $reg->title,
                'year' => $reg->year,
                'main_text' => '',
                'main_chars' => 0,
                'documents' => [],
            ];

            if ($reg->file_path) {
                $text = $this->documentParser->extractFromStoragePath($reg->file_path);
                $regData['main_text'] = $text;
                $regData['main_chars'] = mb_strlen($text);
            }

            foreach ($reg->documents as $doc) {
                $text = $this->documentParser->extractFromStoragePath($doc->file_path);
                $regData['documents'][] = [
                    'name' => $doc->name,
                    'text' => $text,
                    'chars' => mb_strlen($text),
                ];
            }

            $regulations[] = $regData;
        }

        return view('partitions.parsed-text', [
            'document' => $reviewDocument,
            'docPages' => $docPages,
            'docTotalChars' => $docTotalChars,
            'regulations' => $regulations,
        ]);
    }
}
