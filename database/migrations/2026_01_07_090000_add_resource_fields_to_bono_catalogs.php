<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bono_catalogs', function (Blueprint $table) {
            if (!Schema::hasColumn('bono_catalogs', 'offer_credits')) {
                $table->integer('offer_credits')->default(0);
            }
            if (!Schema::hasColumn('bono_catalogs', 'cv_views')) {
                $table->integer('cv_views')->default(0);
            }
            if (!Schema::hasColumn('bono_catalogs', 'user_seats')) {
                $table->integer('user_seats')->default(0);
            }
            if (!Schema::hasColumn('bono_catalogs', 'visibility_days')) {
                $table->integer('visibility_days')->default(0);
            }
        });

        DB::table('bono_catalogs')
            ->where('offer_credits', 0)
            ->update(['offer_credits' => DB::raw('credits_included')]);
    }

    public function down(): void
    {
        Schema::table('bono_catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('bono_catalogs', 'offer_credits')) {
                $table->dropColumn('offer_credits');
            }
            if (Schema::hasColumn('bono_catalogs', 'cv_views')) {
                $table->dropColumn('cv_views');
            }
            if (Schema::hasColumn('bono_catalogs', 'user_seats')) {
                $table->dropColumn('user_seats');
            }
            if (Schema::hasColumn('bono_catalogs', 'visibility_days')) {
                $table->dropColumn('visibility_days');
            }
        });
    }
};
