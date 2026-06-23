<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->dropColumn('toc_page');
            $table->boolean('has_toc')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->dropColumn('has_toc');
            $table->unsignedInteger('toc_page')->nullable()->after('description');
        });
    }
};
