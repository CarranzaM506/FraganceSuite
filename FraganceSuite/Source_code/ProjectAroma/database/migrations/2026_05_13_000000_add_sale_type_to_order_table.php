<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `order` ADD COLUMN `sale_type` VARCHAR(10) NOT NULL DEFAULT 'web'");
        DB::statement("ALTER TABLE `order` MODIFY COLUMN `idlocation` INT NULL");
        DB::statement("ALTER TABLE `order` MODIFY COLUMN `guidenumber` VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `order` DROP COLUMN `sale_type`");
    }
};
