<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('job_offers')) {
            Schema::table('job_offers', function (Blueprint $table) {
                if (!Schema::hasColumn('job_offers', 'expires_at')) {
                    $table->dateTime('expires_at')->nullable()->after('updated_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_offers')) {
            Schema::table('job_offers', function (Blueprint $table) {
                if (Schema::hasColumn('job_offers', 'expires_at')) {
                    $table->dropColumn('expires_at');
                }
            });
        }
    }
};
