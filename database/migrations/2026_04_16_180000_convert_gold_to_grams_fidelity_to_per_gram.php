<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert gold_smelted from kg → g (multiply by 1000)
        DB::statement('UPDATE daily_productions SET gold_smelted = gold_smelted * 1000');

        // Convert fidelity_price from $/kg → $/g (divide by 1000)
        DB::statement('UPDATE daily_productions SET fidelity_price = fidelity_price / 1000');

        // Convert gold_monthly_target setting from kg → g
        DB::statement("
            UPDATE settings
            SET value = CAST(CAST(value AS DECIMAL(15,4)) * 1000 AS CHAR)
            WHERE `key` = 'gold_monthly_target'
        ");
    }

    public function down(): void
    {
        DB::statement('UPDATE daily_productions SET gold_smelted = gold_smelted / 1000');
        DB::statement('UPDATE daily_productions SET fidelity_price = fidelity_price * 1000');
        DB::statement("
            UPDATE settings
            SET value = CAST(CAST(value AS DECIMAL(15,4)) / 1000 AS CHAR)
            WHERE `key` = 'gold_monthly_target'
        ");
    }
};
