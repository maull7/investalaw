<?php

namespace App\Jobs;

use App\Exceptions\ParsingCancelledException;
use App\Models\Regulation;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
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

        if ($regulation->parse_status !== 'parsing') {
            $regulation->update(['parse_status' => 'parsing', 'parse_progress' => 0, 'parse_error' => null]);
        }

        $last = -1;

        try {
            $result = $parser->parseRegulation($regulation, function (int $percent) use ($regulation, &$last) {
                $this->checkCancelled();
                if ($percent === 100 || ($percent - $last) >= 10) {
                    $last = $percent;
                    $regulation->fresh()?->update(['parse_progress' => $percent]);
                }
            });
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulation cancelled for regulation {$regulation->id}");
            $regulation->fresh()?->update(['parse_status' => 'not_parsed', 'parse_progress' => null, 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return;
        } catch (\Throwable $e) {
            Log::error("ParseRegulation exception for regulation {$regulation->id}: {$e->getMessage()}");
            $regulation->fresh()?->update(['parse_status' => 'failed', 'parse_progress' => null, 'parse_error' => $this->truncateError($e->getMessage())]);

            throw $e;
        }

        if (! $result['success']) {
            Log::warning("ParseRegulation job failed for regulation {$regulation->id}: {$result['message']}");
            $regulation->fresh()?->update(['parse_status' => 'failed', 'parse_progress' => null, 'parse_error' => $this->truncateError($result['message'])]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseRegulation job failed for regulation {$this->regulation->id}: {$e->getMessage()}");
        $this->regulation->fresh()?->update([
            'parse_status' => 'failed',
            'parse_progress' => null,
            'parse_error' => $this->truncateError($this->friendlyErrorMessage($e)),
        ]);
    }

    private function friendlyErrorMessage(\Throwable $e): string
    {
        if ($e instanceof \Illuminate\Queue\MaxAttemptsExceededException
            || preg_match('/has been attempted too many times|released a job that has been attempted|has timed out/i', $e->getMessage())) {
            return 'Proses parse gagal di latar belakang. Silakan coba parse ulang.';
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