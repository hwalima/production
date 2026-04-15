<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');          // blasting, chemicals, mechanical, ppe, general
            $table->text('description')->nullable();
            $table->string('purchase_unit');      // box, drum, litre, kg, roll, bag, carton, each
            $table->string('use_unit');           // fuse, litre, kg, each, piece — smallest consumed unit
            $table->decimal('units_per_pack', 12, 4)->default(1); // use_units in one purchase_unit
            $table->decimal('pack_cost', 12, 2)->default(0);      // cost of one purchase_unit
            // unit_cost is always computed: pack_cost / units_per_pack
            $table->decimal('reorder_level', 12, 4)->default(0);  // alert when stock falls below this
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumables');
    }
};
