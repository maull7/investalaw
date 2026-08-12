<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('qris_image');
        });

        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropColumn('qris_image');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('qris_image')->nullable();
        });

        Schema::table('user_packages', function (Blueprint $table) {
            $table->string('qris_image')->nullable();
        });
    }
};
