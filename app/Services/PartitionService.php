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

    public function savePartitions(ReviewDocument $document, array $partitions): void
    {
        $this->validatePartitions($partitions);

        DB::transaction(function () use ($document, $partitions) {
            $document->partitions()->delete();

            $sortOrder = 0;
            foreach ($partitions as $babData) {
                $bab = DocumentPartition::create([
                    'review_document_id' => $document->id,
                    'parent_id' => null,
                    'name' => $babData['name'],
                    'start_page' => $babData['start_page'],
                    'end_page' => $babData['end_page'],
                    'description' => $babData['description'] ?? null,
                    'has_toc' => $babData['has_toc'] ?? false,
                    'sort_order' => $sortOrder++,
                    'level' => 0,
                ]);

                $subSortOrder = 0;
                foreach ($babData['children'] ?? [] as $subData) {
                    $subbab = DocumentPartition::create([
                        'review_document_id' => $document->id,
                        'parent_id' => $bab->id,
                        'name' => $subData['name'],
                        'start_page' => $subData['start_page'],
                        'end_page' => $subData['end_page'],
                        'description' => $subData['description'] ?? null,
                        'sort_order' => $subSortOrder++,
                        'level' => 1,
                    ]);

                    $contentSortOrder = 0;
                    foreach ($subData['children'] ?? [] as $contentData) {
                        DocumentPartition::create([
                            'review_document_id' => $document->id,
                            'parent_id' => $subbab->id,
                            'name' => $contentData['name'],
                            'start_page' => $contentData['start_page'],
                            'end_page' => $contentData['end_page'],
                            'description' => $contentData['description'] ?? null,
                            'sort_order' => $contentSortOrder++,
                            'level' => 2,
                        ]);
                    }
                }
            }
        });
    }

    public function saveTocChildren(DocumentPartition $partition, array $babs): void
    {
        DB::transaction(function () use ($partition, $babs) {
            $partition->children()->delete();

            $babOrder = 0;
            foreach ($babs as $babData) {
                $bab = DocumentPartition::create([
                    'review_document_id' => $partition->review_document_id,
                    'parent_id' => $partition->id,
                    'name' => $babData['title'],
                    'start_page' => $babData['start_page'] ?? 1,
                    'end_page' => $babData['end_page'] ?? ($babData['start_page'] ?? 1) + 5,
                    'sort_order' => $babOrder++,
                    'level' => 1,
                ]);

                $subOrder = 0;
                foreach ($babData['children'] ?? [] as $subData) {
                    $subbab = DocumentPartition::create([
                        'review_document_id' => $partition->review_document_id,
                        'parent_id' => $bab->id,
                        'name' => $subData['title'],
                        'start_page' => $subData['start_page'] ?? 1,
                        'end_page' => $subData['end_page'] ?? ($subData['start_page'] ?? 1) + 3,
                        'sort_order' => $subOrder++,
                        'level' => 2,
                    ]);

                    $isiOrder = 0;
                    foreach ($subData['children'] ?? [] as $isiData) {
                        DocumentPartition::create([
                            'review_document_id' => $partition->review_document_id,
                            'parent_id' => $subbab->id,
                            'name' => $isiData['title'],
                            'start_page' => $isiData['start_page'] ?? 1,
                            'end_page' => $isiData['end_page'] ?? ($isiData['start_page'] ?? 1),
                            'sort_order' => $isiOrder++,
                            'level' => 3,
                        ]);
                    }
                }
            }
        });
    }

    public function saveStructureChildren(DocumentPartition $parent, array $children, int $childLevel): void
    {
        DB::transaction(function () use ($parent, $children, $childLevel) {
            $parent->children()->delete();

            // Filter out TOC/duplicate-like entries and deduplicate
            $seen = [];
            $filtered = [];

            foreach ($children as $data) {
                $title = trim($data['title'] ?? '');

                if (empty($title)) {
                    continue;
                }

                // Skip TOC-like entries
                if (preg_match('/^(daftar\s*isi|toc|table\s*of\s*contents)/i', $title)) {
                    continue;
                }

                // Skip entries that just repeat the parent name (same BAB/LAMPIRAN number)
                $parentBare = preg_replace('/[^a-z0-9]/', '', mb_strtolower($parent->name));
                $childBare = preg_replace('/[^a-z0-9]/', '', mb_strtolower($title));
                $isDuplicateOfParent = $parentBare === $childBare;

                if ($isDuplicateOfParent) {
                    // Promote children of this skipped entry to parent level
                    foreach ($data['items'] ?? [] as $item) {
                        $key = mb_strtolower(trim($item['title'] ?? ''));
                        if (! empty($key) && ! isset($seen[$key])) {
                            $seen[$key] = true;
                            $filtered[] = $item;
                        }
                    }

                    continue;
                }

                // Deduplicate by title (case-insensitive)
                $key = mb_strtolower($title);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $filtered[] = $data;
            }

            foreach ($filtered as $data) {
                $child = DocumentPartition::create([
                    'review_document_id' => $parent->review_document_id,
                    'parent_id' => $parent->id,
                    'name' => $data['title'],
                    'start_page' => $data['start_page'] ?? 1,
                    'end_page' => $data['end_page'] ?? ($data['start_page'] ?? 1),
                    'sort_order' => 0,
                    'level' => $childLevel,
                ]);

                if (! empty($data['items'])) {
                    $this->saveStructureChildren($child, $data['items'], $childLevel + 1);
                }
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

    private function validatePartitions(array $partitions): void
    {
        if (empty($partitions)) {
            throw ValidationException::withMessages(['partitions' => 'Minimal satu partisi diperlukan.']);
        }

        $flat = [];
        $this->flatten($partitions, $flat);

        foreach ($flat as $i => $p) {
            if (empty($p['name'])) {
                throw ValidationException::withMessages(["partitions.{$i}.name" => 'Nama partisi wajib diisi.']);
            }
            if (empty($p['start_page']) || $p['start_page'] < 1) {
                throw ValidationException::withMessages(["partitions.{$i}.start_page" => 'Halaman awal harus >= 1.']);
            }
            if (empty($p['end_page']) || $p['end_page'] < $p['start_page']) {
                throw ValidationException::withMessages(["partitions.{$i}.end_page" => 'Halaman akhir harus >= halaman awal.']);
            }
        }

        $sorted = $flat;
        usort($sorted, fn ($a, $b) => $a['start_page'] <=> $b['start_page']);

        for ($i = 1; $i < count($sorted); $i++) {
            if ($sorted[$i]['start_page'] <= $sorted[$i - 1]['end_page']) {
                throw ValidationException::withMessages([
                    'partitions' => "Range halaman \"{$sorted[$i]['name']}\" overlap dengan \"{$sorted[$i - 1]['name']}\".",
                ]);
            }
        }
    }

    private function flatten(array $items, array &$result): void
    {
        foreach ($items as $item) {
            $children = $item['children'] ?? [];
            unset($item['children']);
            $result[] = $item;
            if (! empty($children)) {
                $this->flatten($children, $result);
            }
        }
    }
}
