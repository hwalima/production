<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drilling_records', function (Blueprint $table) {
            $table->id();
            $table->string('end_name');
            $table->integer('hole_count');
            $table->decimal('drill_steel_length', 10, 2);
            $table->decimal('advance', 10, 2);
            $table->date('date');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('drilling_records');
    }
};