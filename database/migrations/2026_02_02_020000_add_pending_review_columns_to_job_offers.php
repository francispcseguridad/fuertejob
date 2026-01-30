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
                if (!Schema::hasColumn('job_offers', 'pending_review')) {
                    $table->boolean('pending_review')->default(false)->after('is_published');
                }
                if (!Schema::hasColumn('job_offers', 'pending_review_at')) {
                    $table->timestamp('pending_review_at')->nullable()->after('pending_review');
                }
                if (!Schema::hasColumn('job_offers', 'pending_review_reason')) {
                    $table->text('pending_review_reason')->nullable()->after('pending_review_at');
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
                if (Schema::hasColumn('job_offers', 'pending_review_reason')) {
                    $table->dropColumn('pending_review_reason');
                }
                if (Schema::hasColumn('job_offers', 'pending_review_at')) {
                    $table->dropColumn('pending_review_at');
                }
                if (Schema::hasColumn('job_offers', 'pending_review')) {
                    $table->dropColumn('pending_review');
                }
            });
        }
    }
};
