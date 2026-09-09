<?php

namespace Tests\Feature;

use App\Jobs\GenerateRegulationAiResult;
use App\Models\AiJobStatus;
use App\Models\AiPrompt;
use App\Models\Regulation;
use App\Models\RegulationAiResult;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Services\AiService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegulationAiGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_long_regulation_context_is_limited_and_result_is_saved(): void
    {
        config()->set('ai.regulation.max_context_characters', 1200);

        $regulation = $this->makeRegulation(
            'AWAL-DOKUMEN '.str_repeat('bagian isi regulasi ', 2000).' AKHIR-DOKUMEN',
        );
        $prompt = $this->makePrompt();
        $aiService = new class extends AiService
        {
            /** @var array<int, array{role: string, content: string}> */
            public array $capturedMessages = [];

            protected function callAi(array $messages, int $maxTokens = 4096, ?array $responseFormat = null): array
            {
                $this->capturedMessages = $messages;

                return [
                    'content' => 'Short Review berhasil dibuat.',
                    'provider' => 'fake',
                    'model' => 'fake-model',
                    'total_tokens' => 100,
                ];
            }
        };

        $result = $aiService->generateRegulationPrompt($regulation, $prompt);
        $context = $aiService->capturedMessages[1]['content'];

        $this->assertLessThanOrEqual(1200, mb_strlen($context));
        $this->assertStringContainsString('AWAL-DOKUMEN', $context);
        $this->assertStringContainsString('AKHIR-DOKUMEN', $context);
        $this->assertStringContainsString('bagian dokumen dipotong', $context);
        $this->assertStringContainsString('Tulis seluruh jawaban hanya dalam Bahasa Indonesia', $aiService->capturedMessages[0]['content']);
        $this->assertStringContainsString('Jangan gunakan Bahasa Inggris', $aiService->capturedMessages[0]['content']);
        $this->assertSame('Short Review berhasil dibuat.', $result->result);
        $this->assertModelExists($result);
    }

    public function test_empty_ai_content_is_not_saved_as_a_successful_result(): void
    {
        $regulation = $this->makeRegulation('Isi regulasi');
        $prompt = $this->makePrompt();
        $aiService = new class extends AiService
        {
            protected function callAi(array $messages, int $maxTokens = 4096, ?array $responseFormat = null): array
            {
                return [
                    'content' => '   ',
                    'provider' => 'fake',
                    'model' => 'fake-model',
                    'total_tokens' => 0,
                ];
            }
        };

        try {
            $aiService->generateRegulationPrompt($regulation, $prompt);
            $this->fail('Respons AI kosong seharusnya melempar exception.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('tidak menghasilkan konten', $exception->getMessage());
        }

        $this->assertSame(0, RegulationAiResult::query()->count());
    }

    public function test_failed_job_callback_records_final_error_status(): void
    {
        $regulation = $this->makeRegulation('Isi regulasi');
        $prompt = $this->makePrompt();
        AiJobStatus::begin($regulation, 'regulation-ai');

        $job = new GenerateRegulationAiResult($regulation, $prompt);
        $job->failed(new Exception('Provider sedang tidak tersedia'));

        $status = $regulation->aiStatus('regulation-ai');

        $this->assertSame('error', $status?->status);
        $this->assertStringContainsString('Provider sedang tidak tersedia', (string) $status?->message);
        $this->assertTrue($job->failOnTimeout);
        $this->assertSame([5, 15], $job->backoff());
    }

    private function makeRegulation(string $parsedText): Regulation
    {
        $type = RegulationType::create([
            'name' => 'Peraturan Test '.uniqid(),
            'level' => 1,
        ]);
        $category = RegulationCategory::create(['name' => 'Kategori Test '.uniqid()]);

        return Regulation::create([
            'regulation_number' => 'REG-'.uniqid(),
            'title' => 'Regulasi Pengujian',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2026,
            'file_path' => 'regulations/test.pdf',
            'parsed_text' => $parsedText,
        ]);
    }

    private function makePrompt(): AiPrompt
    {
        return AiPrompt::create([
            'type' => 'short-review-'.uniqid(),
            'title' => 'Short Review',
            'prompt_text' => 'Buat short review regulasi berikut.',
            'is_active' => true,
        ]);
    }
}
