<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('chapter_analyses');
        Schema::dropIfExists('document_chapters');
    }

    public function down(): void
    {
        // Tables are recreated by their original migrations
    }
};
