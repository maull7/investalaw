<?php

namespace App\Services;

use App\Models\Regulation;
use Carbon\Carbon;

class RegulationReferencesService
{
    public function __construct(
        private readonly RegulationAnalysisService $analysisService,
    ) {}

    public function extract(Regulation $regulation): array
    {
        if (! $regulation->parsed_text) {
            return [
                'success' => false,
                'message' => 'Regulasi belum diparse. Lakukan Parse PDF terlebih dahulu.',
            ];
        }

        $text = $this->analysisService->getContentText($regulation);

        $extracted = $this->analysisService->extractRegulationsFromText($text);

        if ($extracted === null) {
            return [
                'success' => false,
                'message' => 'Gagal mengekstrak peraturan terkait dari teks regulasi.',
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

        $regulation->relatedReferences()->delete();
        $regulation->relatedReferences()->createMany($rows);

        $dates = $this->extractRegulationDates($regulation);

        $revoked = collect($rows)->where('relationship', 'dicabut')->count();

        return [
            'success' => true,
            'count' => count($rows),
            'related_count' => count($rows) - $revoked,
            'revoked_count' => $revoked,
            'message' => 'Berhasil mengekstrak '.count($rows).' peraturan terkait.'
                .($dates ? ' Tanggal Ditetapkan: '.$dates['tanggal_tetapkan'].', Tanggal Diundangkan: '.$dates['tanggal_diundangkan'] : ''),
        ];
    }

    private function extractRegulationDates(Regulation $regulation): array
    {
        // ponytail: tanggal ada di footer, ambil tail saja; scan penuh jika format dokumen beda
        $tail = mb_substr($regulation->parsed_text, -4000);
        $dates = $this->analysisService->extractDatesFromText($tail);

        $update = [];

        foreach (['tanggal_tetapkan', 'tanggal_diundangkan'] as $field) {
            if ($regulation->{$field} !== null) {
                continue;
            }

            $value = $dates[$field] ?? null;

            if (! $value) {
                continue;
            }

            try {
                $update[$field] = Carbon::parse($value)->toDateString();
            } catch (\Throwable $e) {
                continue;
            }
        }

        if (! empty($update)) {
            $regulation->update($update);
        }

        return [
            'tanggal_tetapkan' => $regulation->tanggal_tetapkan?->format('d/m/Y'),
            'tanggal_diundangkan' => $regulation->tanggal_diundangkan?->format('d/m/Y'),
        ];
    }
}
