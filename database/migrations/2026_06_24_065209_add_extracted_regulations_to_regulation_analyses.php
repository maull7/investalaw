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
        Schema::table('regulation_analyses', function (Blueprint $table) {
            $table->json('extracted_regulations')->nullable()->after('related_data');
        });
    }

    public function down(): void
    {
        Schema::table('regulation_analyses', function (Blueprint $table) {
            $table->dropColumn('extracted_regulations');
        });
    }
};
