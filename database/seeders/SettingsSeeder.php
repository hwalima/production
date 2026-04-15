<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('settings')->insert([
            ['key' => 'zesa_daily', 'value' => '633', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'diesel_daily', 'value' => '428', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'labour_daily', 'value' => '0', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mining_levels', 'value' => 'M/Feed,M/Feed D,7 level,Shaft,Bottom 3,3L Grant F/W,3L Grant F/W B,Bottom 48', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_name', 'value' => 'Epoch Mines and Resources', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_location', 'value' => 'Filabusi, Zimbabwe', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_address', 'value' => 'P.O. Box 1, Filabusi', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_phone', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_email', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_website', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'logo_path', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
