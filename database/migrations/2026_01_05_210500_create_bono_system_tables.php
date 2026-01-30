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
        // Catálogo principal de bonos/paquetes
        Schema::create('bono_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('credits_included')->default(0); // Compatibilidad con lógica previa
            $table->integer('duration_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Packs reutilizables de asientos/usuarios
        Schema::create('user_seat_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('seat_count')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Packs reutilizables de vistas/descargas de CV
        Schema::create('cv_purchase_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('cv_count')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Compras de bonos por parte de empresas
        Schema::create('bono_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_profile_id')->constrained('company_profiles')->cascadeOnDelete();
            $table->foreignId('bono_catalog_id')->constrained('bono_catalogs')->cascadeOnDelete();
            $table->timestamp('purchase_date')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_status')->default('PENDIENTE');
            $table->timestamps();
        });

        // Relación bono -> packs de asientos con cantidad incluida
        Schema::create('bono_seat_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bono_catalog_id')->constrained('bono_catalogs')->cascadeOnDelete();
            $table->foreignId('user_seat_pack_id')->constrained('user_seat_packs')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // Relación bono -> packs de CV con cantidad incluida
        Schema::create('bono_cv_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bono_catalog_id')->constrained('bono_catalogs')->cascadeOnDelete();
            $table->foreignId('cv_purchase_pack_id')->constrained('cv_purchase_packs')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // Libro mayor de créditos de empresa (histórico de movimientos)
        Schema::create('company_credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company_profiles')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->timestamps();

            $table->index(['related_id', 'related_type'], 'ledger_related_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_credit_ledger');
        Schema::dropIfExists('bono_cv_packs');
        Schema::dropIfExists('bono_seat_packs');
        Schema::dropIfExists('bono_purchases');
        Schema::dropIfExists('cv_purchase_packs');
        Schema::dropIfExists('user_seat_packs');
        Schema::dropIfExists('bono_catalogs');
    }
};
