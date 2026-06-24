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
        Schema::create('regulation_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->text('context');
            $table->text('comparison_insights')->nullable();
            $table->text('change_analysis')->nullable();
            $table->text('revocation_analysis')->nullable();
            $table->json('related_data')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('regulation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulation_analyses');
    }
};
