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
        if (Schema::hasTable('social_networks') && !Schema::hasColumn('social_networks', 'island_id')) {
            Schema::table('social_networks', function (Blueprint $table) {
                $table->unsignedBigInteger('island_id')->default(0)->after('order');
                $table->index('island_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('social_networks') && Schema::hasColumn('social_networks', 'island_id')) {
            Schema::table('social_networks', function (Blueprint $table) {
                $table->dropIndex(['island_id']);
                $table->dropColumn('island_id');
            });
        }
    }
};
