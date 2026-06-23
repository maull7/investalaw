<?php

namespace App\Services;

use App\Models\DocumentPartition;
use Illuminate\Support\Collection;

class TocExtractorService
{
    private const TOC_KEYWORDS = ['daftar isi', 'table of contents', 'daftar isi halaman'];

    private const BAB_PATTERN = '/^BAB\s+([IVXLCDM]+|\d+)\s*[\.\):–\-]?\s+(.+)$/i';

    private const TOC_ENTRY_PATTERN = '/^(?:BAB|LAMPIRAN)\s+[IVXLCDM]+\s*:?\s*/i';

    private const SUBBAB_PATTERNS = [
        '/^(\d+\.\d+\.\d+)\s+(.+)$/',  // 1.1.1 Judul
        '/^(\d+\.\d+)\s+(.+)$/',        // 1.1 Judul
        '/^([A-Z]\.)\s+(.+)$/',         // A. Judul
        '/^(\d+\.)\s+(.+)$/',           // 1. Judul
        '/^Bagian\s+([IVXLCDM]+|\d+)\s+(.+)$/i',
        '/^Paragraf\s+(.+)$/i',
    ];

    public function __construct(
        private readonly DocumentParser $documentParser
    ) {}

    /**
     * @return Collection<int, array{title: string, start_page: int|null, end_page: int|null, level: int, children: Collection}>
     */
    public function extractToc(DocumentPartition $partition): Collection
    {
        $document = $partition->reviewDocument;

        $endPage = min($partition->end_page, $partition->start_page + 20);

        $text = $this->documentParser->extractPagesFromStoragePath(
            $document->file_path,
            $partition->start_page,
            $endPage
        );

        $entries = $this->splitTocEntries($text);

        if (empty($entries)) {
            return collect();
        }

        $normalized = $this->normalizeLines($entries);
        $clean = $this->findTocContent($normalized);

        if (empty($clean)) {
            return collect();
        }

        return $this->parseStructure($clean);
    }

    private function splitTocEntries(string $text): array
    {
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
        $text = str_replace(["\t", "\r"], ' ', $text);

        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn ($l) => $l !== ''));

        if (count($lines) >= 3) {
            return $lines;
        }

        $entries = [];
        $pattern = '/\s+(?=(?:BAB|LAMPIRAN)\s+[IVXLCDM]+\s*:?)/i';

        foreach ($lines as $line) {
            $parts = preg_split($pattern, $line, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $entries[] = $part;
                }
            }
        }

        return $entries;
    }

    private function normalizeLines(array $lines): array
    {
        return array_values(array_filter(array_map(function ($l) {
            $line = preg_replace('/[\x00-\x1F\x7F]/u', '', $l);
            $line = str_replace(["\t", "\r"], ' ', $line);
            $line = preg_replace('/[\.]{3,}\s*/', '||', $line);
            $line = preg_replace('/_{4,}/', '||', $line);
            $line = preg_replace('/\s{2,}/', ' ', $line);
            $line = preg_replace('/^[\s\.]+|[\s\.]+$/u', '', $line);

            return trim($line);
        }, $lines)));
    }

    private function findTocContent(array $lines): array
    {
        $keywordIdx = null;
        foreach ($lines as $i => $line) {
            $lower = mb_strtolower($line);
            foreach (self::TOC_KEYWORDS as $keyword) {
                if (str_contains($lower, $keyword)) {
                    $keywordIdx = $i;
                    break 2;
                }
            }
        }

        $entryIndices = [];
        foreach ($lines as $i => $line) {
            if (preg_match(self::TOC_ENTRY_PATTERN, $line)) {
                $entryIndices[] = $i;
            }
        }

        if ($keywordIdx !== null && ! empty($entryIndices)) {
            $entriesAfterKeyword = array_values(array_filter($entryIndices, fn ($idx) => $idx > $keywordIdx));

            if (! empty($entriesAfterKeyword)) {
                $start = $keywordIdx + 1;
                $lastEntry = end($entriesAfterKeyword);
                $end = min($lastEntry + 15, count($lines));

                return array_slice($lines, $start, $end - $start);
            }
        }

        if (count($entryIndices) >= 2) {
            $start = $entryIndices[0];
            $end = min(end($entryIndices) + 15, count($lines));

            return array_slice($lines, $start, $end - $start);
        }

        if ($keywordIdx !== null) {
            return array_slice($lines, $keywordIdx + 1, 80);
        }

        if (! empty($entryIndices)) {
            $start = max(0, $entryIndices[0] - 2);
            $end = min($entryIndices[0] + 20, count($lines));

            return array_slice($lines, $start, $end - $start);
        }

        $pageLines = array_values(array_filter($lines, fn ($l) => preg_match('/\|\|\d+$/', $l)));

        if (count($pageLines) >= 3) {
            $firstPageLine = null;
            foreach ($lines as $i => $l) {
                if (preg_match('/\|\|\d+$/', $l)) {
                    $firstPageLine = $i;
                    break;
                }
            }

            if ($firstPageLine !== null) {
                $start = max(0, $firstPageLine - 2);
                $end = min($firstPageLine + count($pageLines) + 5, count($lines));

                return array_slice($lines, $start, $end - $start);
            }
        }

        return array_slice($lines, 0, min(80, count($lines)));
    }

    /**
     * @return Collection<int, array{title: string, start_page: int|null, end_page: int|null, level: int, children: Collection}>
     */
    private function parseStructure(array $lines): Collection
    {
        $result = collect();
        $currentBab = null;
        $currentSubbabs = collect();
        $currentIsi = collect();

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $page = $this->extractPageNumber($line);
            $title = trim(preg_replace('/\|\|.*$/', '', $line));

            if ($page !== null) {
                $title = trim(preg_replace('/\s+\d+$/', '', $title));
            } elseif (preg_match('/^(.+?)\s+(\d+)\s+(.+)$/', $title, $m)) {
                $page = (int) $m[2];
                $title = trim($m[1].' '.$m[3]);
            }

            $matched = $this->matchPattern($title, $page, $lines);

            if ($matched === null) {
                continue;
            }

            [$type, $cleanTitle] = $matched;

            if ($type === 'bab') {
                if ($currentBab !== null) {
                    $this->finalizeBab($currentBab, $currentSubbabs, $currentIsi, $result);
                }
                $currentBab = [
                    'title' => $cleanTitle,
                    'start_page' => $page,
                    'end_page' => null,
                    'level' => 1,
                    'children' => collect(),
                ];
                $currentSubbabs = collect();
                $currentIsi = collect();
            } elseif ($type === 'subbab' && $currentBab !== null) {
                $this->finalizeSubbab($currentSubbabs, $currentIsi);
                $currentSubbabs->push([
                    'title' => $cleanTitle,
                    'start_page' => $page,
                    'end_page' => null,
                    'level' => 2,
                    'children' => collect(),
                ]);
                $currentIsi = collect();
            } elseif ($type === 'isi' && $currentSubbabs->isNotEmpty()) {
                $currentIsi->push([
                    'title' => $cleanTitle,
                    'start_page' => $page,
                    'end_page' => null,
                    'level' => 3,
                ]);
            }
        }

        $this->finalizeBab($currentBab, $currentSubbabs, $currentIsi, $result);

        return $this->resolvePageRanges($result);
    }

    private function matchPattern(string $title, ?int $page, array $lines): ?array
    {
        if (preg_match(self::BAB_PATTERN, $title, $m)) {
            return ['bab', 'BAB '.$m[1].' '.trim($m[2])];
        }

        if (preg_match('/^(\d+\.\d+\.\d+)\s+(.+)$/', $title, $m)) {
            preg_match('/^(\d+\.\d+)\./', $m[1], $parent);
            $parentKey = $parent[1] ?? null;
            if ($parentKey) {
                return ['isi', trim($m[0])];
            }

            return ['subbab', trim($m[1].' '.$m[2])];
        }

        foreach (self::SUBBAB_PATTERNS as $pattern) {
            if (preg_match($pattern, $title, $m)) {
                return ['subbab', trim($m[0])];
            }
        }

        if ($page !== null && trim($title) !== '') {
            return $this->detectGenericEntry($title);
        }

        return null;
    }

    private function detectGenericEntry(string $title): ?array
    {
        $lower = mb_strtolower(trim($title));

        if (preg_match('/^(bab|lampiran|bagian|paragraf|pasal|ayat)\s/i', $lower)) {
            return ['bab', trim($title)];
        }

        if (preg_match('/^[ivxlcdm]+[\.\):\s]/i', $lower)) {
            return ['bab', trim($title)];
        }

        if (preg_match('/^\d+[\.\)]\s+/', $title)) {
            return ['subbab', trim($title)];
        }

        if (preg_match('/^[a-z][\.\)]\s+/i', $title)) {
            return ['subbab', trim($title)];
        }

        return ['subbab', trim($title)];
    }

    private function finalizeBab(?array &$bab, Collection &$subbabs, Collection &$isi, Collection &$result): void
    {
        if ($bab === null) {
            return;
        }
        $this->finalizeSubbab($subbabs, $isi);
        $bab['children'] = $subbabs;
        $result->push($bab);
        $bab = null;
    }

    private function finalizeSubbab(Collection &$subbabs, Collection &$isi): void
    {
        if ($subbabs->isEmpty() || $isi->isEmpty()) {
            return;
        }
        $last = $subbabs->pop();
        $last['children'] = $isi;
        $subbabs->push($last);
    }

    private function extractPageNumber(string $line): ?int
    {
        if (preg_match('/\|\|(\d+)$/', $line, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/\s+(\d+)$/', trim($line), $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * @param  Collection<int, array>  $items
     * @return Collection<int, array>
     */
    private function resolvePageRanges(Collection $items): Collection
    {
        $all = collect();
        foreach ($items as $bab) {
            $all->push([...$bab, '_type' => 'bab']);
            foreach ($bab['children'] ?? [] as $sub) {
                $all->push([...$sub, '_type' => 'sub']);
                foreach ($sub['children'] ?? [] as $isi) {
                    $all->push([...$isi, '_type' => 'isi']);
                }
            }
        }

        for ($i = 0; $i < $all->count(); $i++) {
            $item = $all[$i];
            $next = $all[$i + 1] ?? null;

            if ($item['_type'] === 'isi') {
                $item['end_page'] = $next ? ($next['start_page'] ?? $item['start_page']) - 1 : ($item['start_page'] ?? 1);
                if (($item['end_page'] ?? 0) < ($item['start_page'] ?? 0)) {
                    $item['end_page'] = $item['start_page'];
                }
                $all[$i] = $item;
            }
        }

        for ($i = 0; $i < $all->count(); $i++) {
            $item = $all[$i];
            $nextBab = collect($all->slice($i + 1))->firstWhere('_type', 'bab');
            $nextSub = collect($all->slice($i + 1))->firstWhere('_type', 'sub');

            if ($item['_type'] === 'sub') {
                $nextPage = $nextBab['start_page'] ?? $nextSub['start_page'] ?? $item['children']->last()['end_page'] ?? $item['start_page'];
                $item['end_page'] = $nextPage - 1;
                if (($item['end_page'] ?? 0) < ($item['start_page'] ?? 0)) {
                    $item['end_page'] = $item['children']->last()['end_page'] ?? $item['start_page'];
                }
                $all[$i] = $item;
            }
        }

        for ($i = 0; $i < $all->count(); $i++) {
            $item = $all[$i];
            $next = collect($all->slice($i + 1))->firstWhere('_type', 'bab');

            if ($item['_type'] === 'bab') {
                $firstChild = $item['children']->first();
                $lastChild = $item['children']->last();
                if ($item['start_page'] === null) {
                    $item['start_page'] = $firstChild['start_page'] ?? 1;
                }
                $item['end_page'] = $next ? ($next['start_page'] ?? $item['start_page'] + 10) - 1 : ($lastChild['end_page'] ?? $item['start_page'] + 10);
                if (($item['end_page'] ?? 0) < ($item['start_page'] ?? 0)) {
                    $item['end_page'] = $item['start_page'] + 5;
                }
                $all[$i] = $item;
            }
        }

        return $all->where('_type', 'bab')->values()->map(fn ($bab) => tap($bab, fn (&$b) => $b['children'] = collect($b['children'])->map(fn ($sub) => tap($sub, fn (&$s) => $s['children'] = collect($s['children'] ?? [])))));
    }
}
