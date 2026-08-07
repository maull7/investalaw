<?php

namespace App\Jobs;

use App\Models\Regulation;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

    public function handle(RegulationParserService $parser): void
    {
        $regulation = $this->regulation->fresh();

        if (! $regulation) {
            return;
        }

        if ($regulation->isParsed() && $regulation->parse_progress === 100) {
            return;
        }

        if ($regulation->parse_status !== 'parsing') {
            $regulation->update(['parse_status' => 'parsing', 'parse_progress' => 0]);
        }

        $last = -1;

        try {
            $result = $parser->parseRegulation($regulation, function (int $percent) use ($regulation, &$last) {
                if ($percent === 100 || ($percent - $last) >= 10) {
                    $last = $percent;
                    $regulation->fresh()?->update(['parse_progress' => $percent]);
                }
            });
        } catch (\Throwable $e) {
            Log::error("ParseRegulation exception for regulation {$regulation->id}: {$e->getMessage()}");
            $regulation->fresh()?->update(['parse_status' => null, 'parse_progress' => null]);

            throw $e;
        }

        if (! $result['success']) {
            Log::warning("ParseRegulation job failed for regulation {$regulation->id}: {$result['message']}");
            $regulation->fresh()?->update(['parse_status' => null, 'parse_progress' => null]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseRegulation job failed for regulation {$this->regulation->id}: {$e->getMessage()}");
        $this->regulation->fresh()?->update(['parse_status' => null, 'parse_progress' => null]);
    }
}
