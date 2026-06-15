<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_document_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('regulation_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['review_document_id', 'category_id'], 'rev_doc_cat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_document_category');
    }
};
