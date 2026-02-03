<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('experiences')) {
            return;
        }

        DB::statement('ALTER TABLE `experiences` ADD COLUMN `start_year` YEAR NULL AFTER `start_date`, ADD COLUMN `end_year` YEAR NULL AFTER `start_year`;');
        DB::statement('UPDATE `experiences` SET `start_year` = YEAR(`start_date`), `end_year` = YEAR(`end_date`);');
        DB::statement('ALTER TABLE `experiences` DROP COLUMN `start_date`, DROP COLUMN `end_date`;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('experiences')) {
            return;
        }

        DB::statement('ALTER TABLE `experiences` ADD COLUMN `start_date` DATE NULL, ADD COLUMN `end_date` DATE NULL;');
        DB::statement('UPDATE `experiences` SET `start_date` = CASE WHEN `start_year` IS NULL THEN NULL ELSE MAKEDATE(`start_year`, 1) END, `end_date` = CASE WHEN `end_year` IS NULL THEN NULL ELSE MAKEDATE(`end_year`, 1) END;');
        DB::statement('ALTER TABLE `experiences` DROP COLUMN `start_year`, DROP COLUMN `end_year`;');
    }
};
