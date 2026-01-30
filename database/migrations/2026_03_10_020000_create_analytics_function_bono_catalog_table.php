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
        Schema::create('analytics_function_bono_catalog', function (Blueprint $table) {
            $table->foreignId('analytics_function_id')
                ->constrained('analytics_functions')
                ->cascadeOnDelete();
            $table->foreignId('bono_catalog_id')
                ->constrained('bono_catalogs')
                ->cascadeOnDelete();
            $table->primary(['analytics_function_id', 'bono_catalog_id'], 'analytics_function_bono_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_function_bono_catalog');
    }
};
