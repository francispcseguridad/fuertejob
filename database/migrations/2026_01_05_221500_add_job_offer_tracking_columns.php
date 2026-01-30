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
        // Contadores en la tabla de ofertas
        if (Schema::hasTable('job_offers')) {
            Schema::table('job_offers', function (Blueprint $table) {
                if (!Schema::hasColumn('job_offers', 'views_count')) {
                    $table->unsignedBigInteger('views_count')->default(0)->after('status');
                }

                if (!Schema::hasColumn('job_offers', 'apply_clicks_count')) {
                    $table->unsignedBigInteger('apply_clicks_count')->default(0)->after('views_count');
                }

                if (!Schema::hasColumn('job_offers', 'applications_count')) {
                    $table->unsignedBigInteger('applications_count')->default(0)->after('apply_clicks_count');
                }
            });
        }

        // Tabla de logs de vistas por oferta (una fila por sesión/visita única)
        if (!Schema::hasTable('job_views_log')) {
            Schema::create('job_views_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_offer_id')->constrained('job_offers')->cascadeOnDelete();
                $table->string('session_id', 120)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('viewed_at')->useCurrent();

                $table->unique(['job_offer_id', 'session_id']);
                $table->index('viewed_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('job_views_log')) {
            Schema::drop('job_views_log');
        }

        if (Schema::hasTable('job_offers')) {
            Schema::table('job_offers', function (Blueprint $table) {
                foreach (['views_count', 'apply_clicks_count', 'applications_count'] as $column) {
                    if (Schema::hasColumn('job_offers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
