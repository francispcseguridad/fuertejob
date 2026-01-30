<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'rectifies_invoice_id')) {
                    $table->foreignId('rectifies_invoice_id')->nullable()
                        ->constrained('invoices')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('invoices', 'is_rectificativa')) {
                    $table->boolean('is_rectificativa')->default(false);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'rectifies_invoice_id')) {
                    $table->dropConstrainedForeignId('rectifies_invoice_id');
                }
                if (Schema::hasColumn('invoices', 'is_rectificativa')) {
                    $table->dropColumn('is_rectificativa');
                }
            });
        }
    }
};
