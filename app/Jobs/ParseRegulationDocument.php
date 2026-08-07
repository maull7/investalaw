<?php

namespace App\Jobs;

use App\Models\RegulationDocument;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParseRegulationDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'parsing';

    public $timeout = 600;

    public $tries = 1;

    public function __construct(
        public RegulationDocument $document,
    ) {}

    public function handle(RegulationParserService $parser): void
    {
        $document = $this->document->fresh();

        if (! $document) {
            return;
        }

        if ($document->isParsed() && $document->parse_progress === 100) {
            return;
        }

        if ($document->parse_status !== 'parsing') {
            $document->update(['parse_status' => 'parsing', 'parse_progress' => 0]);
        }

        $last = -1;
        $result = $parser->parseDocumentChoice($document, 'text', function (int $percent) use ($document, &$last) {
            if ($percent === 100 || ($percent - $last) >= 10) {
                $last = $percent;
                $document->fresh()?->update(['parse_progress' => $percent]);
            }
        });

        if (! $result['success']) {
            Log::warning("ParseRegulationDocument job failed for doc {$document->id}: {$result['message']}");
            $document->fresh()?->update(['parse_status' => null, 'parse_progress' => null]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseRegulationDocument job failed for doc {$this->document->id}: {$e->getMessage()}");
    }
}
