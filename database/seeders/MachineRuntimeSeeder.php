<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MachineRuntimeSeeder extends Seeder
{
    // Epoch Mines fleet
    private array $machines = [
        ['code' => 'COMP-01', 'desc' => 'Air Compressor #1',       'interval' => 30],
        ['code' => 'COMP-02', 'desc' => 'Air Compressor #2',       'interval' => 30],
        ['code' => 'PUMP-01', 'desc' => 'Dewatering Pump #1',      'interval' => 14],
        ['code' => 'PUMP-02', 'desc' => 'Dewatering Pump #2',      'interval' => 14],
        ['code' => 'MILL-01', 'desc' => 'Ball Mill',               'interval' => 60],
        ['code' => 'CRUSH-01','desc' => 'Jaw Crusher',             'interval' => 45],
        ['code' => 'HOIST-01','desc' => 'Shaft Hoist',             'interval' => 21],
        ['code' => 'GEN-01',  'desc' => 'Diesel Generator #1',     'interval' => 21],
        ['code' => 'GEN-02',  'desc' => 'Diesel Generator #2',     'interval' => 21],
        ['code' => 'LEACH-01','desc' => 'Leach Tank Agitator',     'interval' => 90],
    ];

    public function run(): void
    {
        $now = Carbon::now();

        foreach ($this->machines as $m) {
            // Generate 3 runtime entries per machine over the last 90 days
            for ($run = 2; $run >= 0; $run--) {
                $startDay = $now->copy()->subDays($run * 28 + rand(0, 5));
                $hoursRun = rand(6, 22);
                $endTime  = $startDay->copy()->addHours($hoursRun);

                DB::table('machine_runtimes')->insert([
                    'machine_code'       => $m['code'],
                    'description'        => $m['desc'],
                    'start_time'         => $startDay->format('Y-m-d 07:00:00'),
                    'end_time'           => $endTime->format('Y-m-d H:i:s'),
                    'service_after_hours'=> $m['interval'],
                    'next_service_date'  => $endTime->copy()->addDays($m['interval'])->toDateString(),
                    'created_at'         => $startDay,
                    'updated_at'         => $startDay,
                ]);
            }
        }
    }
}
