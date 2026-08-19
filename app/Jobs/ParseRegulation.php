<?php

namespace App\Jobs;

use App\Exceptions\ParsingCancelledException;
use App\Models\Regulation;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ParseRegulation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $queue = 'parsing';

    public $timeout = 600;

    public $tries = 1;

    public function __construct(
        public Regulation $regulation,
    ) {}

    private function cancelKey(): string
    {
        return "parse_cancel:regulation:{$this->regulation->id}";
    }

    public function handle(RegulationParserService $parser): void
    {
        $regulation = $this->regulation->fresh();

        if (! $regulation) {
            return;
        }

        if ($regulation->parse_status === 'complete') {
            return;
        }

        try {
            $this->checkCancelled();
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulation cancelled for regulation {$regulation->id}");
            $regulation->fresh()?->update(['parse_status' => 'incomplete', 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return;
        }

        $stats = $regulation->parse_stats ?? [];
        $fromPage = (int) ($stats['resume_page'] ?? 1);

        if ($regulation->parse_status !== 'parsing') {
            $regulation->update(['parse_status' => 'parsing', 'parse_progress' => 0, 'parse_error' => null]);
        }

        try {
            $result = $parser->parseRegulationChunk($regulation, $fromPage);
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulation cancelled for regulation {$regulation->id}");
            $regulation->fresh()?->update(['parse_status' => 'incomplete', 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return;
        } catch (\Throwable $e) {
            Log::error("ParseRegulation exception for regulation {$regulation->id}: {$e->getMessage()}");
            $regulation->fresh()?->update(['parse_status' => 'failed', 'parse_error' => $this->truncateError($e->getMessage())]);

            throw $e;
        }

        if (! $result['success']) {
            Log::warning("ParseRegulation chunk failed for regulation {$regulation->id}: {$result['message']}");
            $regulation->fresh()?->update(['parse_status' => 'failed', 'parse_error' => $this->truncateError($result['message'])]);

            return;
        }

        if ($result['done']) {
            $parser->finalizeOcrParsed($regulation->fresh());
            Log::info("ParseRegulation finished for regulation {$regulation->id}");

            return;
        }

        self::dispatch($regulation->fresh());
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
