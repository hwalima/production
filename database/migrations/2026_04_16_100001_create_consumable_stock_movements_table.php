<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consumable_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['purchase', 'usage', 'adjustment', 'return']);
            $table->enum('direction', ['in', 'out']);
            $table->decimal('quantity', 12, 4);           // in use_units
            $table->decimal('packs', 12, 4)->nullable();  // packs received (purchase only)
            $table->decimal('unit_cost', 12, 4)->default(0);  // cost per use_unit at movement time
            $table->decimal('total_cost', 12, 2)->default(0); // quantity × unit_cost
            $table->date('movement_date');
            $table->string('reference')->nullable();       // delivery note, job no., etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_stock_movements');
    }
};
