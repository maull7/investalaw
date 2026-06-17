<?php

namespace App\Services;

use App\Models\AiPrompt;
use App\Models\AiSummary;
use App\Models\ReviewDocument;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI;

class AiService
{
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
        $document->loadMissing('regulations');

        $context = "Dokumen: {$document->title}\n";
        $context .= "Deskripsi: {$document->description}\n\n";
        $context .= "Daftar Regulasi Acuan:\n";

        foreach ($document->regulations as $i => $reg) {
            $context .= ($i + 1).". {$reg->regulation_number} - {$reg->title} (Tahun {$reg->year})\n";
        }

        return $context;
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
