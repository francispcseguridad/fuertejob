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
        if (Schema::hasTable('job_offers')) {
            Schema::table('job_offers', function (Blueprint $table) {
                if (!Schema::hasColumn('job_offers', 'is_published')) {
                    $table->boolean('is_published')->default(false)->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('job_offers')) {
            Schema::table('job_offers', function (Blueprint $table) {
                if (Schema::hasColumn('job_offers', 'is_published')) {
                    $table->dropColumn('is_published');
                }
            });
        }
    }
};
