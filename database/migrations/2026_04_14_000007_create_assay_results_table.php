<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assay_results', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['fire_assay', 'gold_on_carbon', 'bottle_roll']);
            $table->date('date');
            $table->string('description')->nullable();
            $table->decimal('assay_value', 10, 4);
            $table->unsignedBigInteger('daily_production_id')->nullable();
            $table->timestamps();

            $table->foreign('daily_production_id')->references('id')->on('daily_productions')->onDelete('set null');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('assay_results');
    }
};