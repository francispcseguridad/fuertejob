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
        // Eliminamos la tabla si existía parcialmente por el error previo
        Schema::dropIfExists('company_resources_balance');

        Schema::create('company_resources_balance', function (Blueprint $table) {
            $table->id();

            /**
             * IMPORTANTE: El tipo de dato debe ser idéntico al 'id' en 'company_profiles'.
             * Si 'company_profiles' usa $table->id(), aquí debe ser unsignedBigInteger.
             */
            $table->unsignedBigInteger('company_profile_id');

            $table->integer('available_cv_views')->default(0);
            $table->integer('available_user_seats')->default(1);
            $table->timestamps();

            // Definición de la clave ajena apuntando a company_profiles
            $table->foreign('company_profile_id', 'fk_balance_company_profile')
                ->references('id')
                ->on('company_profiles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_resources_balance');
    }
};
