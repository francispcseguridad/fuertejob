<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            $indexes = DB::select("
                SELECT INDEX_NAME
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'invoices'
                  AND COLUMN_NAME = 'bono_purchase_id'
                  AND NON_UNIQUE = 0
            ");

            foreach ($indexes as $index) {
                $indexName = $index->INDEX_NAME ?? null;
                if ($indexName) {
                    Schema::table('invoices', function (Blueprint $table) use ($indexName) {
                        $table->dropUnique($indexName);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'bono_purchase_id')) {
                    $table->unique('bono_purchase_id', 'invoices_bono_purchase_id_unique');
                }
            });
        }
    }
};
