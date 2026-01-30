<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('company_resources_balance', function (Blueprint $table) {
            if (!Schema::hasColumn('company_resources_balance', 'total_offer_credits')) {
                $table->integer('total_offer_credits')->default(0);
            }
            if (!Schema::hasColumn('company_resources_balance', 'used_offer_credits')) {
                $table->integer('used_offer_credits')->default(0);
            }
            if (!Schema::hasColumn('company_resources_balance', 'available_offer_credits')) {
                $table->integer('available_offer_credits')->default(0);
            }
            if (!Schema::hasColumn('company_resources_balance', 'total_cv_views')) {
                $table->integer('total_cv_views')->default(0);
            }
            if (!Schema::hasColumn('company_resources_balance', 'used_cv_views')) {
                $table->integer('used_cv_views')->default(0);
            }
            if (!Schema::hasColumn('company_resources_balance', 'total_user_seats')) {
                $table->integer('total_user_seats')->default(0);
            }
            if (!Schema::hasColumn('company_resources_balance', 'used_user_seats')) {
                $table->integer('used_user_seats')->default(0);
            }
            if (!Schema::hasColumn('company_resources_balance', 'offer_visibility_days')) {
                $table->integer('offer_visibility_days')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_resources_balance', function (Blueprint $table) {
            if (Schema::hasColumn('company_resources_balance', 'total_offer_credits')) {
                $table->dropColumn('total_offer_credits');
            }
            if (Schema::hasColumn('company_resources_balance', 'used_offer_credits')) {
                $table->dropColumn('used_offer_credits');
            }
            if (Schema::hasColumn('company_resources_balance', 'available_offer_credits')) {
                $table->dropColumn('available_offer_credits');
            }
            if (Schema::hasColumn('company_resources_balance', 'total_cv_views')) {
                $table->dropColumn('total_cv_views');
            }
            if (Schema::hasColumn('company_resources_balance', 'used_cv_views')) {
                $table->dropColumn('used_cv_views');
            }
            if (Schema::hasColumn('company_resources_balance', 'total_user_seats')) {
                $table->dropColumn('total_user_seats');
            }
            if (Schema::hasColumn('company_resources_balance', 'used_user_seats')) {
                $table->dropColumn('used_user_seats');
            }
            if (Schema::hasColumn('company_resources_balance', 'offer_visibility_days')) {
                $table->dropColumn('offer_visibility_days');
            }
        });
    }
};
