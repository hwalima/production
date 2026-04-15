<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssayResultSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['fire_assay', 'gold_on_carbon', 'bottle_roll'];

        $productions = DB::table('daily_productions')->orderBy('date')->get();

        foreach ($productions as $prod) {
            $count = rand(1, 2);
            for ($j = 0; $j < $count; $j++) {
                $type = $types[array_rand($types)];
                DB::table('assay_results')->insert([
                    'type'                => $type,
                    'date'                => $prod->date,
                    'description'         => match($type) {
                        'fire_assay'     => 'Fire assay - ' . substr($prod->date, 0, 7),
                        'gold_on_carbon' => 'GOC batch on ' . $prod->date,
                        'bottle_roll'    => 'Bottle roll test #' . rand(100, 999),
                    },
                    'assay_value'         => round(rand(80, 300) / 100, 2),  // g/t: 0.80 – 3.00
                    'daily_production_id' => $prod->id,
                    'created_at'          => $prod->date,
                    'updated_at'          => $prod->date,
                ]);
            }
        }

        $now = Carbon::now();
        for ($i = 10; $i >= 1; $i--) {
            $date = $now->copy()->subDays($i * 3);
            DB::table('assay_results')->insert([
                'type'                => $types[array_rand($types)],
                'date'                => $date->toDateString(),
                'description'         => 'Routine sample ' . $date->format('d M Y'),
                'assay_value'         => round(rand(80, 300) / 100, 2),  // g/t: 0.80 – 3.00
                'daily_production_id' => null,
                'created_at'          => $date,
                'updated_at'          => $date,
            ]);
        }
    }
}
