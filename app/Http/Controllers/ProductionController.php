<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\AuditLog;
use App\Models\MiningRecord;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\MiningSite;
use App\Services\StockpileService;
use App\Http\Requests\StoreDailyProductionRequest;
use App\Http\Requests\UpdateDailyProductionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductionController extends Controller
{
    /* ── index ───────────────────────────────────────── */

    public function index(Request $request)
    {
        $now        = \Carbon\Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();
        $filterShift = $request->input('shift', '');   // '' = all shifts

        $baseQuery = DailyProduction::whereBetween('date', [$filterFrom, $filterTo]);
        $miningQuery = MiningRecord::whereBetween('date', [$filterFrom, $filterTo]);
        if ($filterShift !== '') {
            $baseQuery->where('shift', $filterShift);
            $miningQuery->where('shift', $filterShift);
        }

        $productions = (clone $baseQuery)->orderByDesc('date')->orderByDesc('id')
            ->paginate(30)->withQueryString();
        $miningGroups = MiningRecord::whereBetween('date', [$filterFrom, $filterTo])
            ->selectRaw('date, shift, mining_site, SUM(ore_hoisted) as ore_hoisted, SUM(ore_hoisted_target) as ore_hoisted_target, SUM(waste_hoisted) as waste_hoisted')
            ->groupBy('date', 'shift', 'mining_site')
            ->get();
        $miningByKey = [];
        foreach ($miningGroups as $group) {
            $key = implode('|', [substr((string) $group->date, 0, 10), $group->shift ?? '', $group->mining_site ?? '']);
            $miningByKey[$key] = $group;
        }

        $totals = (clone $baseQuery)
            ->selectRaw('
                SUM(ore_crushed)        as ore_crushed,
                SUM(ore_milled)         as ore_milled,
                SUM(ro_mine_milled)     as ro_mine_milled,
                SUM(sanda_milled)       as sanda_milled,
                SUM(ore_milled_target)  as ore_milled_target,
                SUM(gold_smelted)       as gold_smelted,
                AVG(purity_percentage)  as avg_purity
            ')->first();
        $miningTotals = (clone $miningQuery)->selectRaw('
            SUM(ore_hoisted) as ore_hoisted,
            SUM(ore_hoisted_target) as ore_hoisted_target,
            SUM(waste_hoisted) as waste_hoisted
        ')->first();
        $totals->ore_hoisted = $miningTotals->ore_hoisted;
        $totals->ore_hoisted_target = $miningTotals->ore_hoisted_target;
        $totals->waste_hoisted = $miningTotals->waste_hoisted;

        // Per-shift breakdown for the current date range (always unfiltered by shift)
        $shiftBreakdown = DailyProduction::whereBetween('date', [$filterFrom, $filterTo])
            ->selectRaw('
                COALESCE(shift, \'Unassigned\') as shift_name,
                COUNT(*)                        as records,
                SUM(gold_smelted)               as gold_smelted,
                SUM(ore_milled)                 as ore_milled,
                AVG(purity_percentage)          as avg_purity
            ')
            ->groupByRaw('COALESCE(shift, \'Unassigned\')')
            ->orderBy('shift_name')
            ->get();
        $miningByShift = MiningRecord::whereBetween('date', [$filterFrom, $filterTo])
            ->selectRaw('COALESCE(shift, \'Unassigned\') as shift_name, SUM(ore_hoisted) as ore_hoisted')
            ->groupByRaw('COALESCE(shift, \'Unassigned\')')
            ->pluck('ore_hoisted', 'shift_name');
        foreach ($shiftBreakdown as $shiftRow) {
            $shiftRow->ore_hoisted = (float) ($miningByShift[$shiftRow->shift_name] ?? 0);
        }

        // All distinct known shift names for filter chips
        $knownShifts = Shift::orderBy('name')->pluck('name');

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('production.index', compact(
            'productions', 'filterFrom', 'filterTo', 'filterShift',
            'isDefaultRange', 'totals', 'shiftBreakdown', 'knownShifts', 'miningByKey'
        ));
    }

    /* ── create / store ──────────────────────────────── */

    public function create()
    {
        $shifts      = Shift::active()->orderBy('name')->pluck('name');
        $miningSites = MiningSite::active()->orderBy('name')->pluck('name');
        $date = request('date', now()->toDateString());
        $prev = DailyProduction::where('date', '<', $date)->orderByDesc('date')->orderByDesc('id')->first();
        $mineHoistedForDate = MiningRecord::whereDate('date', $date)->sum('ore_hoisted');
        return view('production.create', compact('shifts', 'miningSites', 'prev', 'mineHoistedForDate'));
    }

    public function store(StoreDailyProductionRequest $request)
    {
        $data = $this->preparePlantData($request->validated());

        $data['uncrushed_stockpile'] = 0; // set by cascade below
        $data['unmilled_stockpile']  = 0;

        DailyProduction::create($data);
        app(StockpileService::class)->recalculateFrom($data['date']);

        AuditLog::record('production_created', "Added production record for {$data['date']}", 'DailyProduction');

        return redirect()->route('production.index')->with('success', 'Production record added.');
    }

    /* ── show ────────────────────────────────────────── */

    public function show(DailyProduction $production)
    {
        $miningRecords = MiningRecord::whereDate('date', $production->date)
            ->where('shift', $production->shift)
            ->where('mining_site', $production->mining_site)
            ->orderBy('id')
            ->get();
        return view('production.show', compact('production', 'miningRecords'));
    }

    /* ── edit / update ───────────────────────────────── */

    public function edit(DailyProduction $production)
    {
        $shifts      = Shift::active()->orderBy('name')->pluck('name');
        $miningSites = MiningSite::active()->orderBy('name')->pluck('name');
        $prev = DailyProduction::where('date', '<', $production->date)
                               ->orderByDesc('date')->orderByDesc('id')
                               ->first();
        $mineHoistedForDate = MiningRecord::whereDate('date', $production->date)->sum('ore_hoisted');
        return view('production.edit', compact('production', 'shifts', 'miningSites', 'prev', 'mineHoistedForDate'));
    }

    public function update(UpdateDailyProductionRequest $request, DailyProduction $production)
    {
        $data = $this->preparePlantData($request->validated());
        $data['uncrushed_stockpile'] = 0;
        $data['unmilled_stockpile']  = 0;

        $oldDate = $production->date->toDateString();
        $production->update($data);

        // Recascade from the earlier of old/new date so all subsequent rows stay correct
        app(StockpileService::class)->recalculateFrom(min($oldDate, $data['date']));

        AuditLog::record('production_updated', "Updated production record for {$data['date']}", 'DailyProduction', $production->id);

        return redirect()->route('production.index')->with('success', 'Production record updated.');
    }

    /* ── destroy ─────────────────────────────────────── */

    public function destroy(DailyProduction $production)
    {
        $date = $production->date->toDateString();
        $prodId = $production->id;
        $production->delete();
        app(StockpileService::class)->recalculateFrom($date);

        AuditLog::record('production_deleted', "Deleted production record for {$date}", 'DailyProduction', $prodId);

        return redirect()->route('production.index')->with('success', 'Production record deleted.');
    }

    /* ── calendar heat-map ─────────────────────────────── */

    public function calendar(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = Carbon::now()->format('Y-m');
        }

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $records = DailyProduction::whereBetween('date', [$start, $end])
            ->orderBy('date')->orderBy('id')
            ->get(['id', 'date', 'gold_smelted', 'ore_milled']);

        // Group by date — sum values per day (multiple shifts)
        $byDate = [];
        foreach ($records as $r) {
            $key = $r->date->format('Y-m-d');
            if (!isset($byDate[$key])) {
                $byDate[$key] = ['gold' => 0.0, 'ore' => 0.0, 'count' => 0, 'ids' => []];
            }
            $byDate[$key]['gold']  += (float) $r->gold_smelted;
            $byDate[$key]['ore']   += (float) $r->ore_milled;
            $byDate[$key]['count'] += 1;
            $byDate[$key]['ids'][]  = $r->id;
        }

        $maxGold    = $byDate ? max(array_column($byDate, 'gold')) : 1.0;
        if ($maxGold <= 0) $maxGold = 1.0;
        $totalGold  = array_sum(array_column($byDate, 'gold'));
        $activeDays = count($byDate);

        $bestDayKey = null;
        $bestGold   = 0.0;
        foreach ($byDate as $key => $d) {
            if ($d['gold'] > $bestGold) {
                $bestGold   = $d['gold'];
                $bestDayKey = $key;
            }
        }

        return view('production.calendar', compact(
            'byDate', 'maxGold', 'month', 'start', 'end',
            'totalGold', 'activeDays', 'bestDayKey', 'bestGold'
        ));
    }

    /* ── targets vs actuals ──────────────────────────── */

    public function targets(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = Carbon::now()->format('Y-m');
        }

        $start       = Carbon::parse($month . '-01')->startOfMonth();
        $end         = $start->copy()->endOfMonth();
        $daysInMonth = $start->daysInMonth;
        $today       = Carbon::now()->startOfDay();

        $goldTarget  = (float) (Setting::where('key', 'gold_monthly_target')->value('value') ?? 3500);
        $dailyTarget = $daysInMonth > 0 ? $goldTarget / $daysInMonth : 0;

        // All production records for the month
        $records = DailyProduction::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get(['date', 'gold_smelted']);

        // Group by date (sum multi-shift entries)
        $byDate = $records
            ->groupBy(fn($r) => $r->date->format('Y-m-d'))
            ->map(fn($rows) => round($rows->sum('gold_smelted'), 3));

        // ── Daily chart data (all calendar days) ──────────────────────────
        $dailyLabels = [];
        $dailyActual = [];
        $dailyColors = [];
        $dailyBorder = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dt  = $start->copy()->addDays($d - 1);
            $key = $dt->format('Y-m-d');
            $dailyLabels[] = $dt->format('d');

            if ($dt->gt($today)) {
                // Future day
                $dailyActual[] = null;
                $dailyColors[] = 'rgba(156,163,175,0.15)';
                $dailyBorder[] = 'rgba(156,163,175,0.25)';
            } elseif (!$byDate->has($key)) {
                // Past day with no data recorded
                $dailyActual[] = 0;
                $dailyColors[] = 'rgba(156,163,175,0.3)';
                $dailyBorder[] = 'rgba(156,163,175,0.5)';
            } else {
                $actual = (float) $byDate->get($key);
                $dailyActual[] = $actual;
                if ($actual >= $dailyTarget) {
                    $dailyColors[] = 'rgba(34,197,94,0.72)';
                    $dailyBorder[] = '#22c55e';
                } elseif ($actual >= $dailyTarget * 0.75) {
                    $dailyColors[] = 'rgba(251,191,36,0.72)';
                    $dailyBorder[] = '#fbbf24';
                } else {
                    $dailyColors[] = 'rgba(239,68,68,0.68)';
                    $dailyBorder[] = '#ef4444';
                }
            }
        }

        // ── Weekly chart data ──────────────────────────────────────────────
        $weeklyLabels  = [];
        $weeklyActual  = [];
        $weeklyTargets = [];
        $weeklyColors  = [];

        $weekStart = $start->copy();
        $weekNum   = 1;
        while ($weekStart->lte($end)) {
            $weekEnd    = $weekStart->copy()->addDays(6);
            if ($weekEnd->gt($end)) $weekEnd = $end->copy();
            $daysInWeek = $weekStart->diffInDays($weekEnd) + 1;

            $weekGold = 0.0;
            for ($day = $weekStart->copy(); $day->lte($weekEnd); $day->addDay()) {
                $weekGold += (float) ($byDate->get($day->format('Y-m-d'), 0));
            }

            $wTarget = round($dailyTarget * $daysInWeek, 2);
            $weeklyLabels[]  = 'Wk ' . $weekNum . ' (' . $weekStart->format('d') . '–' . $weekEnd->format('d M') . ')';
            $weeklyActual[]  = round($weekGold, 2);
            $weeklyTargets[] = $wTarget;
            $weeklyColors[]  = $weekGold >= $wTarget ? 'rgba(34,197,94,0.72)' : ($weekGold >= $wTarget * 0.75 ? 'rgba(251,191,36,0.72)' : 'rgba(239,68,68,0.68)');
            $weekNum++;
            $weekStart = $weekEnd->copy()->addDay();
        }

        // ── Summary stats ──────────────────────────────────────────────────
        $totalActual  = round((float) $byDate->sum(), 3);
        $daysRecorded = $byDate->count();
        $bestVal      = (float) ($byDate->max() ?? 0);
        $bestDayKey   = $bestVal > 0 ? $byDate->filter(fn($v) => (float)$v === $bestVal)->keys()->first() : null;
        $achieved     = $goldTarget > 0 ? min(100, round(($totalActual / $goldTarget) * 100, 1)) : 0;
        $remaining    = max(0, round($goldTarget - $totalActual, 2));
        $daysLeft     = (int) max(0, $today->lt($end) ? $today->diffInDays($end) : 0);
        $paceGold     = $daysRecorded > 0 ? round(($totalActual / $daysRecorded) * $daysInMonth, 2) : 0;
        $onTrack      = $paceGold >= $goldTarget;
        $daysMet      = $byDate->filter(fn($v) => (float)$v >= $dailyTarget)->count();

        // Month selector list
        $months = [];
        for ($i = -11; $i <= 1; $i++) {
            $m        = Carbon::now()->addMonths($i)->format('Y-m');
            $months[] = ['value' => $m, 'label' => Carbon::parse($m . '-01')->format('M Y')];
        }

        return view('production.targets', compact(
            'month', 'start', 'end', 'daysInMonth',
            'goldTarget', 'dailyTarget',
            'dailyLabels', 'dailyActual', 'dailyColors', 'dailyBorder',
            'weeklyLabels', 'weeklyActual', 'weeklyTargets', 'weeklyColors',
            'totalActual', 'daysRecorded', 'bestVal', 'bestDayKey',
            'achieved', 'remaining', 'daysLeft', 'paceGold', 'onTrack', 'daysMet',
            'months'
        ));
    }

    private function preparePlantData(array $data): array
    {
        $manualSanda = filter_var($data['sanda_milled_manual'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['sanda_milled_manual'] = $manualSanda;
        if (!$manualSanda) {
            $data['sanda_milled'] = round((float) $data['ore_milled'] - (float) $data['ro_mine_milled'], 2);
        }

        // Legacy columns remain for compatibility; underground values now live in mining_records.
        $data['ore_hoisted'] = 0;
        $data['ore_hoisted_target'] = null;
        $data['waste_hoisted'] = 0;

        return $data;
    }
}
