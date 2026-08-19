<?php

namespace App\Jobs;

use App\Exceptions\ParsingCancelledException;
use App\Models\RegulationDocument;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

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

        try {
            $this->checkCancelled();
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulationDocument cancelled for doc {$document->id}");
            $document->fresh()?->update(['parse_status' => 'incomplete', 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return;
        }

        $ext = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));

        if ($ext === 'docx') {
            $parser->parseDocumentChunk($document, 1);

            return;
        }

        if ($ext !== 'pdf') {
            $document->update(['parse_status' => 'failed', 'parse_progress' => null, 'parse_error' => 'Format file tidak didukung. Hanya PDF dan DOCX.']);

            return;
        }

        $stats = $document->parse_stats ?? [];
        $fromPage = (int) ($stats['resume_page'] ?? 1);

        // First run: coba ekstraksi teks langsung dulu (bukan scan), kalau ada isi langsung selesai.
        if (empty($stats['resume_page']) && empty($stats['pdf_type'])) {
            $fullPath = Storage::disk('public')->path($document->file_path);
            $pdfParser = new Parser;

            try {
                $pdf = $pdfParser->parseFile($fullPath);
                $hasText = false;
                foreach ($pdf->getPages() as $page) {
                    if (mb_strlen(trim(preg_replace('/\s+/', ' ', $page->getText()))) > 10) {
                        $hasText = true;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $hasText = false;
            }

            if ($hasText) {
                $parser->extractTextPages($document, 'text');

                return;
            }
        }

        if ($document->parse_status !== 'parsing') {
            $document->update(['parse_status' => 'parsing', 'parse_progress' => 0, 'parse_error' => null]);
        }

        try {
            $result = $parser->parseDocumentChunk($document, $fromPage);
        } catch (ParsingCancelledException $e) {
            Log::info("ParseRegulationDocument cancelled for doc {$document->id}");
            $document->fresh()?->update(['parse_status' => 'incomplete', 'parse_error' => null]);
            Cache::forget($this->cancelKey());

            return;
        } catch (\Throwable $e) {
            Log::error("ParseRegulationDocument exception for doc {$document->id}: {$e->getMessage()}");
            $document->fresh()?->update(['parse_status' => 'failed', 'parse_error' => $this->truncateError($e->getMessage())]);

            throw $e;
        }

        if (! $result['success']) {
            Log::warning("ParseRegulationDocument chunk failed for doc {$document->id}: {$result['message']}");
            $document->fresh()?->update(['parse_status' => 'failed', 'parse_error' => $this->truncateError($result['message'])]);

            return;
        }

        if ($result['done']) {
            $parser->finalizeOcrParsed($document->fresh());
            Log::info("ParseRegulationDocument finished for doc {$document->id}");

            return;
        }

        self::dispatch($document->fresh());
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ParseRegulationDocument job failed for doc {$this->document->id}: {$e->getMessage()}");
        $this->document->fresh()?->update([
            'parse_status' => 'failed',
            'parse_error' => $this->truncateError($this->friendlyErrorMessage($e)),
        ]);
    }

    private function friendlyErrorMessage(\Throwable $e): string
    {
        if ($e instanceof MaxAttemptsExceededException
            || preg_match('/has been attempted too many times|released a job that has been attempted|has timed out/i', $e->getMessage())) {
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
