<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class DocumentParser
{
    private const MAX_CHARS = 30000;

    public function extractFromStoragePath(string $path): string
    {
        $fullPath = Storage::disk('public')->path($path);

        if (! file_exists($fullPath)) {
            Log::warning("Document file not found: {$path}");

            return '';
        }

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($fullPath);
            $text = $pdf->getText();

            return $this->cleanAndTruncate($text);
        } catch (\Exception $e) {
            Log::warning("Failed to parse PDF {$path}: {$e->getMessage()}");

            return '';
        }
    }

    private function cleanAndTruncate(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) > self::MAX_CHARS) {
            $text = mb_substr($text, 0, self::MAX_CHARS).'... [konten dipotong]';
        }

        return $text;
    }
}
