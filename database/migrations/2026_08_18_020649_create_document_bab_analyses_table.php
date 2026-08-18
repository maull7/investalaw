<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_bab_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('bab_index');
            $table->string('label');
            $table->json('result');
            $table->timestamps();

            $table->unique(['review_document_id', 'bab_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_bab_analyses');
    }
};
