<?php

namespace App\Services;

use App\Models\ReviewDocument;

class ReviewDocumentReferencesService
{
    private const MAX_TEXT_LENGTH = 30000;

    public function __construct(
        private readonly RegulationAnalysisService $analysisService,
    ) {}

    public function extract(ReviewDocument $document): array
    {
        if (! $document->isParsed()) {
            return [
                'success' => false,
                'message' => 'Dokumen belum diparse. Silakan Parse PDF terlebih dahulu.',
            ];
        }

        $text = $document->pages()
            ->orderBy('page_number')
            ->pluck('content')
            ->implode("\n\n");

        if (mb_strlen(trim($text)) === 0) {
            return [
                'success' => false,
                'message' => 'Konten dokumen kosong. Silakan Parse PDF terlebih dahulu.',
            ];
        }

        $text = mb_substr($text, 0, self::MAX_TEXT_LENGTH);

        $extracted = $this->analysisService->extractRegulationsFromText($text);

        if ($extracted === null) {
            return [
                'success' => false,
                'message' => 'Gagal mengekstrak regulasi terkait dari isi dokumen.',
            ];
        }

        $references = $extracted['referenced_regulations'] ?? [];

        $rows = array_map(fn (array $r) => [
            'name' => $r['name'] ?? '',
            'number' => $r['number'] ?? null,
            'year' => $r['year'] ?? null,
            'relationship' => $r['relationship'] ?? 'dirujuk',
            'created_at' => now(),
            'updated_at' => now(),
        ], $references);

        $document->relatedReferences()->delete();
        $document->relatedReferences()->createMany($rows);

        return [
            'success' => true,
            'count' => count($rows),
            'message' => 'Berhasil menarik '.count($rows).' regulasi terkait dari dokumen.',
        ];
    }
}
