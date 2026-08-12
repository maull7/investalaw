<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->renameColumn('qris_code', 'qris_image');
        });
    }

    public function down(): void
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->renameColumn('qris_image', 'qris_code');
        });
    }
};
