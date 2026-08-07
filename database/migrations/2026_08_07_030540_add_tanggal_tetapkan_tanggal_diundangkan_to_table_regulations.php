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
        Schema::table('regulations', function (Blueprint $table) {
            $table->date('tanggal_tetapkan')->nullable()->after('year');
            $table->date('tanggal_diundangkan')->nullable()->after('tanggal_tetapkan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            //
        });
    }
};
