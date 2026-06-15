<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('regulation_categories')->cascadeOnDelete();
            $table->string('filename');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_files');
    }
};
