<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consultation_session_regulation')) {
            return;
        }

        Schema::create('consultation_session_regulation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->unique(['consultation_session_id', 'regulation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_session_regulation');
    }
};
