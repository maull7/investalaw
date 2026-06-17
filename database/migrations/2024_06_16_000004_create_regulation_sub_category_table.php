<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_sub_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')->constrained()->cascadeOnDelete();
            $table->unique(['regulation_id', 'sub_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_sub_category');
    }
};
