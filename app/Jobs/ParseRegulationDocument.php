<?php

namespace App\Jobs;

use App\Models\RegulationDocument;
use App\Services\RegulationParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParseRegulationDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 1;

    public function __construct(
        public RegulationDocument $document,
    ) {}

    public function handle(RegulationParserService $parser): void
    {
        $this->document->refresh();

        if ($this->document->isParsed()) {
            return;
        }

        $result = $parser->parseDocumentChoice($this->document, 'text');

        if (! $result['success']) {
            Log::warning("ParseRegulationDocument job failed for doc {$this->document->id}: {$result['message']}");
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseRegulationDocument job failed for doc {$this->document->id}: {$e->getMessage()}");
    }
}
