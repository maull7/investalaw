<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->integer('page_number');
            $table->longText('content');
            $table->integer('char_count')->default(0);
            $table->timestamps();

            $table->unique(['review_document_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_pages');
    }
};
