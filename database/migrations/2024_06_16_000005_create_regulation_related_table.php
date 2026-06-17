<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained('regulations')->cascadeOnDelete();
            $table->foreignId('related_regulation_id')->constrained('regulations')->cascadeOnDelete();
            $table->unique(['regulation_id', 'related_regulation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_related');
    }
};
