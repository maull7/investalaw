<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_analysis_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_analysis_id')->constrained('regulation_analyses')->cascadeOnDelete();
            $table->string('name');
            $table->string('number')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('relationship')->default('disebut');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_analysis_references');
    }
};
