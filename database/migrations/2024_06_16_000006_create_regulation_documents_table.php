<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulation_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regulation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('document_type');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_documents');
    }
};
