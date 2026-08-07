<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->unsignedTinyInteger('parse_progress')->nullable()->after('parse_status');
        });

        Schema::table('regulation_documents', function (Blueprint $table) {
            $table->unsignedTinyInteger('parse_progress')->nullable()->after('parse_status');
        });
    }

    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->dropColumn('parse_progress');
        });

        Schema::table('regulation_documents', function (Blueprint $table) {
            $table->dropColumn('parse_progress');
        });
    }
};
