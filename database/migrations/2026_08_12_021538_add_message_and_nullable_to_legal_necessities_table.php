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
        Schema::table('legal_necessities', function (Blueprint $table) {
            $table->text('message')->nullable()->after('target_output');
            $table->string('legal_activities')->nullable()->change();
            $table->string('status_company')->nullable()->change();
            $table->string('value_trx')->nullable()->change();
            $table->string('target_output')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_necessities', function (Blueprint $table) {
            $table->dropColumn('message');
            $table->string('legal_activities')->nullable(false)->change();
            $table->string('status_company')->nullable(false)->change();
            $table->string('value_trx')->nullable(false)->change();
            $table->string('target_output')->nullable(false)->change();
        });
    }
};
