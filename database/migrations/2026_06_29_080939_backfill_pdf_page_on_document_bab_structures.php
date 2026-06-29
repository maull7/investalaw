<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE document_bab_structures bs
            INNER JOIN document_partitions dp
                ON dp.review_document_id = bs.review_document_id
                AND dp.has_toc = 1
                AND dp.parent_id IS NULL
            SET
                bs.pdf_page = bs.start_page + dp.end_page,
                bs.pdf_end_page = bs.end_page + dp.end_page
            WHERE bs.pdf_page IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('UPDATE document_bab_structures SET pdf_end_page = NULL, pdf_page = NULL');
    }
};
