<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('she_indicators');
        Schema::create('she_indicators', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('mining_department_id')
                  ->constrained('mining_departments')
                  ->onDelete('cascade');
            $table->decimal('medical_injury_case', 8, 2)->default(0);
            $table->decimal('fatal_incident', 8, 2)->default(0);
            $table->decimal('lti', 8, 2)->default(0);
            $table->decimal('nlti', 8, 2)->default(0);
            $table->decimal('leave', 8, 2)->default(0);
            $table->decimal('offdays', 8, 2)->default(0);
            $table->decimal('sick', 8, 2)->default(0);
            $table->decimal('iod', 8, 2)->default(0);
            $table->decimal('awol', 8, 2)->default(0);
            $table->decimal('terminations', 8, 2)->default(0);
            $table->timestamps();
            $table->unique(['date', 'mining_department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('she_indicators');
    }
};
