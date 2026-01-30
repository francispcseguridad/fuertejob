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
        Schema::create('offers_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('total_offers');
            $table->integer('duration_days')->nullable();
            $table->timestamps();
        });

        Schema::create('bono_offers_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bono_catalog_id')->constrained('bono_catalogs')->cascadeOnDelete();
            $table->foreignId('offers_pack_id')->constrained('offers_packs')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bono_offers_packs');
        Schema::dropIfExists('offers_packs');
    }
};
