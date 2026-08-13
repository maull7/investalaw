<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('case_number')->nullable();
            $table->string('court')->nullable();
            $table->string('status_case')->default('ongoing'); // ongoing | finished
            $table->string('file_path')->nullable();
            $table->longText('parsed_text')->nullable();
            $table->timestamp('parsed_at')->nullable();
            $table->longText('analysis')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
