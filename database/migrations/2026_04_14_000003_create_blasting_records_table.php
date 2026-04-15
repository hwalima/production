<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blasting_records', function (Blueprint $table) {
            $table->id();
            $table->integer('fractures');
            $table->integer('fuse');
            $table->integer('carmes_ieds');
            $table->integer('power_cords');
            $table->decimal('anfo', 10, 2);
            $table->decimal('oil', 10, 2);
            $table->integer('drill_bits');
            $table->date('date');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('blasting_records');
    }
};