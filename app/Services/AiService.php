<?php

namespace App\Services;

use App\Models\AiPrompt;
use App\Models\AiSummary;
use App\Models\DocumentParsedText;
use App\Models\DocumentPartition;
use App\Models\PartitionAnalysis;
use App\Models\Regulation;
use App\Models\ReviewDocument;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI;

class AiService
{
    public function __construct(
        private readonly DocumentParser $documentParser
    ) {}

    public function generateSummary(ReviewDocument $document, string $type): AiSummary
    {
        $prompt = AiPrompt::active()->where('type', $type)->firstOrFail();

        $document->loadMissing(['partitions' => fn ($q) => $q->ordered(), 'regulations.documents']);

        $context = $this->buildContext($document);

        // Save parsed texts to DB
        $this->saveParsedTexts($document);

        $messages = [
            ['role' => 'system', 'content' => $prompt->prompt_text],
            ['role' => 'user', 'content' => $context],
        ];

        $result = $this->callAi($messages);

        return AiSummary::create([
            'review_document_id' => $document->id,
            'type' => $type,
            'prompt_text' => $prompt->prompt_text,
            'summary' => $result['content'],
            'raw_response' => $result['raw'] ?? null,
            'provider_used' => $result['provider'],
            'model_used' => $result['model'],
        ]);
    }

    public function generatePartitionAnalysis(DocumentPartition $partition, string $type): PartitionAnalysis
    {
        $document = $partition->reviewDocument;
        $document->loadMissing('regulations.documents');

        $partitionText = $this->documentParser->extractPagesFromStoragePath(
            $document->file_path,
            $partition->start_page,
            $partition->end_page
        );

        $systemPrompt = $this->buildPartitionSystemPrompt($type);
        $userPrompt = $this->buildPartitionUserPrompt($document, $partition, $partitionText);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $result = $this->callAi($messages, 1024);

        $parsed = $this->parseAnalysisResponse($result['content']);

        return PartitionAnalysis::updateOrCreate(
            [
                'document_partition_id' => $partition->id,
                'type' => $type,
            ],
            [
                'review_document_id' => $document->id,
                'summary' => $parsed['summary'],
                'findings' => $parsed['findings'],
                'compliance_score' => $parsed['compliance_score'],
                'compliance_status' => $this->scoreToStatus($parsed['compliance_score']),
                'raw_response' => $result['content'],
                'provider_used' => $result['provider'],
                'model_used' => $result['model'],
            ]
        );
    }

    /** @return array<PartitionAnalysis> */
    public function generateAllPartitionAnalyses(ReviewDocument $document, string $type): array
    {
        set_time_limit(300);

        $partitions = $document->partitions()->ordered()->get();
        $results = [];

        foreach ($partitions as $partition) {
            $results[] = $this->generatePartitionAnalysis($partition, $type);
        }

        return $results;
    }

    private function buildPartitionSystemPrompt(string $type): string
    {
        $existingPrompt = AiPrompt::active()->where('type', $type)->first();

        if ($existingPrompt) {
            return $existingPrompt->prompt_text."\n\nPENTING: Analisa ini dilakukan PER PARTISI, bukan keseluruhan dokumen. Fokus hanya pada konten partisi yang diberikan.";
        }

        return <<<'PROMPT'
Anda adalah analis kepatuhan hukum profesional. Analisa partisi dokumen berikut berdasarkan regulasi yang berlaku.

Return JSON format:
{
  "summary": "Ringkasan singkat partisi ini (2-3 paragraf)",
  "findings": "Temuan kepatuhan dan ketidaksesuaian yang ditemukan",
  "compliance_score": "Skor kepatuhan 0-100"
}

PENTING: Analisa ini dilakukan PER PARTISI. Fokus hanya pada konten partisi yang diberikan.
PROMPT;
    }

    private function buildPartitionUserPrompt(ReviewDocument $document, DocumentPartition $partition, string $partitionText): string
    {
        $document->loadMissing('regulations.documents');

        $prompt = "=== DOKUMEN ===\n";
        $prompt .= "Judul: {$document->title}\n";
        $prompt .= "Partisi: {$partition->name}\n";
        $prompt .= "Halaman: {$partition->start_page} - {$partition->end_page}\n";

        if ($partition->description) {
            $prompt .= "Deskripsi: {$partition->description}\n";
        }

        $prompt .= "\n--- Konten Partisi ---\n{$partitionText}\n";

        if ($document->regulations->isNotEmpty()) {
            $prompt .= "\n=== REGULASI ACUAN ===\n";
            foreach ($document->regulations as $reg) {
                $prompt .= "\n--- Regulasi: {$reg->regulation_number} - {$reg->title} ({$reg->year}) ---\n";

                $regText = $this->getOrParseRegulationText($document, $reg);
                if ($regText) {
                    $prompt .= $regText."\n";
                }
            }
        }

        return $prompt;
    }

    private function scoreToStatus(?string $score): ?string
    {
        if ($score === null) {
            return null;
        }

        $intScore = (int) $score;

        if ($intScore >= 70) {
            return 'compliant';
        }
        if ($intScore >= 40) {
            return 'partially_compliant';
        }

        return 'non_compliant';
    }

    /** @return array{summary: string, findings: string|null, compliance_score: string|null} */
    private function parseAnalysisResponse(string $content): array
    {
        $cleanContent = preg_replace('/```json\s*/', '', $content);
        $cleanContent = preg_replace('/```\s*$/', '', $cleanContent);
        $cleanContent = trim($cleanContent);

        $decoded = json_decode($cleanContent, true);

        if ($decoded && isset($decoded['summary'])) {
            return [
                'summary' => $decoded['summary'],
                'findings' => $decoded['findings'] ?? null,
                'compliance_score' => isset($decoded['compliance_score']) ? (string) $decoded['compliance_score'] : null,
            ];
        }

        return [
            'summary' => $content,
            'findings' => null,
            'compliance_score' => null,
        ];
    }

    private function buildContext(ReviewDocument $document): string
    {
        $document->loadMissing(['regulations.documents', 'partitions' => fn ($q) => $q->ordered()]);

        $context = "=== DOKUMEN YANG DI-REVIEW ===\n";
        $context .= "Judul: {$document->title}\n";
        $context .= "Deskripsi: {$document->description}\n\n";

        // Document text from cache or fresh parse
        $cachedDoc = DocumentParsedText::forDocument($document->id)->get();

        if ($document->partitions->isNotEmpty()) {
            foreach ($document->partitions as $partition) {
                $cached = $cachedDoc->where('source_id', $partition->id)->first();
                $partitionText = $cached?->parsed_text
                    ?? $this->documentParser->extractPagesFromStoragePath($document->file_path, $partition->start_page, $partition->end_page);
                $context .= "--- Partisi: {$partition->name} (h.{$partition->start_page}-{$partition->end_page}) ---\n";
                $context .= "{$partitionText}\n\n";
            }
        } else {
            $cached = $cachedDoc->whereNull('source_id')->first();
            $documentText = $cached?->parsed_text
                ?? $this->documentParser->extractFromStoragePath($document->file_path);
            if ($documentText) {
                $context .= "--- Konten Dokumen ---\n{$documentText}\n\n";
            }
        }

        $context .= "=== REGULASI ACUAN ===\n";

        foreach ($document->regulations as $i => $reg) {
            $context .= "\n--- Regulasi #".($i + 1)." ---\n";
            $context .= "Nomor: {$reg->regulation_number}\n";
            $context .= "Judul: {$reg->title}\n";
            $context .= "Tahun: {$reg->year}\n";

            $regText = $this->getOrParseRegulationText($document, $reg);
            if ($regText) {
                $context .= "--- Konten Regulasi ---\n{$regText}\n";
            }
        }

        return $context;
    }

    private function getOrParseRegulationText(ReviewDocument $document, Regulation $regulation): string
    {
        $cached = DocumentParsedText::forRegulation($document->id, $regulation->id)->first();

        if ($cached && $cached->char_count > 0) {
            return $cached->parsed_text;
        }

        return $this->extractRegulationText($regulation);
    }

    private function extractRegulationText(Regulation $regulation): string
    {
        $texts = [];

        if ($regulation->file_path) {
            $text = $this->documentParser->extractFromStoragePath($regulation->file_path);
            if ($text) {
                $texts[] = $text;
            }
        }

        foreach ($regulation->documents as $doc) {
            $text = $this->documentParser->extractFromStoragePath($doc->file_path);
            if ($text) {
                $texts[] = "[{$doc->name}] {$text}";
            }
        }

        return implode("\n\n", $texts);
    }

    private function saveParsedTexts(ReviewDocument $document): void
    {
        // Save document text (always refresh)
        DocumentParsedText::forDocument($document->id)->delete();

        if ($document->partitions->isNotEmpty()) {
            foreach ($document->partitions as $partition) {
                $text = $this->documentParser->extractPagesFromStoragePath(
                    $document->file_path,
                    $partition->start_page,
                    $partition->end_page
                );
                DocumentParsedText::create([
                    'review_document_id' => $document->id,
                    'source_type' => 'document',
                    'source_id' => $partition->id,
                    'page' => $partition->start_page,
                    'parsed_text' => $text,
                    'char_count' => mb_strlen($text),
                ]);
            }
        } else {
            $text = $this->documentParser->extractFromStoragePath($document->file_path);
            DocumentParsedText::create([
                'review_document_id' => $document->id,
                'source_type' => 'document',
                'source_id' => null,
                'page' => null,
                'parsed_text' => $text,
                'char_count' => mb_strlen($text),
            ]);
        }

        // Save regulation texts — use cache if already exists
        foreach ($document->regulations as $reg) {
            $existing = DocumentParsedText::forRegulation($document->id, $reg->id)->first();

            if ($existing && $existing->char_count > 0) {
                continue; // Already cached, skip re-parsing
            }

            $regText = $this->extractRegulationText($reg);
            DocumentParsedText::updateOrCreate(
                [
                    'review_document_id' => $document->id,
                    'source_type' => 'regulation',
                    'source_id' => $reg->id,
                ],
                [
                    'page' => null,
                    'parsed_text' => $regText ?: '',
                    'char_count' => mb_strlen($regText),
                ]
            );
        }
    }

    private function callAi(array $messages, int $maxTokens = 4096): array
    {
        $providers = [
            'openai' => [
                'api_key' => config('ai.openai.api_key'),
                'base_url' => config('ai.openai.base_url'),
                'model' => config('ai.openai.model'),
            ],
            'groq' => [
                'api_key' => config('ai.groq.api_key'),
                'base_url' => config('ai.groq.base_url'),
                'model' => config('ai.groq.model'),
            ],
        ];

        $lastException = null;

        foreach ($providers as $name => $config) {
            if (empty($config['api_key'])) {
                continue;
            }

            try {
                $client = OpenAI::factory()
                    ->withApiKey($config['api_key'])
                    ->withBaseUri($config['base_url'])
                    ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
                    ->make();

                $response = $client->chat()->create([
                    'model' => $config['model'],
                    'messages' => $messages,
                    'temperature' => 0.3,
                    'max_tokens' => $maxTokens,
                ]);

                return [
                    'content' => $response->choices[0]->message->content ?? '',
                    'provider' => $name,
                    'model' => $config['model'],
                ];
            } catch (Exception $e) {
                $lastException = $e;
                Log::warning("AI provider {$name} failed: {$e->getMessage()}");
            }
        }

        throw $lastException ?? new Exception('No AI provider available');
    }
}
