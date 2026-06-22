<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partition_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_partition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->text('findings')->nullable();
            $table->string('compliance_status', 30)->nullable();
            $table->timestamps();

            $table->index('document_partition_id');
            $table->index('review_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partition_analyses');
    }
};
