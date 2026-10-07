<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mining_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('shift', 50)->nullable();
            $table->string('mining_site', 100)->nullable();
            $table->decimal('ore_hoisted_entered', 12, 2);
            $table->decimal('ore_hoisted', 12, 2);
            $table->decimal('ore_hoisted_target', 12, 2)->nullable();
            $table->decimal('waste_hoisted', 12, 2);
            $table->decimal('skip_factor', 8, 4)->default(1);
            $table->timestamps();

            $table->index(['date', 'shift', 'mining_site']);
        });

        Schema::table('daily_productions', function (Blueprint $table) {
            $table->decimal('ro_mine_milled', 12, 2)->default(0)->after('ore_milled');
            $table->decimal('sanda_milled', 12, 2)->default(0)->after('ro_mine_milled');
            $table->boolean('sanda_milled_manual')->default(false)->after('sanda_milled');
        });

        DB::table('daily_productions')
            ->orderBy('id')
            ->chunkById(500, function ($productions) {
                $now = now();
                $records = $productions->map(fn ($production) => [
                    'date' => $production->date,
                    'shift' => $production->shift,
                    'mining_site' => $production->mining_site,
                    'ore_hoisted_entered' => $production->ore_hoisted,
                    'ore_hoisted' => $production->ore_hoisted,
                    'ore_hoisted_target' => $production->ore_hoisted_target,
                    'waste_hoisted' => $production->waste_hoisted,
                    'skip_factor' => 1,
                    'created_at' => $production->created_at ?? $now,
                    'updated_at' => $production->updated_at ?? $now,
                ])->all();

                DB::table('mining_records')->insert($records);
            });

        DB::table('daily_productions')->update(['sanda_milled' => DB::raw('ore_milled')]);
    }

    public function down(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->dropColumn(['ro_mine_milled', 'sanda_milled', 'sanda_milled_manual']);
        });

        Schema::dropIfExists('mining_records');
    }
};
