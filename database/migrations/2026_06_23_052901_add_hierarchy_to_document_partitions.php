<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('review_document_id')
                ->constrained('document_partitions')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('level')
                ->default(0)
                ->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'level']);
        });
    }
};
