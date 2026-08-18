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
        Schema::create('consultation_generated_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('format');
            $table->string('filename');
            $table->string('path');
            $table->string('original_prompt')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('ai_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_generated_files');
    }
};
