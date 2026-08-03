<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE regulations ADD FULLTEXT INDEX regulations_parsed_text_fulltext(parsed_text) WITH PARSER ngram');
        DB::statement('ALTER TABLE regulation_documents ADD FULLTEXT INDEX regulation_documents_parsed_text_fulltext(parsed_text) WITH PARSER ngram');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE regulations DROP INDEX regulations_parsed_text_fulltext');
        DB::statement('ALTER TABLE regulation_documents DROP INDEX regulation_documents_parsed_text_fulltext');
    }
};
