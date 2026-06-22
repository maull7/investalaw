<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partition_analyses', function (Blueprint $table) {
            $table->string('type', 50)->default('analisa')->after('document_partition_id');
            $table->string('compliance_score', 20)->nullable()->after('findings');
            $table->text('raw_response')->nullable()->after('compliance_status');
            $table->string('provider_used', 20)->nullable()->after('raw_response');
            $table->string('model_used', 100)->nullable()->after('provider_used');
        });
    }

    public function down(): void
    {
        Schema::table('partition_analyses', function (Blueprint $table) {
            $table->dropColumn(['type', 'compliance_score', 'raw_response', 'provider_used', 'model_used']);
        });
    }
};
