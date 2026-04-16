<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('she_indicators', function (Blueprint $table) {
            $table->id();
            $table->date('period'); // stored as YYYY-MM-01
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

        Schema::create('she_requirement_items', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['she', 'mining', 'engineering', 'plant']);
            $table->string('name', 200);
            $table->string('unit_of_measure', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('she_requirement_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('she_requirement_item_id')
                  ->constrained('she_requirement_items')
                  ->onDelete('cascade');
            $table->date('period');
            $table->decimal('unit_value', 10, 2)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->unique(['she_requirement_item_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('she_requirement_entries');
        Schema::dropIfExists('she_requirement_items');
        Schema::dropIfExists('she_indicators');
    }
};
