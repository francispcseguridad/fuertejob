<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('canary_locations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('province')->nullable();
            $table->string('island')->nullable();
            $table->string('country')->default('España');
            $table->timestamps();

            $table->unique(['city', 'province', 'island'], 'canary_locations_unique_city_province_island');
            $table->index('province');
            $table->index('island');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canary_locations');
    }
};
