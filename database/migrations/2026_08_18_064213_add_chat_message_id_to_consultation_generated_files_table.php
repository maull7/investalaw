<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultation_generated_files', function (Blueprint $table) {
            $table->foreignId('chat_message_id')->nullable()->after('user_id')->constrained('consultation_chat_messages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultation_generated_files', function (Blueprint $table) {
            $table->dropForeign(['chat_message_id']);
            $table->dropColumn('chat_message_id');
        });
    }
};
