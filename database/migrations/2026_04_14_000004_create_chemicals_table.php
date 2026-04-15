<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chemicals', function (Blueprint $table) {
            $table->id();
            $table->decimal('sodium_cyanide', 10, 2);
            $table->decimal('lime', 10, 2);
            $table->decimal('caustic_soda', 10, 2);
            $table->decimal('iodised_salt', 10, 2);
            $table->decimal('mercury', 10, 2);
            $table->decimal('steel_balls', 10, 2);
            $table->decimal('hydrogen_peroxide', 10, 2);
            $table->decimal('borax', 10, 2);
            $table->decimal('nitric_acid', 10, 2);
            $table->decimal('sulphuric_acid', 10, 2);
            $table->date('date');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('chemicals');
    }
};