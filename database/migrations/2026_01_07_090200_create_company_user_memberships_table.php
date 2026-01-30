<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_user_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_profile_id')->constrained('company_profiles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bono_purchase_id')->nullable()->constrained('bono_purchases')->nullOnDelete();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['company_profile_id', 'expires_at']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('company_user_memberships');
    }
};
