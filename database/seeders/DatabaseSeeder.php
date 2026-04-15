<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            DemoUserSeeder::class,
            DailyProductionSeeder::class,
            AssayResultSeeder::class,
            DrillingSeeder::class,
            BlastingSeeder::class,
            ChemicalsSeeder::class,
            LabourEnergySeeder::class,
            MachineRuntimeSeeder::class,
        ]);
    }
}
