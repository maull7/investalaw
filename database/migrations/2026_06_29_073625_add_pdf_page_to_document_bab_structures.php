<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_bab_structures', function (Blueprint $table) {
            $table->unsignedInteger('pdf_page')->nullable()->after('toc_page');
            $table->unsignedInteger('pdf_end_page')->nullable()->after('pdf_page');
        });
    }

    public function down(): void
    {
        Schema::table('document_bab_structures', function (Blueprint $table) {
            $table->dropColumn(['pdf_page', 'pdf_end_page']);
        });
    }
};
