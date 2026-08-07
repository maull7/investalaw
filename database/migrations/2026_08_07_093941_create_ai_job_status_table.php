<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_job_status', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->string('action');
            $table->string('status')->default('processing');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'model_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_job_status');
    }
};
