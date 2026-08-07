<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->foreignId('type_prompt_id')->nullable()->after('id')
                ->constrained('type_prompts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('type_prompt_id');
        });
    }
};
