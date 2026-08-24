<?php

namespace App\Jobs;

use App\Exceptions\ParsingCancelledException;
use App\Models\Regulation;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ParseRegulation implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $queue = 'parsing';

    public $timeout = 1700;

    public $tries = 1;

    public function __construct(
        public Regulation $regulation,
    ) {}

    private function cancelKey(): string
    {
        return "parse_cancel:regulation:{$this->regulation->id}";
    }

    public function uniqueId(): string
    {
        return (string) $this->regulation->id;
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(RegulationParserService $parser): void
    {
        $shouldContinue = Cache::lock("parse_lock:regulation:{$this->regulation->id}", $this->timeout + 60)
            ->get(fn (): bool => $this->process($parser));

        $regulation = $shouldContinue === true ? $this->regulation->fresh() : null;
        if ($regulation) {
            self::dispatch($regulation);
        }
    }

    private function process(RegulationParserService $parser): bool
    {
        $regulation = $this->regulation->fresh();

        if (! $regulation) {
            return false;
        }

        if ($regulation->parse_status === 'complete') {
            return false;
        }

        try {
            $this->checkCancelled();
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulation cancelled for regulation {$regulation->id}");
            $regulation->fresh()?->update(['parse_status' => 'incomplete', 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return false;
        }

        $stats = $regulation->parse_stats ?? [];
        $fromPage = (int) ($stats['resume_page'] ?? 1);

        if (empty($stats['pdf_type']) && empty($stats['page_counts']) && $parser->extractTextPages($regulation, 'text')) {
            Log::info("ParseRegulation extracted text for regulation {$regulation->id}");

            return false;
        }

        if ($regulation->parse_status !== 'parsing') {
            $regulation->update(['parse_status' => 'parsing', 'parse_progress' => 0, 'parse_error' => null]);
        }

        try {
            $result = $parser->parseRegulationChunk($regulation, $fromPage);
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulation cancelled for regulation {$regulation->id}");
            $regulation->fresh()?->update(['parse_status' => 'incomplete', 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return false;
        } catch (\Throwable $e) {
            Log::error("ParseRegulation exception for regulation {$regulation->id}: {$e->getMessage()}");
            $regulation->fresh()?->update(['parse_status' => 'failed', 'parse_error' => $this->truncateError($e->getMessage())]);

            throw $e;
        }

        if (! $result['success']) {
            Log::warning("ParseRegulation chunk failed for regulation {$regulation->id}: {$result['message']}");
            $regulation->fresh()?->update(['parse_status' => 'failed', 'parse_error' => $this->truncateError($result['message'])]);

            return false;
        }

        if ($result['done']) {
            $parser->finalizeOcrParsed($regulation->fresh());
            Log::info("ParseRegulation finished for regulation {$regulation->id}");

            return false;
        }

        return true;
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseRegulation job failed for regulation {$this->regulation->id}: {$e->getMessage()}");
        $this->regulation->fresh()?->update([
            'parse_status' => 'failed',
            'parse_error' => $this->truncateError($this->friendlyErrorMessage($e)),
        ]);
    }

    private function friendlyErrorMessage(\Throwable $e): string
    {
        if (
            $e instanceof MaxAttemptsExceededException
            || preg_match('/has been attempted too many times|released a job that has been attempted|has timed out/i', $e->getMessage())
        ) {
            return 'Proses parse gagal di latar belakang. Silakan coba parse ulang (akan lanjut dari halaman terakhir).';
        }

        return $e->getMessage();
    }

    private function checkCancelled(): void
    {
        if (Cache::get($this->cancelKey())) {
            throw new ParsingCancelledException('Parsing dibatalkan.');
        }
    }

    private function truncateError(string $message): string
    {
        return mb_substr($message, 0, 500);
    }
}
