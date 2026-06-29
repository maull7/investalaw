<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE document_bab_structures bs
            INNER JOIN (
                SELECT review_document_id, MIN(start_page) as first_bab_start
                FROM document_bab_structures
                WHERE parent_id IS NULL
                GROUP BY review_document_id
            ) fb ON fb.review_document_id = bs.review_document_id
            INNER JOIN document_partitions dp
                ON dp.review_document_id = bs.review_document_id
                AND dp.has_toc = 1
                AND dp.parent_id IS NULL
            SET
                bs.pdf_page = bs.start_page + ((dp.end_page + 1) - fb.first_bab_start),
                bs.pdf_end_page = bs.end_page + ((dp.end_page + 1) - fb.first_bab_start)
            WHERE bs.pdf_page IS NOT NULL
        ');
    }

    public function down(): void
    {
        // re-run the original backfill
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
