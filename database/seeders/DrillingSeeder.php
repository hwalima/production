<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DrillingSeeder extends Seeder
{
    public function run(): void
    {
        $ends = ['M/Feed', 'M/Feed D', '7 level', 'Shaft', 'Bottom 3', '3L Grant F/W', 'Bottom 48'];
        $now  = Carbon::now();

        for ($i = 30; $i >= 1; $i--) {
            $date = $now->copy()->subDays($i);
            DB::table('drilling_records')->insert([
                'date'              => $date->toDateString(),
                'end_name'          => $ends[array_rand($ends)],
                'hole_count'        => rand(4, 20),
                'drill_steel_length'=> round(rand(18, 48) / 10 * 10, 1), // 1.8 – 4.8 m steels
                'advance'           => round(rand(12, 38) / 10, 2),       // 1.2 – 3.8 m advance
                'created_at'        => $date,
                'updated_at'        => $date,
            ]);
        }
    }
}
