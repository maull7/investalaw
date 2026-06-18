<?php

namespace App\Services;

use App\Models\AiPrompt;
use App\Models\AiSummary;
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

        $context = $this->buildContext($document);

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

    private function buildContext(ReviewDocument $document): string
    {
        $document->loadMissing('regulations.documents');

        $context = "=== DOKUMEN YANG DI-REVIEW ===\n";
        $context .= "Judul: {$document->title}\n";
        $context .= "Deskripsi: {$document->description}\n\n";

        $documentText = $this->documentParser->extractFromStoragePath($document->file_path);
        if ($documentText) {
            $context .= "--- Konten Dokumen ---\n{$documentText}\n\n";
        }

        $context .= "=== REGULASI ACUAN ===\n";

        foreach ($document->regulations as $i => $reg) {
            $context .= "\n--- Regulasi #".($i + 1)." ---\n";
            $context .= "Nomor: {$reg->regulation_number}\n";
            $context .= "Judul: {$reg->title}\n";
            $context .= "Tahun: {$reg->year}\n";

            $regText = $this->extractRegulationText($reg);
            if ($regText) {
                $context .= "--- Konten Regulasi ---\n{$regText}\n";
            }
        }

        return $context;
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

    private function callAi(array $messages): array
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
                    'max_tokens' => 4096,
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
