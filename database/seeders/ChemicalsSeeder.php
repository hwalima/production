<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChemicalsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        for ($i = 30; $i >= 1; $i--) {
            $date = $now->copy()->subDays($i);
            DB::table('chemicals')->insert([
                'date'               => $date->toDateString(),
                'sodium_cyanide'     => round(rand(80, 200) / 10, 2),    // kg
                'lime'               => round(rand(50, 180) / 10, 2),    // kg
                'caustic_soda'       => round(rand(10, 60)  / 10, 2),    // kg
                'iodised_salt'       => round(rand(5, 40)   / 10, 2),    // kg
                'mercury'            => round(rand(1, 15)   / 10, 2),    // kg
                'steel_balls'        => round(rand(200, 800)/ 10, 2),    // kg
                'hydrogen_peroxide'  => round(rand(5, 50)   / 10, 2),    // litres
                'borax'              => round(rand(10, 60)  / 10, 2),    // kg
                'nitric_acid'        => round(rand(5, 30)   / 10, 2),    // litres
                'sulphuric_acid'     => round(rand(5, 30)   / 10, 2),    // litres
                'created_at'         => $date,
                'updated_at'         => $date,
            ]);
        }
    }
}
