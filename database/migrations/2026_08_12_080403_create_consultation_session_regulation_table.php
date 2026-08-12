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
            $table->foreignId('consultation_session_id')->constrained(table: 'consultation_sessions', indexName: 'csr_session_fk')->cascadeOnDelete();
            $table->foreignId('regulation_id')->constrained(indexName: 'csr_regulation_fk')->cascadeOnDelete();
            $table->unique(['consultation_session_id', 'regulation_id'], 'csr_session_reg_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_session_regulation');
    }
};
