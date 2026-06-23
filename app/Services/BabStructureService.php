<?php

namespace App\Services;

use App\Models\DocumentBabStructure;
use App\Models\DocumentPartition;
use App\Models\ReviewDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BabStructureService
{
    private const MATCH_THRESHOLD = 60;

    public function resolveChildPages(DocumentBabStructure $parent, array $children, ?int $maxPages = null): array
    {
        $parentStart = $parent->start_page;
        $parentEnd = $parent->end_page;

        if ($parent->reviewDocument->relationLoaded('pages') || $parent->reviewDocument->isParsed()) {
            $pages = $parent->reviewDocument->pages()
                ->whereBetween('page_number', [$parentStart, $parentEnd])
                ->orderBy('page_number')
                ->get()
                ->keyBy('page_number');
        } else {
            $pages = collect();
        }

        return $this->resolvePagesRecursive($children, $parentStart, $parentEnd, $pages, $maxPages);
    }

    private function resolvePagesRecursive(array $items, int $rangeStart, int $rangeEnd, Collection $pages, ?int $maxPages): array
    {
        $count = count($items);
        if ($count === 0) {
            return [];
        }

        $pagesPerItem = $maxPages ?? max(1, (int) ceil(($rangeEnd - $rangeStart + 1) / $count));

        $result = [];
        $currentPage = $rangeStart;

        foreach ($items as $i => $item) {
            $title = trim($item['title'] ?? '');

            $foundPage = $this->findTitleInPages($title, $pages, $rangeStart, $rangeEnd, $currentPage);

            if ($foundPage !== null) {
                $startPage = $foundPage;
            } else {
                $startPage = $currentPage;
            }

            $endPage = min($startPage + $pagesPerItem - 1, $rangeEnd);

            $children = [];
            if (! empty($item['items'])) {
                $subEnd = min($endPage, $rangeEnd);
                $children = $this->resolvePagesRecursive(
                    $item['items'],
                    $startPage,
                    $subEnd,
                    $pages,
                    $pagesPerItem > 1 ? max(1, (int) ceil($pagesPerItem / 2)) : 1
                );
                $endPage = max($endPage, ! empty($children) ? end($children)['end_page'] : $startPage);
            }

            $item['start_page'] = $startPage;
            $item['end_page'] = $endPage;
            $item['items'] = $children;

            $result[] = $item;
            $currentPage = $endPage + 1;
        }

        return $result;
    }

    private function findTitleInPages(string $title, Collection $pages, int $rangeStart, int $rangeEnd, int $fallbackPage): ?int
    {
        $search = mb_strtolower(trim(preg_replace('/\s+/', ' ', $title)));

        if (mb_strlen($search) < 3) {
            return null;
        }

        $bestScore = 0;
        $bestPage = null;

        foreach ($pages as $pageNum => $page) {
            if ($pageNum < $rangeStart || $pageNum > $rangeEnd) {
                continue;
            }

            $content = mb_strtolower(trim(preg_replace('/\s+/', ' ', $page->content ?? '')));

            if (str_contains($content, $search)) {
                $score = mb_strlen($search);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestPage = $pageNum;
                }
            }
        }

        if ($bestPage !== null && $bestPage >= $rangeStart && $bestPage <= $rangeEnd) {
            return $bestPage;
        }

        return null;
    }

    public function saveTocChildren(DocumentPartition $partition, array $babs): void
    {
        DB::transaction(function () use ($partition, $babs) {
            $documentId = $partition->review_document_id;

            DocumentBabStructure::where('review_document_id', $documentId)
                ->whereNull('parent_id')
                ->delete();

            $babOrder = 0;
            foreach ($babs as $babData) {
                $bab = DocumentBabStructure::create([
                    'review_document_id' => $documentId,
                    'parent_id' => null,
                    'name' => $babData['title'],
                    'start_page' => $babData['start_page'] ?? 1,
                    'end_page' => $babData['end_page'] ?? ($babData['start_page'] ?? 1) + 5,
                    'sort_order' => $babOrder++,
                    'level' => 0,
                ]);

                $subOrder = 0;
                foreach ($babData['children'] ?? [] as $subData) {
                    $subbab = DocumentBabStructure::create([
                        'review_document_id' => $documentId,
                        'parent_id' => $bab->id,
                        'name' => $subData['title'],
                        'start_page' => $subData['start_page'] ?? 1,
                        'end_page' => $subData['end_page'] ?? ($subData['start_page'] ?? 1) + 3,
                        'sort_order' => $subOrder++,
                        'level' => 1,
                    ]);

                    $isiOrder = 0;
                    foreach ($subData['children'] ?? [] as $isiData) {
                        DocumentBabStructure::create([
                            'review_document_id' => $documentId,
                            'parent_id' => $subbab->id,
                            'name' => $isiData['title'],
                            'start_page' => $isiData['start_page'] ?? 1,
                            'end_page' => $isiData['end_page'] ?? ($isiData['start_page'] ?? 1),
                            'sort_order' => $isiOrder++,
                            'level' => 2,
                        ]);
                    }
                }
            }
        });
    }

    public function saveStructureChildren(DocumentBabStructure $parent, array $children, int $childLevel): void
    {
        DB::transaction(function () use ($parent, $children, $childLevel) {
            $parent->children()->delete();

            $seen = [];
            $filtered = [];

            foreach ($children as $data) {
                $title = trim($data['title'] ?? '');

                if (empty($title)) {
                    continue;
                }

                if (preg_match('/^(daftar\s*isi|toc|table\s*of\s*contents)/i', $title)) {
                    continue;
                }

                $parentBare = preg_replace('/[^a-z0-9]/', '', mb_strtolower($parent->name));
                $childBare = preg_replace('/[^a-z0-9]/', '', mb_strtolower($title));
                $isDuplicateOfParent = $parentBare === $childBare;

                if ($isDuplicateOfParent) {
                    foreach ($data['items'] ?? [] as $item) {
                        $key = mb_strtolower(trim($item['title'] ?? ''));
                        if (! empty($key) && ! isset($seen[$key])) {
                            $seen[$key] = true;
                            $filtered[] = $item;
                        }
                    }

                    continue;
                }

                $key = mb_strtolower($title);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $filtered[] = $data;
            }

            foreach ($filtered as $data) {
                $child = DocumentBabStructure::create([
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

    /** @return array{array{'title': string, 'id': int, 'start_page': int, 'end_page': int, 'children': array}} */
    public function getTree(ReviewDocument $document): array
    {
        $roots = $document->babStructures()
            ->whereNull('parent_id')
            ->ordered()
            ->get();

        return $this->buildTree($roots);
    }

    private function buildTree($nodes): array
    {
        $result = [];

        foreach ($nodes as $node) {
            $item = [
                'id' => $node->id,
                'title' => $node->name,
                'start_page' => $node->start_page,
                'end_page' => $node->end_page,
                'children' => $this->buildTree($node->children),
            ];

            $result[] = $item;
        }

        return $result;
    }

    public function getBabs(ReviewDocument $document)
    {
        return $document->babStructures()
            ->whereNull('parent_id')
            ->ordered()
            ->get();
    }

    public function deleteForDocument(ReviewDocument $document): void
    {
        $document->babStructures()->delete();
    }
}
