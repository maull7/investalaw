<?php

namespace App\Services;

use App\Models\Regulation;
use App\Models\RegulationDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;

class RegulationParserService
{
    private const TEXT_THRESHOLD = 10;

    public function __construct(
        private readonly DocumentParser $documentParser,
    ) {}

    public function detectPdfType(string $path): string
    {
        $fullPath = Storage::disk('public')->path($path);

        if (! file_exists($fullPath)) {
            return 'image';
        }

        try {
            $pdf = $this->parsePdf($fullPath);
            $pages = $pdf->getPages();

            foreach ($pages as $page) {
                $text = trim(preg_replace('/\s+/', ' ', $page->getText()));
                if (mb_strlen($text) > self::TEXT_THRESHOLD) {
                    return 'text';
                }
            }

            return 'image';
        } catch (\Exception $e) {
            Log::warning("Failed to detect PDF type for {$path}: {$e->getMessage()}");

            return 'image';
        }
    }

    public function parseRegulation(Regulation $regulation): array
    {
        $fullPath = Storage::disk('public')->path($regulation->file_path);

        if (! file_exists($fullPath)) {
            return $this->result('error', 'File tidak ditemukan.');
        }

        set_time_limit(300);

        $pdfType = $this->detectPdfType($regulation->file_path);
        $pages = $this->documentParser->extractAllPagesText($regulation->file_path);

        if (empty($pages)) {
            return $this->result('error', 'Gagal mengekstrak teks dari PDF.');
        }

        $totalPages = count($pages);
        $parsedPages = array_filter($pages, fn ($p) => $p['char_count'] > 0);
        $parsedCount = count($parsedPages);
        $percentParsed = $totalPages > 0 ? round(($parsedCount / $totalPages) * 100) : 0;

        $fullText = collect($pages)->pluck('text')->implode("\n\n");

        $parseStatus = $percentParsed >= 100 ? 'complete' : ($percentParsed > 0 ? 'incomplete' : 'not_parsed');

        $stats = [
            'pdf_type' => $pdfType,
            'total_pages' => $totalPages,
            'parsed_pages' => $parsedCount,
            'empty_pages' => $totalPages - $parsedCount,
            'percent_parsed' => $percentParsed,
            'normal_pages' => $pdfType === 'text' ? $parsedCount : 0,
            'ocr_pages' => $pdfType === 'image' ? $parsedCount : 0,
            'char_total' => array_sum(array_column($pages, 'char_count')),
            'used_ocr' => $pdfType === 'image',
        ];

        $regulation->update([
            'parsed_at' => now(),
            'parse_status' => $parseStatus,
            'parsed_text' => $this->sanitizeUtf8($fullText),
            'parse_stats' => $stats,
        ]);

        return $this->result('success', 'Regulasi berhasil diparse.', $stats, $fullText);
    }

    private function sanitizeUtf8(string $text): string
    {
        return preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text);
    }

    public function parseDocument(RegulationDocument $document): array
    {
        $fullPath = Storage::disk('public')->path($document->file_path);

        if (! file_exists($fullPath)) {
            return $this->result('error', 'File tidak ditemukan.');
        }

        set_time_limit(300);

        $pdfType = $this->detectPdfType($document->file_path);

        if ($pdfType !== 'text') {
            return $this->result('error', 'Dokumen tambahan hanya bisa diparse jika berformat teks.');
        }

        $pages = $this->documentParser->extractAllPagesText($document->file_path);

        if (empty($pages)) {
            return $this->result('error', 'Gagal mengekstrak teks dari dokumen.');
        }

        $totalPages = count($pages);
        $parsedPages = array_filter($pages, fn ($p) => $p['char_count'] > 0);
        $parsedCount = count($parsedPages);
        $percentParsed = $totalPages > 0 ? round(($parsedCount / $totalPages) * 100) : 0;

        $fullText = collect($pages)->pluck('text')->implode("\n\n");

        $parseStatus = $percentParsed >= 100 ? 'complete' : ($percentParsed > 0 ? 'incomplete' : 'not_parsed');

        $stats = [
            'pdf_type' => $pdfType,
            'total_pages' => $totalPages,
            'parsed_pages' => $parsedCount,
            'empty_pages' => $totalPages - $parsedCount,
            'percent_parsed' => $percentParsed,
            'normal_pages' => $pdfType === 'text' ? $parsedCount : 0,
            'ocr_pages' => $pdfType === 'image' ? $parsedCount : 0,
            'char_total' => array_sum(array_column($pages, 'char_count')),
            'used_ocr' => $pdfType === 'image',
        ];

        $document->update([
            'parsed_at' => now(),
            'parse_status' => $parseStatus,
            'parsed_text' => $this->sanitizeUtf8($fullText),
            'parse_stats' => $stats,
        ]);

        return $this->result('success', 'Dokumen berhasil diparse.', $stats, $fullText);
    }

    public function parseDocumentChoice(RegulationDocument $document, string $method): array
    {
        $fullPath = Storage::disk('public')->path($document->file_path);

        if (! file_exists($fullPath)) {
            return $this->result('error', 'File tidak ditemukan.');
        }

        set_time_limit(300);

        if ($method === 'ocr') {
            $pages = $this->ocrDocument($document);
        } else {
            $pages = $this->documentParser->extractAllPagesText($document->file_path);
        }

        if (empty($pages)) {
            return $this->result('error', 'Gagal mengekstrak teks.');
        }

        $totalPages = count($pages);
        $parsedPages = array_filter($pages, fn ($p) => $p['char_count'] > 0);
        $parsedCount = count($parsedPages);
        $percentParsed = $totalPages > 0 ? round(($parsedCount / $totalPages) * 100) : 0;

        $fullText = collect($pages)->pluck('text')->implode("\n\n");

        $parseStatus = $percentParsed >= 100 ? 'complete' : ($percentParsed > 0 ? 'incomplete' : 'not_parsed');

        $stats = [
            'pdf_type' => $method,
            'total_pages' => $totalPages,
            'parsed_pages' => $parsedCount,
            'empty_pages' => $totalPages - $parsedCount,
            'percent_parsed' => $percentParsed,
            'normal_pages' => $method === 'text' ? $parsedCount : 0,
            'ocr_pages' => $method === 'ocr' ? $parsedCount : 0,
            'char_total' => array_sum(array_column($pages, 'char_count')),
            'used_ocr' => $method === 'ocr',
            'method' => $method,
        ];

        $document->update([
            'parsed_at' => now(),
            'parse_status' => $parseStatus,
            'parsed_text' => $this->sanitizeUtf8($fullText),
            'parse_stats' => $stats,
        ]);

        return $this->result('success', 'Dokumen berhasil diparse.', $stats, $fullText);
    }

    private function ocrDocument(RegulationDocument $document): array
    {
        $fullPath = Storage::disk('public')->path($document->file_path);

        $tmpDir = sys_get_temp_dir().'/ocr_doc_'.md5($fullPath).'_'.time();
        @mkdir($tmpDir, 0755, true);

        try {
            exec('pdftoppm -png -r 200 '.escapeshellarg($fullPath).' '.escapeshellarg($tmpDir.'/page'), $output, $returnCode);

            if ($returnCode !== 0) {
                return [];
            }

            $images = glob($tmpDir.'/page-*.png');
            sort($images);

            $result = [];
            foreach ($images as $index => $image) {
                $text = (new TesseractOCR($image))->lang('ind', 'eng')->run();
                $text = trim($text);
                $result[] = [
                    'page' => $index + 1,
                    'text' => $text,
                    'char_count' => mb_strlen($text),
                ];
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning("OCR document failed: {$e->getMessage()}");

            return [];
        } finally {
            array_map('unlink', glob($tmpDir.'/*'));
            @rmdir($tmpDir);
        }
    }

    private function parsePdf(string $fullPath): Document
    {
        static $parserCache = [];
        if (! isset($parserCache[$fullPath])) {
            $parser = new Parser;
            $parserCache[$fullPath] = $parser->parseFile($fullPath);
        }

        return $parserCache[$fullPath];
    }

    private function result(string $status, string $message, array $stats = [], ?string $text = null): array
    {
        return [
            'success' => $status === 'success',
            'message' => $message,
            'stats' => $stats,
            'text' => $text,
        ];
    }
}
