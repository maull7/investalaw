<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_documents', function (Blueprint $table) {
            $table->unsignedInteger('total_pages')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('review_documents', function (Blueprint $table) {
            $table->dropColumn('total_pages');
        });
    }
};
