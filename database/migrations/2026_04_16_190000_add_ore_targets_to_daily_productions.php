<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->decimal('ore_hoisted_target', 12, 2)->nullable()->after('ore_hoisted');
            $table->decimal('ore_milled_target',  12, 2)->nullable()->after('ore_milled');
        });
    }

    public function down(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->dropColumn(['ore_hoisted_target', 'ore_milled_target']);
        });
    }
};
