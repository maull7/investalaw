<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapter_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('analisa');
            $table->text('summary');
            $table->text('findings')->nullable();
            $table->string('compliance_score', 20)->nullable();
            $table->text('raw_response')->nullable();
            $table->string('provider_used', 20);
            $table->string('model_used', 100)->nullable();
            $table->timestamps();

            $table->index('document_chapter_id');
            $table->index('review_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_analyses');
    }
};
