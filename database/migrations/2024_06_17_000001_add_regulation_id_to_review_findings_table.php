<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_findings', function (Blueprint $table) {
            $table->foreignId('regulation_id')->nullable()->after('category_id')->constrained('regulations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('review_findings', function (Blueprint $table) {
            $table->dropForeign(['regulation_id']);
            $table->dropColumn('regulation_id');
        });
    }
};
