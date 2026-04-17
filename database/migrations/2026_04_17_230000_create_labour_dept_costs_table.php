<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('labour_dept_costs')) {
            Schema::create('labour_dept_costs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('labour_energy_id')
                      ->constrained('labour_energy')
                      ->cascadeOnDelete();
                $table->foreignId('mining_department_id')
                      ->constrained('mining_departments')
                      ->cascadeOnDelete();
                $table->decimal('labour_cost', 12, 2)->default(0);
                $table->timestamps();
                $table->unique(['labour_energy_id', 'mining_department_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('labour_dept_costs');
    }
};
