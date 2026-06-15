<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_findings', function (Blueprint $table) {
            $table->dropUnique(['review_id', 'regulation_id']);
            $table->renameColumn('regulation_id', 'category_id');
            $table->foreign('review_id')->references('id')->on('reviews')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('regulation_categories')->cascadeOnDelete();
            $table->unique(['review_id', 'category_id']);
        });

        Schema::dropIfExists('review_document_regulation');
        Schema::dropIfExists('regulations');
    }

    public function down(): void
    {
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('regulation_categories')->cascadeOnDelete();
            $table->string('regulation_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('status')->default('active');
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('review_document_regulation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['review_document_id', 'regulation_id'], 'rev_doc_reg_unique');
        });

        Schema::table('review_findings', function (Blueprint $table) {
            $table->dropForeign(['review_id']);
            $table->dropForeign(['category_id']);
            $table->dropUnique(['review_id', 'category_id']);
            $table->renameColumn('category_id', 'regulation_id');
            $table->foreign('review_id')->references('id')->on('reviews')->cascadeOnDelete();
            $table->foreign('regulation_id')->references('id')->on('regulations')->cascadeOnDelete();
            $table->unique(['review_id', 'regulation_id']);
        });
    }
};
