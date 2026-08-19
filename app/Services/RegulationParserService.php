<?php

namespace App\Services;

use App\Models\Regulation;
use App\Models\RegulationDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;

class RegulationParserService
{
    public const CHUNK_SIZE = 10;

    private const OCR_GOOD_THRESHOLD = 0.9;

    public function parseRegulationChunk(Regulation $regulation, int $fromPage, ?callable $progress = null): array
    {
        $fullPath = Storage::disk('public')->path($regulation->file_path);

        if (! file_exists($fullPath)) {
            return ['success' => false, 'message' => 'File tidak ditemukan.'];
        }

        set_time_limit(600);

        $total = $this->getTotalPages($fullPath);
        if ($total <= 0) {
            return ['success' => false, 'message' => 'Gagal membaca jumlah halaman PDF.'];
        }

        // Simpan total_pages dulu sebelum OCR supaya UI progress langsung tahu total & bisa tampilkan "halaman X-Y / total".
        $stats = $regulation->parse_stats ?? [];
        if (($stats['total_pages'] ?? null) !== $total || ($stats['chunk_size'] ?? null) !== self::CHUNK_SIZE) {
            $regulation->update(['parse_stats' => array_merge($stats, [
                'total_pages' => $total,
                'chunk_size' => self::CHUNK_SIZE,
                'resume_page' => $fromPage,
                'completed_pages' => 0,
            ])]);
        }

        $toPage = min($fromPage + self::CHUNK_SIZE - 1, $total);
        $pages = $this->ocrPdfRange($fullPath, $fromPage, $toPage, true);

        if (empty($pages)) {
            return ['success' => false, 'message' => "Gagal OCR halaman {$fromPage}-{$toPage}."];
        }

        $pageCounts = $stats['page_counts'] ?? [];
        foreach ($pages as $p) {
            $pageCounts[$p['page']] = $p['char_count'];
        }

        $existingText = $regulation->parsed_text ?? '';
        $chunkText = collect($pages)->pluck('text')->implode("\n\n");
        $newText = trim($existingText."\n\n".$chunkText);

        $done = $toPage >= $total;
        $nextPage = $done ? null : $toPage + 1;

        $stats = array_merge($stats, [
            'pdf_type' => 'image',
            'total_pages' => $total,
            'chunk_size' => self::CHUNK_SIZE,
            'resume_page' => $nextPage,
            'completed_pages' => $toPage,
            'page_counts' => $pageCounts,
        ]);

        $regulation->update([
            'parsed_text' => $this->sanitizeUtf8($newText),
            'parse_stats' => $stats,
            'parse_progress' => (int) round(($toPage / $total) * 100),
        ]);

        if ($progress) {
            $progress((int) round(($toPage / $total) * 100));
        }

        return [
            'success' => true,
            'done' => $done,
            'next_page' => $nextPage,
            'total' => $total,
        ];
    }

    private function getTotalPages(string $fullPath): int
    {
        exec('pdfinfo '.escapeshellarg($fullPath).' 2>/dev/null', $output);

        foreach ($output as $line) {
            if (preg_match('/^Pages:\s+(\d+)/', $line, $matches)) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    private function sanitizeUtf8(string $text): string
    {
        // Hilangkan byte sequences yang tidak valid agar PREG tidak gagal.
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Hapus karakter di luar BMP (emoji, dll.) agar kompatibel dengan penyimpanan.
        $cleaned = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text);

        return $cleaned ?? $text;
    }

    private function detectContentStartPage(array $pages): ?int
    {
        foreach ($pages as $page) {
            $text = trim($page['text']);
            if (preg_match('/^BAB\s+I/i', $text)) {
                return $page['page'];
            }
        }

        return null;
    }

    public function parseDocumentChunk(RegulationDocument $document, int $fromPage, ?callable $progress = null): array
    {
        $fullPath = Storage::disk('public')->path($document->file_path);

        if (! file_exists($fullPath)) {
            return ['success' => false, 'message' => 'File tidak ditemukan.'];
        }

        set_time_limit(600);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if (! in_array($ext, ['pdf', 'docx'])) {
            return ['success' => false, 'message' => 'Format file tidak didukung. Hanya PDF dan DOCX.'];
        }

        if ($ext === 'docx') {
            $pages = $this->extractDocxText($fullPath);
            $this->finalizeTextParsed($document, $pages, 'docx');

            return ['success' => true, 'done' => true, 'next_page' => null, 'total' => 1];
        }

        $total = $this->getTotalPages($fullPath);
        if ($total <= 0) {
            return ['success' => false, 'message' => 'Gagal membaca jumlah halaman PDF.'];
        }

        // Simpan total_pages dulu sebelum OCR supaya UI progress langsung tahu total & bisa tampilkan "halaman X-Y / total".
        $stats = $document->parse_stats ?? [];
        if (($stats['total_pages'] ?? null) !== $total || ($stats['chunk_size'] ?? null) !== self::CHUNK_SIZE) {
            $document->update(['parse_stats' => array_merge($stats, [
                'total_pages' => $total,
                'chunk_size' => self::CHUNK_SIZE,
                'resume_page' => $fromPage,
                'completed_pages' => 0,
            ])]);
        }

        $toPage = min($fromPage + self::CHUNK_SIZE - 1, $total);
        $pages = $this->ocrPdfRange($fullPath, $fromPage, $toPage, false);

        if (empty($pages)) {
            return ['success' => false, 'message' => "Gagal OCR halaman {$fromPage}-{$toPage}."];
        }

        $pageCounts = $stats['page_counts'] ?? [];
        foreach ($pages as $p) {
            $pageCounts[$p['page']] = $p['char_count'];
        }

        $existingText = $document->parsed_text ?? '';
        $chunkText = collect($pages)->pluck('text')->implode("\n\n");
        $newText = trim($existingText."\n\n".$chunkText);

        $done = $toPage >= $total;
        $nextPage = $done ? null : $toPage + 1;

        $stats = array_merge($stats, [
            'pdf_type' => 'image',
            'total_pages' => $total,
            'chunk_size' => self::CHUNK_SIZE,
            'resume_page' => $nextPage,
            'completed_pages' => $toPage,
            'page_counts' => $pageCounts,
        ]);

        $document->update([
            'parsed_text' => $this->sanitizeUtf8($newText),
            'parse_stats' => $stats,
            'parse_progress' => (int) round(($toPage / $total) * 100),
        ]);

        if ($progress) {
            $progress((int) round(($toPage / $total) * 100));
        }

        return [
            'success' => true,
            'done' => $done,
            'next_page' => $nextPage,
            'total' => $total,
        ];
    }

    public function extractTextPages(RegulationDocument|Regulation $model, string $pdfType): void
    {
        $fullPath = Storage::disk('public')->path($model->file_path);
        $parser = new Parser;

        try {
            $pdf = $parser->parseFile($fullPath);
            $pages = [];
            foreach ($pdf->getPages() as $index => $page) {
                $text = trim(preg_replace('/\s+/', ' ', $page->getText()));
                $pages[] = [
                    'page' => $index + 1,
                    'text' => $text,
                    'char_count' => mb_strlen($text),
                ];
            }

            $this->finalizeTextParsed($model, $pages, $pdfType);
        } catch (\Exception $e) {
            Log::warning("Text extraction failed for {$model->file_path}: {$e->getMessage()}");
            $model->update(['parse_status' => 'failed', 'parse_progress' => null, 'parse_error' => mb_substr($e->getMessage(), 0, 500)]);
        }
    }

    private function finalizeTextParsed(RegulationDocument|Regulation $model, array $pages, string $pdfType): void
    {
        $totalPages = count($pages);
        $parsedPages = array_filter($pages, fn ($p) => $p['char_count'] > 0);
        $parsedCount = count($parsedPages);
        $percentParsed = $totalPages > 0 ? round(($parsedCount / $totalPages) * 100) : 0;

        $fullText = collect($pages)->pluck('text')->implode("\n\n");

        $parseStatus = $percentParsed >= 95 ? 'complete' : ($percentParsed > 0 ? 'incomplete' : 'not_parsed');

        $contentStartPage = $this->detectContentStartPage($pages);
        $pageOffset = $contentStartPage ? $contentStartPage - 1 : 0;

        $stats = [
            'pdf_type' => $pdfType,
            'total_pages' => $totalPages,
            'parsed_pages' => $parsedCount,
            'empty_pages' => $totalPages - $parsedCount,
            'percent_parsed' => $percentParsed,
            'normal_pages' => $parsedCount,
            'ocr_pages' => 0,
            'char_total' => array_sum(array_column($pages, 'char_count')),
            'used_ocr' => false,
            'content_start_page' => $contentStartPage,
            'page_offset' => $pageOffset,
            'ocr_engine' => null,
            'ocr_dpi' => null,
            'ocr_langs' => null,
        ];

        $model->update([
            'parsed_at' => now(),
            'parse_status' => $parseStatus,
            'parsed_text' => $this->sanitizeUtf8($fullText),
            'parse_stats' => $stats,
            'parse_progress' => 100,
        ]);
    }

    public function finalizeOcrParsed(RegulationDocument|Regulation $model): void
    {
        $stats = $model->parse_stats ?? [];
        $totalPages = $stats['total_pages'] ?? 0;
        $pageCounts = $stats['page_counts'] ?? [];
        $parsedCount = count(array_filter($pageCounts, fn ($c) => $c > 0));
        $percentParsed = $totalPages > 0 ? round(($parsedCount / $totalPages) * 100) : 0;

        $parseStatus = $percentParsed >= 95 ? 'complete' : ($percentParsed > 0 ? 'incomplete' : 'not_parsed');

        $contentStartPage = $stats['content_start_page'] ?? null;
        $pageOffset = $contentStartPage ? $contentStartPage - 1 : 0;

        $finalStats = array_merge($stats, [
            'parsed_pages' => $parsedCount,
            'empty_pages' => $totalPages - $parsedCount,
            'percent_parsed' => $percentParsed,
            'normal_pages' => 0,
            'ocr_pages' => $parsedCount,
            'char_total' => array_sum($pageCounts),
            'used_ocr' => true,
            'content_start_page' => $contentStartPage,
            'page_offset' => $pageOffset,
            'ocr_engine' => 'tesseract',
            'ocr_dpi' => 200,
            'ocr_langs' => 'ind+eng',
        ]);

        $model->update([
            'parsed_at' => now(),
            'parse_status' => $parseStatus,
            'parse_stats' => $finalStats,
            'parse_progress' => 100,
        ]);
    }

    private function ocrPdfRange(string $fullPath, int $fromPage, int $toPage, bool $psm6): array
    {
        $tmpDir = sys_get_temp_dir().'/ocr_chunk_'.md5($fullPath.$fromPage.$toPage).'_'.time();
        @mkdir($tmpDir, 0755, true);

        try {
            exec("pdftoppm -png -r 200 -f {$fromPage} -l {$toPage} ".escapeshellarg($fullPath).' '.escapeshellarg($tmpDir.'/page'), $output, $returnCode);

            if ($returnCode !== 0) {
                return [];
            }

            $images = glob($tmpDir.'/page-*.png');
            sort($images);

            $result = [];
            foreach ($images as $index => $image) {
                $text = $this->ocrWithBestOrientation($image, $psm6, $tmpDir);
                $result[] = [
                    'page' => $fromPage + $index,
                    'text' => $text,
                    'char_count' => mb_strlen($text),
                ];
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning("OCR chunk failed: {$e->getMessage()}");

            return [];
        } finally {
            array_map('unlink', glob($tmpDir.'/*'));
            @rmdir($tmpDir);
        }
    }

    private function ocrWithBestOrientation(string $image, bool $psm6, string $tmpDir): string
    {
        try {
            $text = $this->ocrImage($image, $psm6);
            if ($this->ocrQuality($text) >= self::OCR_GOOD_THRESHOLD) {
                return $text;
            }
        } catch (\Throwable $e) {
            Log::warning("OCR initial failed: {$e->getMessage()}");
            $text = '';
        }

        $best = $text;
        $bestScore = $this->ocrQuality($best);

        foreach (['180', '90', '270', 'flip'] as $variant) {
            $rotated = $this->rotateImage($image, $variant, $tmpDir);
            if ($rotated === null) {
                continue;
            }
            try {
                $candidate = $this->ocrImage($rotated, $psm6);
            } finally {
                @unlink($rotated);
            }
            $score = $this->ocrQuality($candidate);
            if ($score > $bestScore) {
                $best = $candidate;
                $bestScore = $score;
            }
            if ($bestScore >= self::OCR_GOOD_THRESHOLD) {
                break;
            }
        }

        return $best;
    }

    private function ocrImage(string $image, bool $psm6): string
    {
        $ocr = (new TesseractOCR($image))->lang('ind', 'eng');
        if ($psm6) {
            $ocr->psm(6);
        }

        return trim($ocr->run());
    }

    private function ocrQuality(string $text): float
    {
        $len = mb_strlen($text);
        if ($len === 0) {
            return 0.0;
        }

        $letters = preg_match_all('/[a-zA-Z]/u', $text);
        $digits = preg_match_all('/[0-9]/u', $text);
        $spaces = preg_match_all('/\s/u', $text);

        return ($letters + $digits + $spaces) / $len;
    }

    private function rotateImage(string $image, string $variant, string $tmpDir): ?string
    {
        $script = $tmpDir.'/rotate.py';
        if (! file_exists($script)) {
            $code = <<<'PY'
import sys
from PIL import Image
src, variant, dst = sys.argv[1], sys.argv[2], sys.argv[3]
im = Image.open(src)
if variant == 'flip':
    im.transpose(Image.FLIP_LEFT_RIGHT).save(dst)
else:
    im.rotate(-int(variant), expand=True).save(dst)
PY;
            file_put_contents($script, $code);
        }

        $out = $tmpDir.'/rot_'.md5($image).'_'.$variant.'.png';
        exec('python3 '.escapeshellarg($script).' '.escapeshellarg($image).' '.escapeshellarg($variant).' '.escapeshellarg($out).' 2>&1', $output, $rc);

        return $rc === 0 && file_exists($out) ? $out : null;
    }

    private function extractDocxText(string $fullPath): array
    {
        $zip = new \ZipArchive;

        if ($zip->open($fullPath) !== true) {
            return [];
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return [];
        }

        $text = str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n+/', "\n", trim($text));

        return [[
            'page' => 1,
            'text' => $text,
            'char_count' => mb_strlen($text),
        ]];
    }
}
