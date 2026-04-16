<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyProductionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Starting stockpile balances (realistic carry-forward)
        $hoisted_stockpile = 120.00;
        $crushed_stockpile = 45.00;

        for ($i = 30; $i >= 1; $i--) {
            $date = $now->copy()->subDays($i);

            // Ore & waste hoisted — small underground mine, ~70-100 t/day ore
            $ore_hoisted   = rand(65, 100);      // tons
            $waste_hoisted = rand(15, 35);        // tons

            // Crushing & milling slightly less than hoisted (stockpile buffer)
            $ore_crushed = rand(60, 95);          // tons
            $ore_milled  = rand(50, 80);          // tons

            // Grade: 1.0 – 3.0 g/t (average ~2 g/t)
            $grade    = rand(100, 300) / 100;     // g/t (precision 0.01)

            // Metallurgical recovery: 87 – 93 %
            $recovery = rand(87, 93) / 100;

            // Gold produced in grams
            $gold_smelted = round($ore_milled * $grade * $recovery, 2);  // g (target ~90-170 g/day)

            // Purity: tightly around 95 %
            $purity_percentage = rand(92, 98);    // %

            // Fidelity gold price — spot ~$104.50/g (Apr 2026), Fidelity pays 95-98 % of spot
            $fidelity_price = round(rand(9900, 10400) / 100, 2); // USD/g

            // Ore hoisted & milled targets (±10% of actuals for realistic variance)
            $ore_hoisted_target = round($ore_hoisted * (rand(90, 110) / 100), 2);
            $ore_milled_target  = round($ore_milled  * (rand(90, 110) / 100), 2);

            // Running stockpile balances
            $hoisted_stockpile = round(max(0, $hoisted_stockpile + $ore_hoisted - $ore_crushed), 2);
            $crushed_stockpile = round(max(0, $crushed_stockpile + $ore_crushed - $ore_milled), 2);

            DB::table('daily_productions')->insert([
                'date'               => $date->toDateString(),
                'ore_hoisted'        => $ore_hoisted,
                'ore_hoisted_target' => $ore_hoisted_target,
                'waste_hoisted'      => $waste_hoisted,
                'hoisted_stockpile'  => $hoisted_stockpile,
                'ore_crushed'        => $ore_crushed,
                'crushed_stockpile'  => $crushed_stockpile,
                'ore_milled'         => $ore_milled,
                'ore_milled_target'  => $ore_milled_target,
                'gold_smelted'       => $gold_smelted,
                'purity_percentage'  => $purity_percentage,
                'fidelity_price'     => $fidelity_price,
                'created_at'         => $date,
                'updated_at'         => $date,
            ]);
        }
    }
}
