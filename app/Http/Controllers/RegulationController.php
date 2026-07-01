<?php

namespace App\Http\Controllers;

use App\Http\Requests\Regulation\StoreRegulationRequest;
use App\Http\Requests\Regulation\UpdateRegulationRequest;
use App\Jobs\ParseRegulationDocument;
use App\Models\Regulation;
use App\Models\RegulationDocument;
use App\Models\UserActivityLog;
use App\Repositories\RegulationRepository;
use App\Services\RegulationAnalysisService;
use App\Services\RegulationParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegulationController extends Controller
{
    public function __construct(
        private readonly RegulationRepository $regulationRepository,
        private readonly RegulationParserService $regulationParserService,
        private readonly RegulationAnalysisService $regulationAnalysisService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'year', 'type_id', 'category_id', 'sort', 'direction']);
        $regulations = $this->regulationRepository->paginateWithFilters($filters);
        $filterOptions = $this->regulationRepository->getFilterOptions();

        return view('regulations.index', compact('regulations', 'filterOptions', 'filters'));
    }

    public function create(): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('upload_regulations'), 403);

        $options = $this->regulationRepository->getFormOptions();

        return view('regulations.create', $options);
    }

    public function store(StoreRegulationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $filePath = $request->file('file')->store('regulations', 'public');

        $regulation = Regulation::create([
            'regulation_number' => $data['regulation_number'],
            'title' => $data['title'],
            'regulation_type_id' => $data['regulation_type_id'],
            'category_id' => $data['category_id'],
            'year' => $data['year'],
            'effective_date' => $data['effective_date'] ?? null,
            'file_path' => $filePath,
        ]);

        if (! empty($data['sub_categories'])) {
            $regulation->subCategories()->sync($data['sub_categories']);
        }

        if (! empty($data['related_regulations'])) {
            $regulation->relatedRegulations()->sync($data['related_regulations']);
        }

        UserActivityLog::log('created', Regulation::class, $regulation->id, "Menambahkan regulasi {$regulation->regulation_number} - {$regulation->title}");

        return redirect()->route('regulations.show', $regulation)
            ->with('success', 'Regulasi berhasil ditambahkan.');
    }

    public function show(Regulation $regulation): View
    {
        $regulation = $this->regulationRepository->findByIdWithRelations($regulation->id);

        return view('regulations.show', compact('regulation'));
    }

    public function edit(Regulation $regulation): View
    {
        abort_if(auth()->user()->isSubAdmin() && ! auth()->user()->hasPermission('upload_regulations'), 403);

        $options = $this->regulationRepository->getFormOptions();
        $regulation->load(['subCategories', 'relatedRegulations.type']);

        return view('regulations.edit', array_merge($options, compact('regulation')));
    }

    public function update(UpdateRegulationRequest $request, Regulation $regulation): RedirectResponse
    {
        $data = $request->validated();

        $updateData = [
            'regulation_number' => $data['regulation_number'],
            'title' => $data['title'],
            'regulation_type_id' => $data['regulation_type_id'],
            'category_id' => $data['category_id'],
            'year' => $data['year'],
            'effective_date' => $data['effective_date'] ?? null,
        ];

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($regulation->file_path);
            $updateData['file_path'] = $request->file('file')->store('regulations', 'public');
        }

        $regulation->update($updateData);
        $regulation->subCategories()->sync($data['sub_categories'] ?? []);
        $regulation->relatedRegulations()->sync($data['related_regulations'] ?? []);

        UserActivityLog::log('updated', Regulation::class, $regulation->id, "Memperbarui regulasi {$regulation->regulation_number} - {$regulation->title}");

        return redirect()->route('regulations.show', $regulation)
            ->with('success', 'Regulasi berhasil diperbarui.');
    }

    public function analyze(Regulation $regulation): View
    {
        $regulation = $this->regulationRepository->findByIdWithRelations($regulation->id);

        $analysis = $this->regulationAnalysisService->analyze($regulation);

        return view('regulations.analyze', compact('regulation', 'analysis'));
    }

    public function generateAnalysis(Regulation $regulation): RedirectResponse
    {
        $analysis = $this->regulationAnalysisService->generate($regulation);

        UserActivityLog::log('generated', Regulation::class, $regulation->id, "Menghasilkan analisis AI untuk regulasi {$regulation->regulation_number}");

        if (! $analysis) {
            return redirect()->route('regulations.analyze', $regulation)
                ->with('error', 'Gagal menghasilkan analisis.');
        }

        return redirect()->route('regulations.analyze', $regulation)
            ->with('success', 'Analisis berhasil dihasilkan. Klik "Simpan Analisis" untuk menyimpan hasil.');
    }

    public function saveAnalysis(Regulation $regulation): RedirectResponse
    {
        $result = $this->regulationAnalysisService->saveAnalysis($regulation);

        if (! $result) {
            return redirect()->route('regulations.analyze', $regulation)
                ->with('error', 'Tidak ada analisis untuk disimpan.');
        }

        UserActivityLog::log('saved', Regulation::class, $regulation->id, "Menyimpan analisis AI untuk regulasi {$regulation->regulation_number}");

        return redirect()->route('regulations.analyze', $regulation)
            ->with('success', 'Analisis berhasil disimpan.');
    }

    public function connectReferences(Request $request, Regulation $regulation): RedirectResponse
    {
        $validated = $request->validate([
            'reference_ids' => ['required', 'array'],
            'reference_ids.*' => ['integer', 'exists:regulation_analysis_references,id'],
        ]);

        $connected = $this->regulationAnalysisService->connectReferences(
            $regulation,
            $validated['reference_ids'],
        );

        if ($connected > 0) {
            UserActivityLog::log('connected', Regulation::class, $regulation->id, "Menghubungkan {$connected} referensi regulasi ke {$regulation->regulation_number}");
        }

        $message = $connected > 0
            ? "{$connected} referensi berhasil dihubungkan."
            : 'Tidak ada referensi baru yang dihubungkan.';

        return redirect()->route('regulations.analyze', $regulation)
            ->with($connected > 0 ? 'success' : 'info', $message);
    }

    public function analyzeBabs(Request $request, Regulation $regulation): JsonResponse
    {
        $results = $this->regulationAnalysisService->analyzeByBabs($regulation);

        return response()->json($results);
    }

    public function analyzeSingleBab(Request $request, Regulation $regulation, int $index): JsonResponse
    {
        $result = $this->regulationAnalysisService->analyzeBabByIndex($regulation, $index);

        return response()->json($result);
    }

    public function babList(Regulation $regulation): JsonResponse
    {
        $text = $this->regulationAnalysisService->getContentText($regulation);
        if (! $text) {
            return response()->json(['babs' => []]);
        }

        $babs = $this->regulationAnalysisService->splitTextToBabs($text);

        return response()->json([
            'babs' => array_map(fn ($b) => ['label' => $b['label']], $babs),
        ]);
    }

    public function reanalyze(Regulation $regulation): RedirectResponse
    {
        $this->regulationAnalysisService->regenerate($regulation);

        UserActivityLog::log('reanalyzed', Regulation::class, $regulation->id, "Melakukan re-analisis AI untuk regulasi {$regulation->regulation_number}");

        return redirect()->route('regulations.analyze', $regulation)
            ->with('success', 'Analisis berhasil diperbarui. Klik "Simpan Analisis" untuk menyimpan hasil.');
    }

    public function destroy(Regulation $regulation): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('upload_regulations'), 403);

        Storage::disk('public')->delete($regulation->file_path);

        foreach ($regulation->documents as $document) {
            Storage::disk('public')->delete($document->file_path);
        }

        $number = $regulation->regulation_number;
        $regulation->delete();

        UserActivityLog::log('deleted', Regulation::class, $regulation->id, "Menghapus regulasi {$number}");

        return redirect()->route('regulations.index')
            ->with('success', 'Regulasi berhasil dihapus.');
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $excludeId = $request->get('exclude_id');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->regulationRepository->search($query, $excludeId ? (int) $excludeId : null);

        return response()->json($results->map(fn (Regulation $r) => [
            'id' => $r->id,
            'regulation_number' => $r->regulation_number,
            'title' => $r->title,
            'year' => $r->year,
            'type_name' => $r->type?->name,
        ]));
    }

    public function uploadDocument(Request $request, Regulation $regulation): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('upload_regulations'), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,docx,doc,xlsx,xls,pptx,ppt', 'max:20480'],
        ]);

        $filePath = $request->file('file')->store('regulation-documents', 'public');

        RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => $request->input('name'),
            'document_type' => $request->input('document_type'),
            'file_path' => $filePath,
        ]);

        UserActivityLog::log('uploaded', Regulation::class, $regulation->id, "Mengunggah dokumen {$request->input('name')} ke regulasi {$regulation->regulation_number}");

        return redirect()->route('regulations.show', $regulation)
            ->with('success', 'Dokumen tambahan berhasil diunggah.');
    }

    public function deleteDocument(RegulationDocument $document): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('upload_regulations'), 403);

        $regulation = $document->regulation;

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        UserActivityLog::log('deleted', Regulation::class, $regulation->id, "Menghapus dokumen {$document->name} dari regulasi {$regulation->regulation_number}");

        return redirect()->route('regulations.show', $regulation)
            ->with('success', 'Dokumen tambahan berhasil dihapus.');
    }

    public function viewDocument(RegulationDocument $document): StreamedResponse
    {
        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $contentType = match ($extension) {
            'pdf' => 'application/pdf',
            'docx', 'doc' => 'application/msword',
            'xlsx', 'xls' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx', 'ppt' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            default => 'application/octet-stream',
        };

        return Storage::disk('public')->response($document->file_path, null, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline',
        ]);
    }

    public function viewDocumentParsedText(RegulationDocument $document): View
    {
        abort_unless($document->isParsed(), 404);

        return view('regulations.document-parsed-text', compact('document'));
    }

    public function viewFile(Regulation $regulation): StreamedResponse
    {
        return Storage::disk('public')->response($regulation->file_path, null, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function parseRegulation(Regulation $regulation): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('upload_regulations'), 403);

        $result = $this->regulationParserService->parseRegulation($regulation);

        if (! $result['success']) {
            return redirect()->route('regulations.show', $regulation)
                ->with('error', $result['message']);
        }

        UserActivityLog::log('parsed', Regulation::class, $regulation->id, "Melakukan parse teks regulasi {$regulation->regulation_number}");

        return redirect()->route('regulations.show', $regulation)
            ->with('success', $result['message']);
    }

    public function parseDocument(Regulation $regulation, RegulationDocument $document): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('upload_regulations'), 403);

        $result = $this->regulationParserService->parseDocument($document);

        if (! $result['success']) {
            return redirect()->route('regulations.show', $regulation)
                ->with('error', $result['message']);
        }

        UserActivityLog::log('parsed', Regulation::class, $regulation->id, "Melakukan parse dokumen {$document->name} dari regulasi {$regulation->regulation_number}");

        return redirect()->route('regulations.show', $regulation)
            ->with('success', $result['message']);
    }

    public function parseAllDocuments(Regulation $regulation): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('upload_regulations'), 403);

        $regulation->load('documents');
        $pending = $regulation->documents->reject(fn ($d) => $d->isParsed());

        if ($pending->isEmpty()) {
            return redirect()->route('regulations.show', $regulation)
                ->with('info', 'Semua dokumen tambahan sudah diparse.');
        }

        $count = $pending->count();

        foreach ($pending as $document) {
            ParseRegulationDocument::dispatchAfterResponse($document);
        }

        UserActivityLog::log('parsed', Regulation::class, $regulation->id, "Memproses {$count} dokumen tambahan dari regulasi {$regulation->regulation_number}");

        return redirect()->route('regulations.show', $regulation)
            ->with('success', "Memproses {$count} dokumen tambahan secara background. Silakan refresh halaman untuk melihat hasil.");
    }
}
