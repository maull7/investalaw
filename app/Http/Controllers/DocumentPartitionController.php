<?php

namespace App\Http\Controllers;

use App\Models\DocumentBabStructure;
use App\Models\DocumentPartition;
use App\Models\PartitionAnalysis;
use App\Models\ReviewDocument;
use App\Services\AiService;
use App\Services\BabStructureService;
use App\Services\DocumentParser;
use App\Services\PartitionService;
use App\Services\TocExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentPartitionController extends Controller
{
    public function __construct(
        private readonly PartitionService $partitionService,
        private readonly BabStructureService $babStructureService,
        private readonly DocumentParser $documentParser,
        private readonly AiService $aiService,
        private readonly TocExtractorService $tocExtractorService
    ) {}

    public function index(ReviewDocument $reviewDocument): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        $totalPages = $this->partitionService->ensureTotalPages($reviewDocument);

        $reviewDocument->load([
            'partitions' => fn ($q) => $q->roots(),
            'partitions.analysis',
            'regulations',
        ]);

        $babTree = $this->babStructureService->getTree($reviewDocument);

        $parsedBabIds = collect($babTree)->filter(fn ($b) => ! empty($b['children']))->pluck('id')->values()->toArray();

        $allParsed = ! empty($babTree) && collect($babTree)->every(fn ($b) => ! empty($b['children']));

        return view('partitions.index', [
            'document' => $reviewDocument,
            'totalPages' => $totalPages,
            'babTree' => $babTree,
            'parsedBabIds' => $parsedBabIds,
            'allParsed' => $allParsed,
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
            'partitions.*.has_toc' => ['nullable', 'boolean'],
            'partitions.*.children' => ['nullable', 'array'],
            'partitions.*.children.*.name' => ['required', 'string', 'max:255'],
            'partitions.*.children.*.start_page' => ['required', 'integer', 'min:1'],
            'partitions.*.children.*.end_page' => ['required', 'integer', 'min:1'],
            'partitions.*.children.*.description' => ['nullable', 'string'],
            'partitions.*.children.*.children' => ['nullable', 'array'],
            'partitions.*.children.*.children.*.name' => ['required', 'string', 'max:255'],
            'partitions.*.children.*.children.*.start_page' => ['required', 'integer', 'min:1'],
            'partitions.*.children.*.children.*.end_page' => ['required', 'integer', 'min:1'],
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

    public function extractToc(Request $request, ReviewDocument $reviewDocument, DocumentPartition $documentPartition): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        if (! $documentPartition->has_toc) {
            return redirect()->route('partitions.index', $reviewDocument)
                ->with('error', 'Partisi ini tidak dicentang sebagai Daftar Isi.');
        }

        try {
            $toc = $this->tocExtractorService->extractToc($documentPartition);

            if ($toc->isEmpty()) {
                return redirect()->route('partitions.index', $reviewDocument)
                    ->with('error', 'Tidak dapat menemukan struktur Daftar Isi pada partisi ini.');
            }

            $this->babStructureService->saveTocChildren($documentPartition, $toc->toArray());

            return redirect()->route('partitions.index', $reviewDocument)
                ->with('success', 'Daftar Isi berhasil diekstrak: '.$toc->count().' Bab, '.$toc->sum(fn ($b) => count($b['children'])).' Subbab.');
        } catch (\Exception $e) {
            return redirect()->route('partitions.index', $reviewDocument)
                ->with('error', 'Gagal ekstrak Daftar Isi: '.$e->getMessage());
        }
    }

    public function generateAnalysis(Request $request, ReviewDocument $reviewDocument): RedirectResponse
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

        $query = $reviewDocument->partitions()
            ->whereNull('parent_id')
            ->when($partitionIds, fn ($q) => $q->whereIn('id', $partitionIds));

        if ($query->count() === 0) {
            return redirect()->route('partitions.index', $reviewDocument)
                ->with('error', 'Tidak ada partisi yang dipilih.');
        }

        try {
            $this->aiService->generateAllPartitionAnalyses($reviewDocument, $type, $partitionIds);

            $count = $partitionIds ? count($partitionIds) : $query->count();

            return redirect()->route('partitions.index', $reviewDocument)
                ->with('success', "Analisa AI selesai ({$count} partisi).");
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

    public function debugToc(ReviewDocument $reviewDocument, DocumentPartition $documentPartition): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        $text = $this->documentParser->extractPagesFromStoragePath(
            $reviewDocument->file_path,
            $documentPartition->start_page,
            min($documentPartition->end_page, $documentPartition->start_page + 20)
        );

        $rawByNewline = explode("\n", $text);
        $entries = $this->splitTocEntries($text);

        $normalized = array_values(array_filter(array_map(function ($l) {
            $line = preg_replace('/[\x00-\x1F\x7F]/u', '', $l);
            $line = str_replace(["\t", "\r"], ' ', $line);
            $line = preg_replace('/[\.]{3,}\s*/', '||', $line);
            $line = preg_replace('/_{4,}/', '||', $line);
            $line = preg_replace('/\s{2,}/', ' ', $line);
            $line = preg_replace('/^[\s\.]+|[\s\.]+$/u', '', $line);

            return trim($line);
        }, $entries)));

        $babMatches = [];
        foreach ($normalized as $i => $line) {
            if (preg_match('/^BAB\s+([IVXLCDM]+|\d+)\s*[\.\):–\-]?\s+(.+)$/i', $line)) {
                $babMatches[] = ['line' => $i + 1, 'text' => $line];
            }
        }

        $entryMatches = [];
        foreach ($normalized as $i => $line) {
            if (preg_match('/^(?:BAB|LAMPIRAN)\s+[IVXLCDM]+\s*:?\s*/i', $line)) {
                $entryMatches[] = ['line' => $i + 1, 'text' => $line];
            }
        }

        return view('partitions.debug-toc', [
            'document' => $reviewDocument,
            'partition' => $documentPartition,
            'rawText' => $text,
            'rawByNewline' => $rawByNewline,
            'entries' => $entries,
            'normalized' => $normalized,
            'babMatches' => $babMatches,
            'entryMatches' => $entryMatches,
        ]);
    }

    private function splitTocEntries(string $text): array
    {
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
        $text = str_replace(["\t", "\r"], ' ', $text);

        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn ($l) => $l !== ''));

        if (count($lines) >= 3) {
            return $lines;
        }

        $entries = [];
        $pattern = '/\s+(?=(?:BAB|LAMPIRAN)\s+[IVXLCDM]+\s*:)/i';

        foreach ($lines as $line) {
            $parts = preg_split($pattern, $line, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $entries[] = $part;
                }
            }
        }

        return $entries;
    }

    public function parsePdf(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        set_time_limit(300);

        try {
            $this->documentParser->extractAndCachePages($reviewDocument);

            return redirect()->route('partitions.parsed-text', $reviewDocument)
                ->with('success', 'PDF berhasil diparse dan disimpan ke database ('.number_format($reviewDocument->pages()->count()).' halaman).');
        } catch (\Exception $e) {
            return redirect()->route('partitions.parsed-text', $reviewDocument)
                ->with('error', 'Gagal parse PDF: '.$e->getMessage());
        }
    }

    public function detectStructure(Request $request, ReviewDocument $reviewDocument, DocumentBabStructure $documentBabStructure): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        if ($documentBabStructure->level !== 0) {
            return redirect()->route('partitions.parsed-text', $reviewDocument)
                ->with('error', 'Deteksi struktur hanya untuk BAB level (level=0).');
        }

        set_time_limit(300);

        try {
            $subsections = $this->aiService->detectContentStructure($documentBabStructure);

            if (empty($subsections)) {
                return redirect()->route('partitions.parsed-text', $reviewDocument)
                    ->with('error', 'AI tidak dapat mendeteksi struktur sub-bab pada "'.$documentBabStructure->name.'". Mungkin format konten tidak dikenali.');
            }

            $subsections = $this->babStructureService->resolveChildPages($documentBabStructure, $subsections);
            $this->babStructureService->saveStructureChildren($documentBabStructure, $subsections, 1);

            $totalItems = collect($subsections)->sum(fn ($s) => count($s['items'] ?? []));

            return redirect()->route('partitions.parsed-text', $reviewDocument)
                ->with('success', 'Struktur terdeteksi: '.count($subsections).' Subbab, '.$totalItems.' Isi pada "'.$documentBabStructure->name.'".');
        } catch (\Exception $e) {
            return redirect()->route('partitions.parsed-text', $reviewDocument)
                ->with('error', 'Gagal deteksi struktur AI: '.$e->getMessage());
        }
    }

    public function batchDetectStructure(Request $request, ReviewDocument $reviewDocument): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        $validated = $request->validate([
            'bab_ids' => ['required', 'array', 'min:1', 'max:5'],
            'bab_ids.*' => ['required', 'integer', 'exists:document_bab_structures,id'],
        ]);

        set_time_limit(300);

        $babs = DocumentBabStructure::whereIn('id', $validated['bab_ids'])
            ->where('review_document_id', $reviewDocument->id)
            ->where('level', 0)
            ->orderBy('sort_order')
            ->get();

        if ($babs->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada BAB valid yang dipilih.');
        }

        $success = [];
        $errors = [];

        foreach ($babs as $bab) {
            try {
                $subsections = $this->aiService->detectContentStructure($bab);

                if (empty($subsections)) {
                    $errors[] = "{$bab->name}: AI tidak dapat mendeteksi struktur";

                    continue;
                }

                $subsections = $this->babStructureService->resolveChildPages($bab, $subsections);
                $this->babStructureService->saveStructureChildren($bab, $subsections, 1);

                $totalItems = collect($subsections)->sum(fn ($s) => count($s['items'] ?? []));
                $success[] = "{$bab->name}: ".count($subsections)." Subbab, {$totalItems} Isi";
            } catch (\Exception $e) {
                $errors[] = "{$bab->name}: {$e->getMessage()}";
            }
        }

        $message = '';
        if ($success) {
            $message .= 'Berhasil: '.implode('; ', $success).'. ';
        }
        if ($errors) {
            $message .= 'Gagal: '.implode('; ', $errors);
        }

        $message = trim($message);

        return redirect()->back()->with(
            $errors && ! $success ? 'error' : 'success',
            $message
        );
    }

    public function detectStructureAjax(Request $request, ReviewDocument $reviewDocument, DocumentBabStructure $documentBabStructure): JsonResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);

        if ($documentBabStructure->level !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Deteksi struktur hanya untuk BAB level (level=0).',
            ]);
        }

        set_time_limit(300);

        try {
            $subsections = $this->aiService->detectContentStructure($documentBabStructure);

            if (empty($subsections)) {
                DocumentBabStructure::create([
                    'review_document_id' => $documentBabStructure->review_document_id,
                    'parent_id' => $documentBabStructure->id,
                    'name' => '—',
                    'start_page' => $documentBabStructure->start_page,
                    'end_page' => $documentBabStructure->end_page,
                    'sort_order' => 1,
                    'level' => 1,
                ]);

                return response()->json([
                    'success' => true,
                    'detected' => false,
                    'message' => 'Sub-bab tidak terdeteksi pada "'.$documentBabStructure->name.'".',
                ]);
            }

            $subsections = $this->babStructureService->resolveChildPages($documentBabStructure, $subsections);
            $this->babStructureService->saveStructureChildren($documentBabStructure, $subsections, 1);

            $totalItems = collect($subsections)->sum(fn ($s) => count($s['items'] ?? []));

            return response()->json([
                'success' => true,
                'detected' => true,
                'message' => count($subsections).' Subbab, '.$totalItems.' Isi pada "'.$documentBabStructure->name.'".',
                'data' => [
                    'bab_id' => $documentBabStructure->id,
                    'bab_name' => $documentBabStructure->name,
                    'total_subsections' => count($subsections),
                    'total_items' => $totalItems,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal deteksi struktur AI: '.$e->getMessage(),
            ]);
        }
    }

    public function showParsedText(ReviewDocument $reviewDocument): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);

        $reviewDocument->loadMissing(['regulations.documents']);

        $isParsed = $reviewDocument->isParsed();

        if (! $isParsed) {
            $allBabsHaveStructure = DocumentBabStructure::query()
                ->where('review_document_id', $reviewDocument->id)
                ->whereNull('parent_id')
                ->whereDoesntHave('children')
                ->doesntExist();

            if ($allBabsHaveStructure) {
                $this->documentParser->extractAndCachePages($reviewDocument);
                $isParsed = true;
            }
        }

        if ($isParsed) {
            $docPages = $reviewDocument->pages()
                ->orderBy('page_number')
                ->get()
                ->map(fn ($p) => [
                    'page' => $p->page_number,
                    'text' => $p->content,
                    'char_count' => $p->char_count,
                ])
                ->toArray();
        } else {
            set_time_limit(300);
            $docPages = $this->documentParser->extractAllPagesText($reviewDocument->file_path);
        }

        $docTotalChars = array_sum(array_column($docPages, 'char_count'));

        $babs = DocumentBabStructure::query()
            ->where('review_document_id', $reviewDocument->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->ordered(), 'children.children' => fn ($q) => $q->ordered()])
            ->get();

        $babGroups = [];
        $unassignedPages = [];

        foreach ($docPages as $pageData) {
            $pageNum = (int) $pageData['page'];
            $matched = $babs->first(fn ($bab) => $pageNum >= $bab->start_page && $pageNum <= $bab->end_page);

            if ($matched) {
                $key = $matched->id;
                if (! isset($babGroups[$key])) {
                    $babGroups[$key] = ['bab' => $matched, 'pages' => []];
                }
                $babGroups[$key]['pages'][] = $pageData;
            } else {
                $unassignedPages[] = $pageData;
            }
        }

        $babGroups = collect($babGroups);

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
            'babs' => $babs,
            'babGroups' => $babGroups,
            'unassignedPages' => $unassignedPages,
            'regulations' => $regulations,
            'isParsed' => $isParsed,
        ]);
    }
}
