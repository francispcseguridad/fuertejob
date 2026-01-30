<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('bono_catalogs', function (Blueprint $table) {
            if (!Schema::hasColumn('bono_catalogs', 'credit_cost')) {
                $table->integer('credit_cost')->default(0)->after('price');
            }
            if (!Schema::hasColumn('bono_catalogs', 'is_extra')) {
                $table->boolean('is_extra')->default(false)->after('credit_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bono_catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('bono_catalogs', 'credit_cost')) {
                $table->dropColumn('credit_cost');
            }
            if (Schema::hasColumn('bono_catalogs', 'is_extra')) {
                $table->dropColumn('is_extra');
            }
        });
    }
};
