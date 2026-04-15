<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BlastingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        for ($i = 30; $i >= 1; $i--) {
            $date = $now->copy()->subDays($i);
            DB::table('blasting_records')->insert([
                'date'        => $date->toDateString(),
                'fractures'   => rand(4, 18),
                'fuse'        => rand(20, 80),          // metres of fuse
                'carmes_ieds' => rand(2, 12),
                'power_cords' => rand(4, 20),
                'anfo'        => round(rand(50, 200) / 10, 2),   // kg of ANFO
                'oil'         => round(rand(5, 30) / 10, 2),     // litres of oil
                'drill_bits'  => rand(1, 6),
                'created_at'  => $date,
                'updated_at'  => $date,
            ]);
        }
    }
}
