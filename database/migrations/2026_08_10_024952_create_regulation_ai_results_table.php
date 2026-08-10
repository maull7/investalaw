<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_ai_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_prompt_id')->nullable()->constrained('type_prompts')->nullOnDelete();
            $table->string('type');
            $table->string('prompt_title');
            $table->longText('prompt_text');
            $table->longText('result');
            $table->string('provider_used')->nullable();
            $table->string('model_used')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_ai_results');
    }
};
