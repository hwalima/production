<?php
namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MachineRuntimeSeeder extends Seeder
{
    // Epoch Mines fleet
    private array $machines = [
        ['code' => 'COMP-01', 'desc' => 'Air Compressor #1',       'interval' => 720],
        ['code' => 'COMP-02', 'desc' => 'Air Compressor #2',       'interval' => 720],
        ['code' => 'PUMP-01', 'desc' => 'Dewatering Pump #1',      'interval' => 336],
        ['code' => 'PUMP-02', 'desc' => 'Dewatering Pump #2',      'interval' => 336],
        ['code' => 'MILL-01', 'desc' => 'Ball Mill',               'interval' => 1440],
        ['code' => 'CRUSH-01','desc' => 'Jaw Crusher',             'interval' => 1080],
        ['code' => 'HOIST-01','desc' => 'Shaft Hoist',             'interval' => 504],
        ['code' => 'GEN-01',  'desc' => 'Diesel Generator #1',     'interval' => 504],
        ['code' => 'GEN-02',  'desc' => 'Diesel Generator #2',     'interval' => 504],
        ['code' => 'LEACH-01','desc' => 'Leach Tank Agitator',     'interval' => 2160],
    ];

    public function run(): void
    {
        $now = Carbon::now();

        foreach ($this->machines as $m) {
            $machine = Machine::firstOrCreate(
                ['machine_code' => $m['code']],
                [
                    'description' => $m['desc'],
                    'service_interval_hours' => $m['interval'],
                    'is_active' => true,
                ],
            );

            // Generate 3 runtime entries per machine over the last 90 days
            for ($run = 2; $run >= 0; $run--) {
                $startDay = $now->copy()->subDays($run * 28 + rand(0, 5));
                $hoursRun = rand(6, 22);
                $startTime = $startDay->copy()->setTime(7, 0);
                $endTime = $startTime->copy()->addHours($hoursRun);

                DB::table('machine_runtimes')->insert([
                    'machine_id'         => $machine->id,
                    'machine_code'       => $m['code'],
                    'description'        => $m['desc'],
                    'start_time'         => $startTime->format('Y-m-d H:i:s'),
                    'end_time'           => $endTime->format('Y-m-d H:i:s'),
                    'hours_run'          => $hoursRun,
                    'service_after_hours'=> $m['interval'],
                    'created_at'         => $startDay,
                    'updated_at'         => $startDay,
                ]);
            }
        }
    }
}
