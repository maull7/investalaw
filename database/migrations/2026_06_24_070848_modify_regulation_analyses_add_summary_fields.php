<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulation_analyses', function (Blueprint $table) {
            $table->text('changes_summary')->nullable()->after('revocation_analysis');
            $table->json('key_points')->nullable()->after('changes_summary');
            $table->dropColumn('extracted_regulations');
        });
    }

    public function down(): void
    {
        Schema::table('regulation_analyses', function (Blueprint $table) {
            $table->json('extracted_regulations')->nullable()->after('related_data');
            $table->dropColumn(['changes_summary', 'key_points']);
        });
    }
};
