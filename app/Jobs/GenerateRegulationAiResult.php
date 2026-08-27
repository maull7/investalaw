<?php

namespace App\Jobs;

use App\Models\AiJobStatus;
use App\Models\AiPrompt;
use App\Models\Regulation;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateRegulationAiResult implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $queue = 'ai';

    public $timeout = 200;

    public $tries = 3;

    public bool $failOnTimeout = true;

    public function __construct(
        public Regulation $regulation,
        public AiPrompt $prompt,
    ) {}

    public function handle(AiService $aiService): void
    {
        AiJobStatus::begin($this->regulation, 'regulation-ai');

        try {
            $aiService->generateRegulationPrompt($this->regulation, $this->prompt);
            $this->regulation->aiStatus('regulation-ai')?->markDone("Generate AI {$this->prompt->title} selesai.");
        } catch (Throwable $e) {
            report($e);
            $this->regulation->aiStatus('regulation-ai')?->markFailed('Gagal generate AI: '.$e->getMessage());

            throw $e;
        }
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [5, 15];
    }

    public function failed(?Throwable $exception): void
    {
        $message = $exception?->getMessage() ?: 'Terjadi kesalahan yang tidak diketahui.';

        $this->regulation->aiStatus('regulation-ai')?->markFailed('Gagal generate AI: '.$message);
    }
}
