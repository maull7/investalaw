<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_document_regulation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['review_document_id', 'regulation_id'], 'rev_doc_reg_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_document_regulation');
    }
};