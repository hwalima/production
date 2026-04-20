<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MiningDepartment;
use Carbon\Carbon;

/**
 * Seeds realistic sample data for 13, 14, 15, 16, 17 & 20 April 2026.
 * Gold Fidelity price: $156/g (as specified).
 * Safe to run multiple times — deletes existing rows for these dates first.
 */
class AprilAnalyticsSampleSeeder extends Seeder
{
    // The 6 working days to seed
    private array $dates = [
        '2026-04-13',
        '2026-04-14',
        '2026-04-15',
        '2026-04-16',
        '2026-04-17',
        '2026-04-20',
    ];

    // Realistic daily production figures (underground narrow reef, ~2 g/t)
    private array $prodData = [
        //  date           hoisted  waste  crushed  milled   grade  recovery  purity  uncrushed  unmilled  shift
        '2026-04-13' => [82,  22,  78,  68,  2.10, 0.89,  95.5,  18.0,  10.0, 'Day'],
        '2026-04-14' => [76,  18,  74,  65,  1.95, 0.91,  94.8,  22.0,  19.0, 'Day'],
        '2026-04-15' => [90,  25,  85,  72,  2.25, 0.88,  96.0,  27.0,  32.0, 'Day'],
        '2026-04-16' => [78,  20,  75,  70,  2.05, 0.90,  95.2,  30.0,  37.0, 'Night'],
        '2026-04-17' => [85,  23,  80,  75,  2.30, 0.92,  95.8,  35.0,  42.0, 'Day'],
        '2026-04-20' => [88,  21,  84,  78,  2.15, 0.90,  96.1,  39.0,  48.0, 'Day'],
    ];

    public function run(): void
    {
        // ── 0. Purge existing rows for these dates ─────────────────────────
        DB::table('daily_productions')->whereIn('date', $this->dates)->delete();
        DB::table('assay_results')->whereIn(DB::raw('DATE(date)'), $this->dates)->delete();
        DB::table('labour_energy')->whereIn('date', $this->dates)->delete();
        DB::table('blasting_records')->whereIn('date', $this->dates)->delete();
        DB::table('drilling_records')->whereIn('date', $this->dates)->delete();
        DB::table('she_indicators')->whereIn('date', $this->dates)->delete();
        DB::table('consumable_stock_movements')->whereIn(DB::raw('DATE(movement_date)'), $this->dates)->delete();

        // ── 1. Daily Production ────────────────────────────────────────────
        $prodIds = [];
        foreach ($this->dates as $date) {
            [$hoisted, $waste, $crushed, $milled, $grade, $recovery, $purity, $uncrushed, $unmilled, $shift]
                = $this->prodData[$date];

            $goldSmelted = round($milled * $grade * $recovery, 2);

            $id = DB::table('daily_productions')->insertGetId([
                'date'               => $date,
                'shift'              => $shift,
                'mining_site'        => 'Main Reef',
                'ore_hoisted'        => $hoisted,
                'ore_hoisted_target' => round($hoisted * 1.05, 1),
                'waste_hoisted'      => $waste,
                'uncrushed_stockpile'=> $uncrushed,
                'ore_crushed'        => $crushed,
                'unmilled_stockpile' => $unmilled,
                'ore_milled'         => $milled,
                'ore_milled_target'  => round($milled * 1.05, 1),
                'gold_smelted'       => $goldSmelted,
                'purity_percentage'  => $purity,
                'fidelity_price'     => 156.00,   // $156/g as specified
                'created_at'         => $date,
                'updated_at'         => $date,
            ]);

            $prodIds[$date] = $id;
        }

        // ── 2. Assay Results (fire_assay for each day) ─────────────────────
        $assayGrades = [
            '2026-04-13' => 2.10,
            '2026-04-14' => 1.95,
            '2026-04-15' => 2.25,
            '2026-04-16' => 2.05,
            '2026-04-17' => 2.30,
            '2026-04-20' => 2.15,
        ];
        foreach ($this->dates as $date) {
            DB::table('assay_results')->insert([
                'type'                => 'fire_assay',
                'date'                => $date,
                'description'         => 'Fire assay – ' . Carbon::parse($date)->format('d M Y'),
                'assay_value'         => $assayGrades[$date],
                'daily_production_id' => $prodIds[$date],
                'created_at'          => $date,
                'updated_at'          => $date,
            ]);
        }

        // ── 3. Labour & Energy costs ───────────────────────────────────────
        $leCosts = [
            '2026-04-13' => [280.00, 640.00, 430.00],
            '2026-04-14' => [295.00, 610.00, 445.00],
            '2026-04-15' => [310.00, 680.00, 420.00],
            '2026-04-16' => [275.00, 655.00, 438.00],
            '2026-04-17' => [300.00, 670.00, 450.00],
            '2026-04-20' => [285.00, 625.00, 435.00],
        ];
        foreach ($this->dates as $date) {
            [$labour, $zesa, $diesel] = $leCosts[$date];
            DB::table('labour_energy')->insert([
                'date'        => $date,
                'labour_cost' => $labour,
                'zesa_cost'   => $zesa,
                'diesel_cost' => $diesel,
                'created_at'  => $date,
                'updated_at'  => $date,
            ]);
        }

        // ── 4. Blasting Records ────────────────────────────────────────────
        $blastData = [
            '2026-04-13' => [12.5, 2.8,  8, 45, 6, 12,  4],
            '2026-04-14' => [10.0, 2.2,  6, 38, 5, 10,  3],
            '2026-04-15' => [15.0, 3.5, 10, 52, 7, 14,  5],
            '2026-04-16' => [11.5, 2.5,  7, 42, 6, 11,  4],
            '2026-04-17' => [13.5, 3.0,  9, 48, 7, 13,  4],
            '2026-04-20' => [14.0, 3.2,  9, 50, 7, 13,  5],
        ];
        foreach ($this->dates as $date) {
            [$anfo, $oil, $fractures, $fuse, $carmes, $power, $bits] = $blastData[$date];
            DB::table('blasting_records')->insert([
                'date'        => $date,
                'anfo'        => $anfo,
                'oil'         => $oil,
                'fractures'   => $fractures,
                'fuse'        => $fuse,
                'carmes_ieds' => $carmes,
                'power_cords' => $power,
                'drill_bits'  => $bits,
                'created_at'  => $date,
                'updated_at'  => $date,
            ]);
        }

        // ── 5. Drilling Records ────────────────────────────────────────────
        $drillData = [
            '2026-04-13' => ['7 Level',    14, 3.6, 2.80],
            '2026-04-14' => ['M/Feed',     12, 3.2, 2.40],
            '2026-04-15' => ['3L Grant F/W', 16, 4.2, 3.20],
            '2026-04-16' => ['Bottom 3',   13, 3.6, 2.60],
            '2026-04-17' => ['Shaft',      15, 4.0, 3.00],
            '2026-04-20' => ['7 Level',    15, 3.8, 2.90],
        ];
        foreach ($this->dates as $date) {
            [$end, $holes, $steel, $advance] = $drillData[$date];
            DB::table('drilling_records')->insert([
                'date'               => $date,
                'end_name'           => $end,
                'hole_count'         => $holes,
                'drill_steel_length' => $steel,
                'advance'            => $advance,
                'created_at'         => $date,
                'updated_at'         => $date,
            ]);
        }

        // ── 6. SHE Indicators ─────────────────────────────────────────────
        $dept = MiningDepartment::firstOrCreate(['name' => 'Mining'], ['description' => 'Underground Mining Department']);

        $sheData = [
            '2026-04-13' => [0, 0, 0, 1, 2, 1, 0],
            '2026-04-14' => [0, 0, 0, 0, 1, 2, 0],
            '2026-04-15' => [0, 0, 0, 0, 1, 0, 0],
            '2026-04-16' => [0, 1, 0, 0, 2, 1, 0],  // 1 non-LTI
            '2026-04-17' => [0, 0, 0, 1, 1, 1, 0],
            '2026-04-20' => [0, 0, 0, 0, 0, 2, 0],
        ];
        foreach ($this->dates as $date) {
            [$fatal, $nlti, $lti, $medical, $sick, $leave, $awol] = $sheData[$date];
            DB::table('she_indicators')->insert([
                'date'                 => $date,
                'mining_department_id' => $dept->id,
                'fatal_incident'       => $fatal,
                'lti'                  => $lti,
                'nlti'                 => $nlti,
                'medical_injury_case'  => $medical,
                'sick'                 => $sick,
                'leave'                => $leave,
                'awol'                 => $awol,
                'offdays'              => 0,
                'iod'                  => 0,
                'terminations'         => 0,
                'created_at'           => $date,
                'updated_at'           => $date,
            ]);
        }

        // ── 7. Consumable Stock Movements (out) ───────────────────────────
        // Use first consumable in each category, or skip gracefully if none exist
        $consumableIds = DB::table('consumables')
            ->select('id', 'category', 'name')
            ->orderBy('id')
            ->get()
            ->groupBy('category')
            ->map(fn($rows) => $rows->first());

        if ($consumableIds->count() > 0) {
            $consumCosts = [14.50, 12.80, 18.20, 13.50, 16.00, 15.40];
            foreach ($this->dates as $i => $date) {
                foreach ($consumableIds->take(3) as $cat => $consumable) {
                    DB::table('consumable_stock_movements')->insert([
                        'consumable_id'  => $consumable->id,
                        'movement_date'  => $date,
                        'direction'      => 'out',
                        'quantity'       => round(rand(5, 15) / 10, 1),
                        'total_cost'     => round($consumCosts[$i] * (1 + (ord($cat[0]) % 5) * 0.1), 2),
                        'notes'          => 'Daily issue – ' . Carbon::parse($date)->format('d M Y'),
                        'created_at'     => $date,
                        'updated_at'     => $date,
                    ]);
                }
            }
        }

        // ── 8. Machine Runtimes (skip if already seeded — just ensure records exist) ──
        $existingMachines = DB::table('machine_runtimes')->count();
        if ($existingMachines === 0) {
            $machines = [
                ['MILL-01',  'Ball Mill',         2160, 30],
                ['CRUSH-01', 'Jaw Crusher',        1080, 20],
                ['HOIST-01', 'Shaft Hoist',         504, 10],
                ['PUMP-01',  'Dewatering Pump #1',  336, 5],
                ['GEN-01',   'Diesel Generator #1', 504, 8],
            ];
            foreach ($machines as [$code, $desc, $serviceHours, $daysLeft]) {
                DB::table('machine_runtimes')->insert([
                    'machine_code'       => $code,
                    'description'        => $desc,
                    'start_time'         => '2026-04-13 07:00:00',
                    'end_time'           => '2026-04-13 19:00:00',
                    'service_after_hours'=> $serviceHours,
                    'next_service_date'  => Carbon::parse('2026-04-20')->addDays($daysLeft)->toDateString(),
                    'created_at'         => '2026-04-13',
                    'updated_at'         => '2026-04-20',
                ]);
            }
        }

        $this->command->info('✓ April 2026 sample data seeded for: ' . implode(', ', $this->dates));
    }
}
