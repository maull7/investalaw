<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->text('parse_error')->nullable()->after('parse_progress');
        });

        Schema::table('regulation_documents', function (Blueprint $table) {
            $table->text('parse_error')->nullable()->after('parse_progress');
        });
    }

    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->dropColumn('parse_error');
        });

        Schema::table('regulation_documents', function (Blueprint $table) {
            $table->dropColumn('parse_error');
        });
    }
};
