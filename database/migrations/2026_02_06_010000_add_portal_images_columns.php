<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_settings', function (Blueprint $table) {
            $table->string('imagen_academias')->nullable()->after('logo_url');
            $table->string('imagen_inmobiliarias')->nullable()->after('imagen_academias');
        });
    }


    public function down(): void
    {
        Schema::table('portal_settings', function (Blueprint $table) {
            $table->dropColumn(['imagen_academias', 'imagen_inmobiliarias']);
        });
    }
};
