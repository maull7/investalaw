<?php

namespace App\Jobs;

use App\Exceptions\ParsingCancelledException;
use App\Models\RegulationDocument;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ParseRegulationDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $queue = 'parsing';

    public $timeout = 600;

    public $tries = 1;

    public function __construct(
        public RegulationDocument $document,
    ) {}

    private function cancelKey(): string
    {
        return "parse_cancel:document:{$this->document->id}";
    }

    public function handle(RegulationParserService $parser): void
    {
        $document = $this->document->fresh();

        if (! $document) {
            return;
        }

        if ($document->parse_status === 'complete') {
            return;
        }

        if ($document->parse_status !== 'parsing') {
            $document->update(['parse_status' => 'parsing', 'parse_progress' => 0, 'parse_error' => null]);
        }

        $last = -1;

        try {
            $result = $parser->parseDocumentChoice($document, 'text', function (int $percent) use ($document, &$last) {
                $this->checkCancelled();
                if ($percent === 100 || ($percent - $last) >= 10) {
                    $last = $percent;
                    $document->fresh()?->update(['parse_progress' => $percent]);
                }
            }, fn () => $this->checkCancelled());
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulationDocument cancelled for doc {$document->id}");
            $document->fresh()?->update(['parse_status' => 'not_parsed', 'parse_progress' => null, 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return;
        } catch (\Throwable $e) {
            Log::error("ParseRegulationDocument exception for doc {$document->id}: {$e->getMessage()}");
            $document->fresh()?->update(['parse_status' => 'failed', 'parse_progress' => null, 'parse_error' => $this->truncateError($e->getMessage())]);

            throw $e;
        }

        if (! $result['success']) {
            Log::warning("ParseRegulationDocument job failed for doc {$document->id}: {$result['message']}");
            $document->fresh()?->update(['parse_status' => 'failed', 'parse_progress' => null, 'parse_error' => $this->truncateError($result['message'])]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseRegulationDocument job failed for doc {$this->document->id}: {$e->getMessage()}");
        $this->document->fresh()?->update([
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