<?php

namespace App\Http\Controllers;

use App\Models\AssayResult;
use App\Models\BlastingRecord;
use App\Models\ConsumableStockMovement;
use App\Models\DailyProduction;
use App\Models\DrillingRecord;
use App\Models\LabourEnergy;
use App\Models\MachineRuntime;
use App\Models\SheIndicator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to'   => 'nullable|date_format:Y-m-d',
        ]);

        $now = Carbon::now();

        // ── Date filter with session persistence ──────────────────────────
        $from = $request->filled('from')
            ? $request->input('from')
            : session('analytics_from', $now->copy()->subMonths(3)->startOfMonth()->toDateString());
        $to = $request->filled('to')
            ? $request->input('to')
            : session('analytics_to', $now->copy()->endOfMonth()->toDateString());

        if ($from > $to) {
            $from = Carbon::parse($to)->startOfMonth()->toDateString();
        }

        session(['analytics_from' => $from, 'analytics_to' => $to]);

        // ── Core production aggregates ────────────────────────────────────
        $prodRows = DailyProduction::whereBetween('date', [$from, $to])->orderBy('date')->get();
        $totalGoldSmelted = (float) $prodRows->sum('gold_smelted');
        $totalOreMilled   = (float) $prodRows->sum('ore_milled');
        $totalOreHoisted  = (float) $prodRows->sum('ore_hoisted');
        $daysRange        = max(1, Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1);

        // Per-day production sums (for join with assay)
        $prodByDay = DailyProduction::whereBetween('date', [$from, $to])
            ->selectRaw('DATE(date) as day, SUM(gold_smelted) as gold, SUM(ore_milled) as milled, SUM(ore_hoisted) as hoisted')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // ── Total costs by day (Labour+Energy + Consumables out) ──────────
        $leCostsByDay = LabourEnergy::whereBetween('date', [$from, $to])
            ->selectRaw('DATE(date) as day, SUM(labour_cost + zesa_cost + diesel_cost) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn($v) => (float) $v);

        $consumableCostsByDay = DB::table('consumable_stock_movements')
            ->whereBetween('movement_date', [$from, $to])
            ->where('direction', 'out')
            ->selectRaw('DATE(movement_date) as day, SUM(total_cost) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn($v) => (float) $v);

        $totalCostsByDay = [];
        foreach ($prodByDay as $row) {
            $totalCostsByDay[$row->day] = ($leCostsByDay[$row->day] ?? 0)
                + ($consumableCostsByDay[$row->day] ?? 0);
        }
        $totalAllCosts = array_sum($totalCostsByDay);

        // ── 1. Mill Recovery % ───────────────────────────────────────────
        $fireAssayByDate = AssayResult::whereBetween('date', [$from, $to])
            ->where('type', 'fire_assay')
            ->selectRaw('DATE(date) as day, AVG(assay_value) as avg_grade')
            ->groupBy('day')
            ->pluck('avg_grade', 'day')
            ->map(fn($v) => (float) $v);

        $recoveryTrendLabels = [];
        $recoveryTrendData   = [];
        foreach ($prodByDay as $row) {
            $grade = $fireAssayByDate[$row->day] ?? 0;
            if ($grade > 0 && (float) $row->milled > 0) {
                $rec = ((float) $row->gold / ((float) $row->milled * $grade)) * 100;
                $recoveryTrendData[] = round($rec, 1);
            } else {
                $recoveryTrendData[] = null;
            }
            $recoveryTrendLabels[] = Carbon::parse($row->day)->format('M d');
        }
        $validRecoveries = array_filter($recoveryTrendData, fn($v) => $v !== null);
        $avgMillRecovery = count($validRecoveries) > 0
            ? round(array_sum($validRecoveries) / count($validRecoveries), 1)
            : null;
        $hasAssayData = count($validRecoveries) > 0;

        // ── 2. AISC per gram (monthly buckets) ───────────────────────────
        $aiscByMonth = [];
        foreach ($prodByDay as $row) {
            $month = Carbon::parse($row->day)->format('Y-m');
            $aiscByMonth[$month]['gold'] = ($aiscByMonth[$month]['gold'] ?? 0) + (float) $row->gold;
            $aiscByMonth[$month]['cost'] = ($aiscByMonth[$month]['cost'] ?? 0) + ($totalCostsByDay[$row->day] ?? 0);
        }
        ksort($aiscByMonth);
        $aiscLabels = array_map(fn($m) => Carbon::parse($m . '-01')->format('M Y'), array_keys($aiscByMonth));
        $aiscData   = array_map(
            fn($v) => $v['gold'] > 0 ? round($v['cost'] / $v['gold'], 2) : null,
            array_values($aiscByMonth)
        );
        $avgAisc = $totalGoldSmelted > 0 ? round($totalAllCosts / $totalGoldSmelted, 2) : null;

        // ── 3. Grade Reconciliation ──────────────────────────────────────
        $fireAssayRaw = AssayResult::whereBetween('date', [$from, $to])
            ->where('type', 'fire_assay')
            ->orderBy('date')
            ->get(['date', 'assay_value']);

        $allRecDates = $prodByDay->pluck('day')
            ->merge($fireAssayRaw->pluck('date')->map(fn($d) => $d->format('Y-m-d')))
            ->unique()->sort()->values();

        $gradeRecLabels  = [];
        $gradeRecFire    = [];
        $gradeRecImplied = [];
        foreach ($allRecDates as $d) {
            $gradeRecLabels[] = Carbon::parse($d)->format('M d');
            $pRow = $prodByDay->firstWhere('day', $d);
            $gradeRecImplied[] = ($pRow && (float) $pRow->milled > 0)
                ? round((float) $pRow->gold / (float) $pRow->milled, 4)
                : null;
            $fRow = $fireAssayRaw->first(fn($r) => $r->date->format('Y-m-d') === $d);
            $gradeRecFire[] = $fRow ? round((float) $fRow->assay_value, 4) : null;
        }

        // ── 4. Cost per tonne milled (monthly) ───────────────────────────
        $cptByMonth = [];
        foreach ($prodByDay as $row) {
            $month = Carbon::parse($row->day)->format('Y-m');
            $cptByMonth[$month]['milled'] = ($cptByMonth[$month]['milled'] ?? 0) + (float) $row->milled;
            $cptByMonth[$month]['cost']   = ($cptByMonth[$month]['cost'] ?? 0) + ($totalCostsByDay[$row->day] ?? 0);
        }
        ksort($cptByMonth);
        $cptLabels = array_map(fn($m) => Carbon::parse($m . '-01')->format('M Y'), array_keys($cptByMonth));
        $cptData   = array_map(
            fn($v) => $v['milled'] > 0 ? round($v['cost'] / $v['milled'], 2) : null,
            array_values($cptByMonth)
        );
        $avgCostPerTonne = $totalOreMilled > 0 ? round($totalAllCosts / $totalOreMilled, 2) : null;

        // ── 5. MoM / YTD comparison ──────────────────────────────────────
        $prevFrom = Carbon::parse($from)->subMonth()->startOfMonth()->toDateString();
        $prevTo   = Carbon::parse($from)->subMonth()->endOfMonth()->toDateString();
        $prevProd = DailyProduction::whereBetween('date', [$prevFrom, $prevTo])->get();
        $prevGold   = (float) $prevProd->sum('gold_smelted');
        $prevMilled = (float) $prevProd->sum('ore_milled');
        $prevCosts  = (float) (LabourEnergy::whereBetween('date', [$prevFrom, $prevTo])
            ->selectRaw('SUM(labour_cost + zesa_cost + diesel_cost) as t')->value('t') ?? 0);
        $prevCosts += (float) DB::table('consumable_stock_movements')
            ->whereBetween('movement_date', [$prevFrom, $prevTo])
            ->where('direction', 'out')->sum('total_cost');

        $ytdFrom = Carbon::parse($from)->startOfYear()->toDateString();
        $ytdProd  = DailyProduction::whereBetween('date', [$ytdFrom, $to])->get();
        $ytdGold   = (float) $ytdProd->sum('gold_smelted');
        $ytdMilled = (float) $ytdProd->sum('ore_milled');

        $momGoldDelta   = $prevGold > 0 ? round((($totalGoldSmelted - $prevGold) / $prevGold) * 100, 1) : null;
        $momMilledDelta = $prevMilled > 0 ? round((($totalOreMilled - $prevMilled) / $prevMilled) * 100, 1) : null;
        $momCostDelta   = $prevCosts > 0 ? round((($totalAllCosts - $prevCosts) / $prevCosts) * 100, 1) : null;

        // ── 6. Stockpile balance trend ───────────────────────────────────
        $stockpileTrend = DailyProduction::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['date', 'uncrushed_stockpile', 'unmilled_stockpile']);
        $stockLabels    = $stockpileTrend->pluck('date')->map(fn($d) => $d->format('M d'))->toArray();
        $stockUncrushed = $stockpileTrend->pluck('uncrushed_stockpile')->map(fn($v) => (float) $v)->toArray();
        $stockUnmilled  = $stockpileTrend->pluck('unmilled_stockpile')->map(fn($v) => (float) $v)->toArray();
        $latestUncrushed = end($stockUncrushed) ?: 0;
        $latestUnmilled  = end($stockUnmilled) ?: 0;

        // ── 7. Blasting / Powder factor (ANFO trend) ─────────────────────
        $blastingRows = BlastingRecord::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['date', 'anfo', 'oil', 'fractures']);
        $blastLabels = $blastingRows->pluck('date')->map(fn($d) => $d->format('M d'))->toArray();
        $blastAnfo   = $blastingRows->pluck('anfo')->map(fn($v) => (float) $v)->toArray();
        $blastOil    = $blastingRows->pluck('oil')->map(fn($v) => (float) $v)->toArray();
        $totalAnfo   = (float) $blastingRows->sum('anfo');
        $totalOil    = (float) $blastingRows->sum('oil');
        $avgAnfoPerDay = count($blastLabels) > 0 ? round($totalAnfo / count($blastLabels), 2) : 0;

        // ── 8. SHE Safety rates ──────────────────────────────────────────
        $sheRows = SheIndicator::whereBetween('date', [$from, $to])->orderBy('date')->get();
        $totalLti     = (int) $sheRows->sum('lti');
        $totalNlti    = (int) $sheRows->sum('nlti');
        $totalFatal   = (int) $sheRows->sum('fatal_incident');
        $totalMedical = (int) $sheRows->sum('medical_injury_case');
        $totalSick    = (int) $sheRows->sum('sick');
        $totalLeave   = (int) $sheRows->sum('leave');
        $totalAwol    = (int) $sheRows->sum('awol');
        $totalAbsence = $totalSick + $totalLeave + $totalAwol;

        $sheByMonth = $sheRows->groupBy(fn($r) => $r->date->format('Y-m'))
            ->map(fn($rows) => [
                'lti'     => $rows->sum('lti'),
                'nlti'    => $rows->sum('nlti'),
                'medical' => $rows->sum('medical_injury_case'),
                'fatal'   => $rows->sum('fatal_incident'),
            ])
            ->sortKeys();
        $sheMonthLabels  = array_map(fn($m) => Carbon::parse($m . '-01')->format('M Y'), $sheByMonth->keys()->toArray());
        $sheMonthLti     = $sheByMonth->pluck('lti')->toArray();
        $sheMonthNlti    = $sheByMonth->pluck('nlti')->toArray();
        $sheMonthMedical = $sheByMonth->pluck('medical')->toArray();

        // ── 9. Consumables burn rate + runway ────────────────────────────
        $burnByCategory = DB::table('consumable_stock_movements')
            ->join('consumables', 'consumables.id', '=', 'consumable_stock_movements.consumable_id')
            ->whereBetween('movement_date', [$from, $to])
            ->where('consumable_stock_movements.direction', 'out')
            ->selectRaw('consumables.category, SUM(consumable_stock_movements.total_cost) as total_cost, SUM(consumable_stock_movements.quantity) as total_qty')
            ->groupBy('consumables.category')
            ->orderByDesc('total_cost')
            ->get();

        // Consumables cost trend monthly
        $consumMonthly = DB::table('consumable_stock_movements')
            ->whereBetween('movement_date', [$from, $to])
            ->where('direction', 'out')
            ->selectRaw("DATE_FORMAT(movement_date, '%Y-%m') as month, SUM(total_cost) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $consumMonthLabels = $consumMonthly->map(fn($r) => Carbon::parse($r->month . '-01')->format('M Y'))->toArray();
        $consumMonthData   = $consumMonthly->pluck('total')->map(fn($v) => (float) $v)->toArray();

        // ── 10. Drill metres trend ───────────────────────────────────────
        $drillRows = DrillingRecord::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['date', 'advance', 'hole_count']);
        $drillLabels  = $drillRows->pluck('date')->map(fn($d) => $d->format('M d'))->toArray();
        $drillAdvance = $drillRows->pluck('advance')->map(fn($v) => (float) $v)->toArray();
        $totalAdvance = (float) $drillRows->sum('advance');
        $totalHoles   = (int) $drillRows->sum('hole_count');
        $avgAdvPerDay = count($drillLabels) > 0 ? round($totalAdvance / count($drillLabels), 2) : 0;

        // ── 11. SPC Control Chart (implied gold grade ±2σ) ───────────────
        $spcData = $prodByDay
            ->filter(fn($r) => (float) $r->milled > 0)
            ->map(fn($r) => ['day' => $r->day, 'grade' => round((float) $r->gold / (float) $r->milled, 4)])
            ->values();
        $spcGrades = $spcData->pluck('grade');
        $spcMean   = $spcGrades->count() > 0 ? $spcGrades->avg() : 0;
        $spcStd    = $spcGrades->count() > 1
            ? sqrt($spcGrades->map(fn($v) => ($v - $spcMean) ** 2)->avg())
            : 0;
        $spcUcl    = round($spcMean + 2 * $spcStd, 4);
        $spcLcl    = round(max(0, $spcMean - 2 * $spcStd), 4);
        $spcLabels = $spcData->pluck('day')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
        $spcValues = $spcData->pluck('grade')->toArray();

        // ── 12. Predictive Maintenance ───────────────────────────────────
        $machines = MachineRuntime::orderBy('machine_code')->get();
        $machineScores = $machines->map(function ($m) {
            $daysToService = $m->next_service_date
                ? (int) Carbon::now()->diffInDays($m->next_service_date, false)
                : null;
            $score  = null;
            $status = 'unknown';
            if ($daysToService !== null) {
                // Service interval (hours → days, default 90 days)
                $intervalDays = (float) ($m->service_after_hours > 0 ? $m->service_after_hours / 24 : 90);
                $score        = max(0, min(100, (int) round(($daysToService / max(1, $intervalDays)) * 100)));
                $status       = $daysToService < 0 ? 'overdue' : ($daysToService <= 7 ? 'due_soon' : 'ok');
            }
            return [
                'code'            => $m->machine_code,
                'description'     => $m->description,
                'next_service'    => $m->next_service_date?->format('M d, Y'),
                'days_to_service' => $daysToService,
                'score'           => $score,
                'status'          => $status,
            ];
        })->toArray();

        // ── 13. Anomaly Detection (gold grade z-score) ───────────────────
        $anomalies = [];
        if ($spcGrades->count() > 5) {
            foreach ($spcData as $pt) {
                $z = $spcStd > 0 ? ($pt['grade'] - $spcMean) / $spcStd : 0;
                if (abs($z) > 2) {
                    $anomalies[] = [
                        'date'   => Carbon::parse($pt['day'])->format('d M Y'),
                        'metric' => 'Implied Grade',
                        'value'  => round($pt['grade'], 4) . ' g/t',
                        'z'      => round($z, 2),
                        'dir'    => $z > 0 ? 'above' : 'below',
                    ];
                }
            }
        }
        // Gold smelted anomalies
        $goldByDay = $prodByDay->pluck('gold')->map(fn($v) => (float) $v);
        $goldMean  = $goldByDay->count() > 0 ? $goldByDay->avg() : 0;
        $goldStd   = $goldByDay->count() > 1
            ? sqrt($goldByDay->map(fn($v) => ($v - $goldMean) ** 2)->avg())
            : 0;
        if ($goldStd > 0) {
            foreach ($prodByDay as $row) {
                $z = ($row->gold - $goldMean) / $goldStd;
                if (abs($z) > 2) {
                    $anomalies[] = [
                        'date'   => Carbon::parse($row->day)->format('d M Y'),
                        'metric' => 'Gold Smelted',
                        'value'  => round((float) $row->gold, 2) . ' g',
                        'z'      => round($z, 2),
                        'dir'    => $z > 0 ? 'above' : 'below',
                    ];
                }
            }
        }
        usort($anomalies, fn($a, $b) => abs($b['z']) <=> abs($a['z']));

        return view('analytics.index', compact(
            'from', 'to', 'now',
            'totalGoldSmelted', 'totalOreMilled', 'totalOreHoisted', 'totalAllCosts', 'daysRange',
            // 1
            'avgMillRecovery', 'hasAssayData', 'recoveryTrendLabels', 'recoveryTrendData',
            // 2
            'avgAisc', 'aiscLabels', 'aiscData',
            // 3
            'gradeRecLabels', 'gradeRecFire', 'gradeRecImplied',
            // 4
            'avgCostPerTonne', 'cptLabels', 'cptData',
            // 5
            'momGoldDelta', 'momMilledDelta', 'momCostDelta',
            'prevGold', 'prevMilled', 'prevCosts', 'prevFrom', 'prevTo',
            'ytdGold', 'ytdMilled',
            // 6
            'stockLabels', 'stockUncrushed', 'stockUnmilled', 'latestUncrushed', 'latestUnmilled',
            // 7
            'blastLabels', 'blastAnfo', 'blastOil', 'totalAnfo', 'totalOil', 'avgAnfoPerDay',
            // 8
            'totalLti', 'totalNlti', 'totalFatal', 'totalMedical', 'totalSick', 'totalLeave', 'totalAwol', 'totalAbsence',
            'sheMonthLabels', 'sheMonthLti', 'sheMonthNlti', 'sheMonthMedical',
            // 9
            'burnByCategory', 'consumMonthLabels', 'consumMonthData',
            // 10
            'drillLabels', 'drillAdvance', 'totalAdvance', 'totalHoles', 'avgAdvPerDay',
            // 11
            'spcLabels', 'spcValues', 'spcMean', 'spcStd', 'spcUcl', 'spcLcl',
            // 12
            'machineScores',
            // 13
            'anomalies'
        ));
    }

    public function export(Request $request)
    {
        $from = $request->input('from', session('analytics_from', Carbon::now()->subMonths(3)->startOfMonth()->toDateString()));
        $to   = $request->input('to',   session('analytics_to',   Carbon::now()->endOfMonth()->toDateString()));

        $filename = 'analytics_' . $from . '_to_' . $to . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($from, $to) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['MyMine Analytics Export', 'Period: ' . $from . ' to ' . $to]);
            fputcsv($handle, ['Generated', now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Daily Production
            fputcsv($handle, ['=== DAILY PRODUCTION ===']);
            fputcsv($handle, ['Date', 'Ore Milled (t)', 'Ore Hoisted (t)', 'Gold Smelted (g)', 'Fire Assay Grade (g/t)', 'Mill Recovery %']);

            $prodByDay = DailyProduction::whereBetween('date', [$from, $to])
                ->selectRaw('DATE(date) as day, SUM(gold_smelted) as gold, SUM(ore_milled) as milled, SUM(ore_hoisted) as hoisted')
                ->groupBy('day')->orderBy('day')->get();

            $fireAssayByDate = AssayResult::whereBetween('date', [$from, $to])
                ->where('type', 'fire_assay')
                ->selectRaw('DATE(date) as day, AVG(assay_value) as avg_grade')
                ->groupBy('day')->pluck('avg_grade', 'day');

            foreach ($prodByDay as $row) {
                $grade    = (float) ($fireAssayByDate[$row->day] ?? 0);
                $recovery = ($grade > 0 && (float) $row->milled > 0)
                    ? round(((float) $row->gold / ((float) $row->milled * $grade)) * 100, 1)
                    : '';
                fputcsv($handle, [$row->day, $row->milled, $row->hoisted, $row->gold, $grade ?: '', $recovery]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['=== COSTS (Labour + Energy + Consumables Out) ===']);
            fputcsv($handle, ['Date', 'Labour+Energy Cost', 'Consumables Cost', 'Total Cost']);

            $leCosts = LabourEnergy::whereBetween('date', [$from, $to])
                ->selectRaw('DATE(date) as day, SUM(labour_cost + zesa_cost + diesel_cost) as total')
                ->groupBy('day')->pluck('total', 'day');
            $cCosts = DB::table('consumable_stock_movements')
                ->whereBetween('movement_date', [$from, $to])->where('direction', 'out')
                ->selectRaw('DATE(movement_date) as day, SUM(total_cost) as total')
                ->groupBy('day')->pluck('total', 'day');

            $allDays = collect(array_keys($leCosts->toArray()) + array_keys($cCosts->toArray()))->unique()->sort();
            foreach ($allDays as $day) {
                $le = (float) ($leCosts[$day] ?? 0);
                $cc = (float) ($cCosts[$day] ?? 0);
                fputcsv($handle, [$day, $le, $cc, $le + $cc]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['=== DRILLING ===']);
            fputcsv($handle, ['Date', 'Advance (m)', 'Hole Count']);
            foreach (DrillingRecord::whereBetween('date', [$from, $to])->orderBy('date')->get() as $r) {
                fputcsv($handle, [$r->date->format('Y-m-d'), $r->advance, $r->hole_count]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['=== BLASTING ===']);
            fputcsv($handle, ['Date', 'ANFO (kg)', 'Oil (L)', 'Fractures']);
            foreach (BlastingRecord::whereBetween('date', [$from, $to])->orderBy('date')->get() as $r) {
                fputcsv($handle, [$r->date->format('Y-m-d'), $r->anfo, $r->oil, $r->fractures]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['=== SHE INDICATORS ===']);
            fputcsv($handle, ['Date', 'LTI', 'NLTI', 'Fatal', 'Medical', 'Sick', 'Leave', 'AWOL']);
            foreach (SheIndicator::whereBetween('date', [$from, $to])->orderBy('date')->get() as $r) {
                fputcsv($handle, [$r->date->format('Y-m-d'), $r->lti, $r->nlti, $r->fatal_incident, $r->medical_injury_case, $r->sick, $r->leave, $r->awol]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
