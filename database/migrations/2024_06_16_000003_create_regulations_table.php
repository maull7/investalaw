<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->string('regulation_number');
            $table->string('title');
            $table->foreignId('regulation_type_id')->constrained('regulation_types')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('regulation_categories')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('file_path');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};
