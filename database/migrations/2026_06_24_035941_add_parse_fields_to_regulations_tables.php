<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->timestamp('parsed_at')->nullable()->after('file_path');
            $table->string('parse_status', 20)->default('not_parsed')->after('parsed_at');
            $table->longText('parsed_text')->nullable()->after('parse_status');
            $table->json('parse_stats')->nullable()->after('parsed_text');
        });

        Schema::table('regulation_documents', function (Blueprint $table) {
            $table->timestamp('parsed_at')->nullable()->after('file_path');
            $table->string('parse_status', 20)->default('not_parsed')->after('parsed_at');
            $table->longText('parsed_text')->nullable()->after('parse_status');
            $table->json('parse_stats')->nullable()->after('parsed_text');
        });
    }

    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->dropColumn(['parsed_at', 'parse_status', 'parsed_text', 'parse_stats']);
        });

        Schema::table('regulation_documents', function (Blueprint $table) {
            $table->dropColumn(['parsed_at', 'parse_status', 'parsed_text', 'parse_stats']);
        });
    }
};
