<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: add new columns
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->string('shift', 50)->nullable()->after('date');
            $table->string('mining_site', 100)->nullable()->after('shift');
        });

        // Step 2: rename stockpile columns to descriptive names
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->renameColumn('hoisted_stockpile', 'uncrushed_stockpile');
            $table->renameColumn('crushed_stockpile',  'unmilled_stockpile');
        });
    }

    public function down(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->renameColumn('uncrushed_stockpile', 'hoisted_stockpile');
            $table->renameColumn('unmilled_stockpile',  'crushed_stockpile');
        });

        Schema::table('daily_productions', function (Blueprint $table) {
            $table->dropColumn(['shift', 'mining_site']);
        });
    }
};
