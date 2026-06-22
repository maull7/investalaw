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
        Schema::create('document_parsed_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_document_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 30); // 'document' or 'regulation'
            $table->unsignedBigInteger('source_id')->nullable(); // regulation_id if source_type=regulation
            $table->unsignedInteger('page')->nullable();
            $table->longText('parsed_text');
            $table->unsignedInteger('char_count')->default(0);
            $table->timestamps();

            $table->index(['review_document_id', 'source_type', 'source_id'], 'dpt_doc_source_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_parsed_texts');
    }
};
