<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;

class DocumentExtractorService
{
    private const MAX_CHARS = 15000;

    private const SUPPORTED_TYPES = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'jpg',
        'jpeg',
        'png',
    ];

    public function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'pdf' => $this->extractFromPdf($file),
            'doc', 'docx' => $this->extractFromWord($file),
            'xls', 'xlsx' => $this->extractFromExcel($file),
            'jpg', 'jpeg', 'png' => $this->extractFromImage($file),
            default => '',
        };
    }

    public function extractFromPdf(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            $clean = $this->cleanText($text);

            if (mb_strlen(trim($clean)) < 50) {
                return $this->ocrFallback($path);
            }

            return $this->truncate($clean);
        } catch (\Exception $e) {
            Log::warning("Failed to extract text from PDF: {$e->getMessage()}");

            return $this->ocrFallback($path);
        }
    }

    public function extractFromWord(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        try {
            $phpWord = IOFactory::load($path);
            $texts = [];

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $texts[] = $this->extractTextFromElement($element);
                }
            }

            $result = implode("\n", array_filter($texts));

            return $this->truncate($this->cleanText($result));
        } catch (\Exception $e) {
            Log::warning("Failed to extract text from Word: {$e->getMessage()}");

            return '';
        }
    }

    public function extractFromImage(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        try {
            $text = (new TesseractOCR($path))
                ->lang('ind', 'eng')
                ->run();

            return $this->truncate($this->cleanText($text));
        } catch (\Exception $e) {
            Log::warning("Failed to extract text from image via OCR: {$e->getMessage()}");

            return '';
        }
    }

    public function extractFromExcel(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        try {
            $reader = SpreadsheetIOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $texts = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                $rows = [];

                foreach ($sheet->toArray() as $row) {
                    $cells = array_filter($row, fn ($cell) => $cell !== null && $cell !== '');
                    if (! empty($cells)) {
                        $rows[] = implode("\t", array_map('strval', $cells));
                    }
                }

                if (! empty($rows)) {
                    $texts[] = "=== Sheet: {$sheetName} ===\n".implode("\n", $rows);
                }
            }

            return $this->truncate($this->cleanText(implode("\n\n", $texts)));
        } catch (\Exception $e) {
            Log::warning("Failed to extract text from Excel: {$e->getMessage()}");

            return '';
        }
    }

    public function getFileType(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'jpg', 'jpeg' => 'image',
            'png' => 'image',
            default => 'unknown',
        };
    }

    public function isSupported(UploadedFile $file): bool
    {
        return in_array(
            strtolower($file->getClientOriginalExtension()),
            self::SUPPORTED_TYPES
        );
    }

    private function ocrFallback(string $path): string
    {
        $tmpDir = sys_get_temp_dir().'/ocr_consult_'.md5($path).'_'.time();
        @mkdir($tmpDir, 0755, true);

        try {
            exec('pdftoppm -png -r 150 '.escapeshellarg($path).' '.escapeshellarg($tmpDir.'/page'), $output, $returnCode);

            if ($returnCode !== 0) {
                return '';
            }

            $images = glob($tmpDir.'/page-*.png');
            sort($images);

            $texts = [];
            foreach (array_slice($images, 0, 10) as $image) {
                $text = (new TesseractOCR($image))->lang('ind', 'eng')->run();
                if (trim($text)) {
                    $texts[] = trim($text);
                }
            }

            return $this->truncate($this->cleanText(implode("\n\n", $texts)));
        } catch (\Exception $e) {
            Log::warning("OCR fallback failed for consultation PDF: {$e->getMessage()}");

            return '';
        } finally {
            array_map('unlink', glob($tmpDir.'/*'));
            @rmdir($tmpDir);
        }
    }

    private function extractTextFromElement(mixed $element): string
    {
        if (method_exists($element, 'getText')) {
            return $element->getText();
        }

        if (method_exists($element, 'getElements')) {
            $texts = [];
            foreach ($element->getElements() as $child) {
                $texts[] = $this->extractTextFromElement($child);
            }

            return implode('', $texts);
        }

        return '';
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        $text = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text);

        return $text;
    }

    private function truncate(string $text, int $maxChars = self::MAX_CHARS): string
    {
        if (mb_strlen($text) > $maxChars) {
            return mb_substr($text, 0, $maxChars).'... [konten dipotong]';
        }

        return $text;
    }
}
