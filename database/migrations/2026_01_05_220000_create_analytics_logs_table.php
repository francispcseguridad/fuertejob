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
            Schema::create('analytics_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('session_id', 120)->index();
                $table->string('url', 255);
                $table->string('method', 10);
                $table->string('ip_address', 45);
                $table->string('user_agent', 255)->nullable();
                $table->string('referer', 255)->nullable();
                $table->timestamps();

                $table->index('created_at');
            });

            return;
        }

        Schema::table('analytics_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('analytics_logs', 'session_id')) {
                $table->string('session_id', 120)->nullable()->index()->after('user_id');
            }

            if (!Schema::hasColumn('analytics_logs', 'url')) {
                $table->string('url', 255)->nullable()->after('session_id');
            }

            if (!Schema::hasColumn('analytics_logs', 'method')) {
                $table->string('method', 10)->nullable()->after('url');
            }

            if (!Schema::hasColumn('analytics_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('method');
            }

            if (!Schema::hasColumn('analytics_logs', 'user_agent')) {
                $table->string('user_agent', 255)->nullable()->after('ip_address');
            }

            if (!Schema::hasColumn('analytics_logs', 'referer')) {
                $table->string('referer', 255)->nullable()->after('user_agent');
            }

            $hasCreatedAt = Schema::hasColumn('analytics_logs', 'created_at');
            $hasUpdatedAt = Schema::hasColumn('analytics_logs', 'updated_at');

            if (!$hasCreatedAt && !$hasUpdatedAt) {
                $table->timestamps();
            } else {
                if (!$hasCreatedAt) {
                    $table->timestamp('created_at')->nullable()->after('referer');
                }

                if (!$hasUpdatedAt) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            }

            if (!$hasCreatedAt) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_logs');
    }
};
