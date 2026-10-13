<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labour_energy', function (Blueprint $table) {
            $table->decimal('mining_cost', 12, 2)->default(0);
            $table->decimal('milling_cost', 12, 2)->default(0);
            $table->decimal('maintenance_cost', 12, 2)->default(0);
            $table->decimal('transport_cost', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('labour_energy', function (Blueprint $table) {
            $table->dropColumn([
                'mining_cost',
                'milling_cost',
                'maintenance_cost',
                'transport_cost',
            ]);
        });
    }
};
