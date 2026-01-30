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
        if (!Schema::hasTable('analytics_logs')) {
            return;
        }

        Schema::table('analytics_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_logs', 'route_name')) {
                $table->string('route_name', 150)->nullable()->after('url')->index();
            }

            if (!Schema::hasColumn('analytics_logs', 'route_params')) {
                $table->json('route_params')->nullable()->after('route_name');
            }

            if (!Schema::hasColumn('analytics_logs', 'related_type')) {
                $table->string('related_type', 150)->nullable()->after('route_params');
            }

            if (!Schema::hasColumn('analytics_logs', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('related_type')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('analytics_logs')) {
            return;
        }

        Schema::table('analytics_logs', function (Blueprint $table) {
            foreach (['related_id', 'related_type', 'route_params', 'route_name'] as $column) {
                if (Schema::hasColumn('analytics_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
