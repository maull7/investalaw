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
            $table->foreignId('category_id')->constrained('regulation_categories')->cascadeOnDelete();
            $table->string('regulation_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('status')->default('active');
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};
