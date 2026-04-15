<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabourEnergySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        for ($i = 30; $i >= 1; $i--) {
            $date = $now->copy()->subDays($i);
            // Slight day-to-day variation around the base values from settings
            DB::table('labour_energy')->insert([
                'date'         => $date->toDateString(),
                'zesa_cost'    => round(633 + rand(-50, 80), 2),
                'diesel_cost'  => round(428 + rand(-30, 60), 2),
                'labour_cost'  => round(rand(180, 320), 2),
                'created_at'   => $date,
                'updated_at'   => $date,
            ]);
        }
    }
}
