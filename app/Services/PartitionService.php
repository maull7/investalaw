<?php

namespace App\Services;

use App\Models\DocumentPartition;
use App\Models\ReviewDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartitionService
{
    public function __construct(
        private readonly DocumentParser $documentParser
    ) {}

    /**
     * @param  array<int, array{name: string, start_page: int, end_page: int, description?: string}>  $partitions
     */
    public function savePartitions(ReviewDocument $document, array $partitions): void
    {
        $totalPages = $this->ensureTotalPages($document);

        $this->validatePartitions($partitions, $totalPages);

        DB::transaction(function () use ($document, $partitions) {
            $document->partitions()->delete();

            foreach ($partitions as $index => $partition) {
                DocumentPartition::create([
                    'review_document_id' => $document->id,
                    'name' => $partition['name'],
                    'start_page' => $partition['start_page'],
                    'end_page' => $partition['end_page'],
                    'description' => $partition['description'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        });
    }

    public function ensureTotalPages(ReviewDocument $document): int
    {
        if ($document->total_pages) {
            return $document->total_pages;
        }

        $totalPages = $this->documentParser->getPageCount($document->file_path);
        $document->update(['total_pages' => $totalPages]);

        return $totalPages;
    }

    /**
     * @param  array<int, array{name: string, start_page: int, end_page: int}>  $partitions
     *
     * @throws ValidationException
     */
    private function validatePartitions(array $partitions, int $totalPages): void
    {
        if (empty($partitions)) {
            throw ValidationException::withMessages(['partitions' => 'Minimal satu partisi diperlukan.']);
        }

        foreach ($partitions as $i => $partition) {
            if (empty($partition['name'])) {
                throw ValidationException::withMessages(["partitions.{$i}.name" => 'Nama partisi wajib diisi.']);
            }
            if (empty($partition['start_page']) || $partition['start_page'] < 1) {
                throw ValidationException::withMessages(["partitions.{$i}.start_page" => 'Halaman awal harus >= 1.']);
            }
            if (empty($partition['end_page']) || $partition['end_page'] < $partition['start_page']) {
                throw ValidationException::withMessages(["partitions.{$i}.end_page" => 'Halaman akhir harus >= halaman awal.']);
            }
            if ($totalPages > 0 && $partition['end_page'] > $totalPages) {
                throw ValidationException::withMessages(["partitions.{$i}.end_page" => "Halaman akhir tidak boleh melebihi total halaman ({$totalPages})."]);
            }
        }

        $sorted = $partitions;
        usort($sorted, fn ($a, $b) => $a['start_page'] <=> $b['start_page']);

        for ($i = 1; $i < count($sorted); $i++) {
            if ($sorted[$i]['start_page'] <= $sorted[$i - 1]['end_page']) {
                throw ValidationException::withMessages([
                    'partitions' => "Range halaman partisi \"{$sorted[$i]['name']}\" overlap dengan \"{$sorted[$i - 1]['name']}\".",
                ]);
            }
        }
    }
}
