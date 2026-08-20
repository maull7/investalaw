<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_chat_messages', function (Blueprint $table): void {
            $table->json('citations')->nullable()->after('attachments');
            $table->string('confidence')->nullable()->after('citations');
            $table->longText('prompt_text')->nullable()->after('confidence');
            $table->string('provider_used')->nullable()->after('prompt_text');
            $table->string('model_used')->nullable()->after('provider_used');
            $table->string('context_hash', 64)->nullable()->after('model_used');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_chat_messages', function (Blueprint $table): void {
            $table->dropColumn(['citations', 'confidence', 'prompt_text', 'provider_used', 'model_used', 'context_hash']);
        });
    }
};
