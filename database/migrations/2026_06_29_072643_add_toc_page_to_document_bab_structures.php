<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_bab_structures', function (Blueprint $table) {
            $table->unsignedInteger('toc_page')->nullable()->after('end_page');
        });
    }

    public function down(): void
    {
        Schema::table('document_bab_structures', function (Blueprint $table) {
            $table->dropColumn('toc_page');
        });
    }
};
