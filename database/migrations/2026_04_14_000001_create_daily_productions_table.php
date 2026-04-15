<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_productions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('ore_hoisted', 12, 2);
            $table->decimal('waste_hoisted', 12, 2);
            $table->decimal('hoisted_stockpile', 12, 2);
            $table->decimal('ore_crushed', 12, 2);
            $table->decimal('crushed_stockpile', 12, 2);
            $table->decimal('ore_milled', 12, 2);
            $table->decimal('gold_smelted', 12, 2);
            $table->decimal('purity_percentage', 5, 2);
            $table->decimal('fidelity_price', 12, 2);
            $table->decimal('profit_calculated', 14, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('daily_productions');
    }
};