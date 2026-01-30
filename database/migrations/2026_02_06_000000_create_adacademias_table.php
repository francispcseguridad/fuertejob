<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->unsignedBigInteger('island_id');
            $table->timestamps();

            $table->foreign('island_id')->references('id')->on('islands')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academias');
    }
};
