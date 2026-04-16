<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop and recreate she_indicators with FK to mining_departments
        Schema::dropIfExists('she_indicators');
        Schema::create('she_indicators', function (Blueprint $table) {
            $table->id();
            $table->date('period');
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
            $table->unique(['period', 'mining_department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('she_indicators');
        Schema::create('she_indicators', function (Blueprint $table) {
            $table->id();
            $table->date('period');
            $table->enum('department', ['mining', 'plant_processing', 'engineering', 'admin']);
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
            $table->unique(['period', 'department']);
        });
    }
};
