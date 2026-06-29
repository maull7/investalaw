<?php

use App\Models\DocumentPartition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $partitions = DocumentPartition::where('has_toc', 1)
            ->whereNull('parent_id')
            ->with('reviewDocument')
            ->get();

        foreach ($partitions as $partition) {
            $babs = $partition->reviewDocument->babStructures()
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get(['id', 'name', 'start_page']);

            if ($babs->isEmpty()) {
                continue;
            }

            $firstBab = $babs->first();
            $firstBabStart = $firstBab->start_page ?? 1;
            $title = $firstBab->name;
            $search = null;

            if (preg_match('/^(BAB\s+\w+)/iu', $title, $m)) {
                $search = $m[1];
            }

            $offset = null;

            if ($search) {
                $found = $partition->reviewDocument->pages()
                    ->where('page_number', '>', $partition->end_page)
                    ->whereRaw('LOCATE(?, content) > 0', [$search])
                    ->orderBy('page_number')
                    ->first();

                if ($found) {
                    $offset = $found->page_number - $firstBabStart;
                }
            }

            if ($offset === null) {
                $endPage = $partition->end_page ?? 0;
                $offset = ($endPage + 1) - $firstBabStart;
            }

            $partition->reviewDocument->babStructures()
                ->whereNull('parent_id')
                ->update([
                    'pdf_page' => DB::raw("start_page + {$offset}"),
                    'pdf_end_page' => DB::raw("end_page + {$offset}"),
                ]);

            if (app()->runningInConsole()) {
                $method = $search && isset($found) ? 'content' : 'formula';
                app('log')->info("Document {$partition->review_document_id}: offset={$offset} (method: {$method})");
            }
        }
    }

    public function down(): void
    {
        // re-run original backfill (formula-based)
        DB::statement('
            UPDATE document_bab_structures bs
            INNER JOIN document_partitions dp
                ON dp.review_document_id = bs.review_document_id
                AND dp.has_toc = 1
                AND dp.parent_id IS NULL
            SET
                bs.pdf_page = bs.start_page + dp.end_page,
                bs.pdf_end_page = bs.end_page + dp.end_page
            WHERE bs.pdf_page IS NOT NULL
        ');
    }
};
