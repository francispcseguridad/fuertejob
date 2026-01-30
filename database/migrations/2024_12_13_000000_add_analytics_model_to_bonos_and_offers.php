<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bono_catalogs', function (Blueprint $table) {
            $table->foreignId('analytics_model_id')
                ->nullable()
                ->after('credit_cost')
                ->constrained('analytics_models')
                ->nullOnDelete();
        });

        Schema::table('job_offers', function (Blueprint $table) {
            $table->foreignId('analytics_model_id')
                ->nullable()
                ->after('job_sector_id')
                ->constrained('analytics_models')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bono_catalogs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('analytics_model_id');
        });

        Schema::table('job_offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('analytics_model_id');
        });
    }
};
