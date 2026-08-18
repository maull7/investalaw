<?php

namespace App\Http\Controllers;

use App\Models\AiPrompt;
use App\Models\AiSummary;
use App\Models\DocumentBabAnalysis;
use App\Models\DocumentParsedText;
use App\Models\ReviewDocument;
use App\Services\AiService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    public function babList(ReviewDocument $reviewDocument): JsonResponse
    {
        $reviewDocument->load('pages');
        $text = $reviewDocument->pages->pluck('content')->implode("\n");

        if (! $text) {
            return response()->json(['babs' => []]);
        }

        $babs = $this->splitTextToBabs($text);

        $saved = DocumentBabAnalysis::where('review_document_id', $reviewDocument->id)
            ->get()
            ->keyBy('bab_index');

        return response()->json([
            'babs' => array_map(fn ($b, $index) => [
                'label' => $b['label'],
                ...($saved->has($index) ? ['result' => $saved->get($index)->result] : []),
            ], $babs, array_keys($babs)),
        ]);
    }

    public function analyzeBabs(ReviewDocument $reviewDocument): JsonResponse
    {
        $reviewDocument->load('pages');
        $text = $reviewDocument->pages->pluck('content')->implode("\n");

        if (! $text) {
            return response()->json(['babs' => []]);
        }

        $babs = $this->splitTextToBabs($text);

        $providers = [
            'openai' => [
                'api_key' => config('ai.openai.api_key'),
                'base_url' => config('ai.openai.base_url', 'https://api.openai.com/v1'),
                'model' => config('ai.openai.model', 'gpt-4o-mini'),
            ],
        ];

        $results = [];
        foreach ($babs as $bab) {
            $babLabel = $bab['label'];
            $babText = $bab['text'];

            $prompt = <<<PROMPT
Anda adalah ekstraktor pasal. Dari teks BAB berikut, ekstrak struktur pasal dan referensi ke peraturan lain.

BAB: {$babLabel}

TEKS:
{$babText}

Kembalikan JSON SAJA dengan format:
{
  "pasal_structure": [
    {
      "pasal": "Pasal X",
      "content": "ringkasan isi pasal (max 200 chars)",
      "type": "baru|existing|diubah",
      "changes": "deskripsi perubahan jika ada"
    }
  ],
  "referenced_regulations": [
    {
      "name": "nama peraturan",
      "number": "nomor",
      "year": tahun,
      "relationship": "diubah|dicabut|dirujuk|disebut"
    }
  ]
}
PROMPT;

            $result = ['pasal_structure' => [], 'referenced_regulations' => []];

            foreach ($providers as $provider) {
                if (empty($provider['api_key'])) {
                    continue;
                }
                try {
                    $response = Http::withToken($provider['api_key'])
                        ->timeout(120)
                        ->post(rtrim($provider['base_url'], '/').'/chat/completions', [
                            'model' => $provider['model'],
                            'messages' => [
                                ['role' => 'system', 'content' => 'Anda adalah asisten yang hanya mengembalikan JSON valid.'],
                                ['role' => 'user', 'content' => $prompt],
                            ],
                            'max_tokens' => 4096,
                            'temperature' => 0.1,
                            'response_format' => ['type' => 'json_object'],
                        ]);

                    if (! $response->successful()) {
                        continue;
                    }

                    $content = $response->json('choices.0.message.content');
                    if (empty($content)) {
                        continue;
                    }

                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $result = $decoded;
                        break;
                    }
                } catch (\Throwable $e) {
                    report($e);

                    continue;
                }
            }

            $results[] = [
                'label' => $babLabel,
                'pasal_count' => count($result['pasal_structure'] ?? []),
                'ref_count' => count($result['referenced_regulations'] ?? []),
                'pasal' => $result['pasal_structure'] ?? [],
                'references' => $result['referenced_regulations'] ?? [],
            ];
        }

        return response()->json(['babs' => $results]);
    }

    public function analyzeSingleBab(ReviewDocument $reviewDocument, int $index): JsonResponse
    {
        $reviewDocument->load(['pages', 'regulations']);
        $text = $reviewDocument->pages->pluck('content')->implode("\n");

        if (! $text) {
            return response()->json(['label' => null, 'pasal' => [], 'references' => [], 'insights' => null, 'compliance_assessment' => null, 'key_findings' => []]);
        }

        $babs = $this->splitTextToBabs($text);

        if (! isset($babs[$index])) {
            return response()->json(['label' => null, 'pasal' => [], 'references' => [], 'insights' => null, 'compliance_assessment' => null, 'key_findings' => []]);
        }

        $bab = $babs[$index];
        $babLabel = $bab['label'];
        $babText = $bab['text'];

        $regTexts = [];
        foreach ($reviewDocument->regulations as $reg) {
            $regContent = $reg->parsed_text ?? '';
            if ($regContent) {
                $regTexts[] = "{$reg->regulation_number} - {$reg->title}:\n".mb_substr($regContent, 0, 3000);
            }
        }
        $regBlock = $regTexts ? "\n\nREGULASI ACUAN:\n".implode("\n\n", $regTexts) : '';

        $pembandingList = '';
        if ($reviewDocument->regulations->isNotEmpty()) {
            $pembandingList = "\n\nDAFTAR REGULASI PEMBANDING — HANYA daftar ini yang boleh muncul di referensi:\n";
            foreach ($reviewDocument->regulations as $i => $reg) {
                $pembandingList .= ($i + 1).'. '.$reg->regulation_number.($reg->year ? " ({$reg->year})" : '')."\n";
            }
        }

        $providers = [
            'openai' => [
                'api_key' => config('ai.openai.api_key'),
                'base_url' => config('ai.openai.base_url', 'https://api.openai.com/v1'),
                'model' => config('ai.openai.model', 'gpt-4o-mini'),
            ],
        ];

        $prompt = <<<PROMPT
Anda adalah analis dokumen. Analisis BAB berikut dari dokumen "{$reviewDocument->title}" dan bandingkan dengan regulasi acuan.

BAB: {$babLabel}

Konten BAB:
{$babText}
{$regBlock}
{$pembandingList}

referenced_regulations hanya boleh berisi peraturan dari DAFTAR REGULASI PEMBANDING di atas. Abaikan peraturan lain yang disebut di luar daftar tersebut.

Berikan analisis JSON dengan format:
{
  "pasal_structure": [
    {
      "pasal": "Pasal X",
      "content": "ringkasan isi pasal",
      "type": "baru|existing|diubah",
      "changes": "deskripsi perubahan jika ada"
    }
  ],
  "referenced_regulations": [
    {
      "name": "nama peraturan",
      "number": "nomor",
      "year": tahun,
      "relationship": "diubah|dicabut|dirujuk|disebut",
      "pasal": ["Pasal X", "Pasal Y ayat (z)"]
    }
  ],
  "insights": "analisis perbandingan bab ini dengan regulasi acuan: kesesuaian, perbedaan, atau celah",
  "compliance_assessment": "Sesuai|Perlu Penyesuaian|Tidak Sesuai|Tidak Ada Regulasi Terkait",
  "key_findings": ["temuan singkat 1", "temuan singkat 2"]
}
PROMPT;

        $result = ['pasal_structure' => [], 'referenced_regulations' => [], 'insights' => null, 'compliance_assessment' => null, 'key_findings' => []];

        foreach ($providers as $provider) {
            if (empty($provider['api_key'])) {
                continue;
            }
            try {
                $response = Http::withToken($provider['api_key'])
                    ->timeout(120)
                    ->post(rtrim($provider['base_url'], '/').'/chat/completions', [
                        'model' => $provider['model'],
                        'messages' => [
                            ['role' => 'system', 'content' => 'Anda adalah asisten yang hanya mengembalikan JSON valid.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'max_tokens' => 4096,
                        'temperature' => 0.1,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                if (! $response->successful()) {
                    continue;
                }

                $content = $response->json('choices.0.message.content');
                if (empty($content)) {
                    continue;
                }

                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result = $decoded;
                    break;
                }
            } catch (\Throwable $e) {
                report($e);

                continue;
            }
        }

        $references = $this->filterPembandingReferences($result['referenced_regulations'] ?? [], $reviewDocument->regulations);

        $payload = [
            'label' => $babLabel,
            'pasal_count' => count($result['pasal_structure'] ?? []),
            'ref_count' => count($references),
            'pasal' => $result['pasal_structure'] ?? [],
            'references' => $references,
            'insights' => $result['insights'] ?? null,
            'compliance_assessment' => $result['compliance_assessment'] ?? null,
            'key_findings' => $result['key_findings'] ?? [],
        ];

        DocumentBabAnalysis::updateOrCreate(
            ['review_document_id' => $reviewDocument->id, 'bab_index' => $index],
            ['label' => $babLabel, 'result' => $payload]
        );

        return response()->json($payload);
    }

    // ponytail: fuzzy best-effort match; prompt whitelist does the heavy lifting, this drops wildcards
    private function filterPembandingReferences(array $references, Collection $regulations): array
    {
        return array_values(array_filter($references, function ($ref) use ($regulations) {
            $refName = $this->normalizeKey($ref['name'] ?? '');
            $refNumber = $this->normalizeKey($ref['number'] ?? '');
            $refYear = (int) ($ref['year'] ?? 0);

            foreach ($regulations as $reg) {
                $regName = $this->normalizeKey($reg->regulation_number ?? '');
                $regTitle = $this->normalizeKey($reg->title ?? '');

                $matches = ($refName !== '' && ($refName === $regName || str_contains($regName, $refName) || str_contains($refName, $regName)))
                    || ($refName !== '' && $regTitle !== '' && str_contains($regTitle, $refName))
                    || ($refNumber !== '' && $refNumber === $regName);

                if (! $matches) {
                    continue;
                }

                $regYear = (int) ($reg->year ?? 0);

                return $refYear === 0 || $regYear === 0 || $regYear === $refYear;
            }

            return false;
        }));
    }

    private function normalizeKey(string $value): string
    {
        return mb_strtolower(preg_replace('/[^a-zA-Z0-9]/u', '', $value) ?? '');
    }

    private function splitTextToBabs(string $text): array
    {
        $pattern = '/(BAB\s+(?:[IVXLCDM]+|\d+)[^\n]*)/i';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            return [['label' => 'Full Text', 'text' => mb_substr($text, 0, 30000)]];
        }

        $babs = [];
        foreach ($matches[0] as $idx => $match) {
            $label = trim($match[0]);
            $start = $match[1];
            $nextStart = $matches[0][$idx + 1][1] ?? strlen($text);
            $babText = mb_substr($text, $start, $nextStart - $start);
            $babText = mb_substr($babText, 0, 30000);
            $babs[] = ['label' => $label, 'text' => $babText];
        }

        return $babs;
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

        if (! $reviewDocument->isParsed()) {
            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $type])
                ->with('error', 'Dokumen belum di-parse. Silakan lakukan Parse PDF terlebih dahulu di menu Partisi.');
        }

        $reviewDocument->load('regulations');
        $unparsedRegs = $reviewDocument->regulations->reject(fn ($r) => $r->isParsed());
        if ($unparsedRegs->isNotEmpty()) {
            return redirect()->route('ai-preview.show', [$reviewDocument, 'type' => $type])
                ->with('error', 'Regulasi berikut belum diparse: '.$unparsedRegs->pluck('regulation_number')->implode(', ').'. Parse terlebih dahulu di menu Regulasi.');
        }

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
