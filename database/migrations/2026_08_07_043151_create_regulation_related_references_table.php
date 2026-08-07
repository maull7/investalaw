<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_related_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('number')->nullable();
            $table->string('year')->nullable();
            $table->string('relationship')->default('dirujuk');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_related_references');
    }
};
