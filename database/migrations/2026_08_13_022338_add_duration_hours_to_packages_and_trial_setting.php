<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('duration_hours')->nullable()->after('price');
        });

        Schema::table('user_packages', function (Blueprint $table) {
            $table->timestamp('kak_vesta_started_at')->nullable();
        });

        DB::table('settings')->insert([
            'key' => 'trial_max_hours',
            'value' => '48',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'trial_max_hours')->delete();

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('duration_hours');
        });

        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropColumn('kak_vesta_started_at');
        });
    }
};
