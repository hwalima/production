<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('labour_energy', function (Blueprint $table) {
            $table->id();
            $table->decimal('zesa_cost', 12, 2)->default(633);
            $table->decimal('diesel_cost', 12, 2)->default(428);
            $table->decimal('labour_cost', 12, 2)->default(0);
            $table->date('date');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('labour_energy');
    }
};