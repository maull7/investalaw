<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'regulations_public_listing_idx';

    public function up(): void
    {
        if (Schema::hasTable('regulations') && ! Schema::hasIndex('regulations', self::INDEX)) {
            Schema::table('regulations', function (Blueprint $table): void {
                $table->index(['deleted_at', 'tanggal_tetapkan', 'category_id'], self::INDEX);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('regulations') && Schema::hasIndex('regulations', self::INDEX)) {
            Schema::table('regulations', function (Blueprint $table): void {
                $table->dropIndex(self::INDEX);
            });
        }
    }
};
