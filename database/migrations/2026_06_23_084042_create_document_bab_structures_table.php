<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_bab_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('document_bab_structures')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('start_page');
            $table->unsignedInteger('end_page');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('level')->default(0);
            $table->timestamps();

            $table->index('review_document_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_bab_structures');
    }
};
