<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\MiningSite;
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

        $productions = DailyProduction::whereBetween('date', [$filterFrom, $filterTo])
            ->orderByDesc('date')->paginate(30)->withQueryString();

        $totals = DailyProduction::whereBetween('date', [$filterFrom, $filterTo])
            ->selectRaw('
                SUM(ore_hoisted)        as ore_hoisted,
                SUM(ore_hoisted_target) as ore_hoisted_target,
                SUM(waste_hoisted)      as waste_hoisted,
                SUM(ore_crushed)        as ore_crushed,
                SUM(ore_milled)         as ore_milled,
                SUM(ore_milled_target)  as ore_milled_target,
                SUM(gold_smelted)       as gold_smelted,
                AVG(purity_percentage)  as avg_purity
            ')->first();

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('production.index', compact('productions', 'filterFrom', 'filterTo', 'isDefaultRange', 'totals'));
    }

    /* ── create / store ──────────────────────────────── */

    public function create()
    {
        $shifts      = Shift::active()->orderBy('name')->pluck('name');
        $miningSites = MiningSite::active()->orderBy('name')->pluck('name');
        $prev        = DailyProduction::orderByDesc('date')->first();
        return view('production.create', compact('shifts', 'miningSites', 'prev'));
    }

    public function store(StoreDailyProductionRequest $request)
    {
        $data = $request->validated();

        $data['uncrushed_stockpile'] = 0; // set by cascade below
        $data['unmilled_stockpile']  = 0;

        DailyProduction::create($data);
        $this->recalculateFrom($data['date']);

        AuditLog::record('production_created', "Added production record for {$data['date']}", 'DailyProduction');

        return redirect()->route('production.index')->with('success', 'Production record added.');
    }

    /* ── show ────────────────────────────────────────── */

    public function show(DailyProduction $production)
    {
        return view('production.show', compact('production'));
    }

    /* ── edit / update ───────────────────────────────── */

    public function edit(DailyProduction $production)
    {
        $shifts      = Shift::active()->orderBy('name')->pluck('name');
        $miningSites = MiningSite::active()->orderBy('name')->pluck('name');
        $prev = DailyProduction::where('date', '<', $production->date)
                               ->orderByDesc('date')->orderByDesc('id')
                               ->first();
        return view('production.edit', compact('production', 'shifts', 'miningSites', 'prev'));
    }

    public function update(UpdateDailyProductionRequest $request, DailyProduction $production)
    {
        $data = $request->validated();
        $data['uncrushed_stockpile'] = 0;
        $data['unmilled_stockpile']  = 0;

        $oldDate = $production->date->toDateString();
        $production->update($data);

        // Recascade from the earlier of old/new date so all subsequent rows stay correct
        $this->recalculateFrom(min($oldDate, $data['date']));

        AuditLog::record('production_updated', "Updated production record for {$data['date']}", 'DailyProduction', $production->id);

        return redirect()->route('production.index')->with('success', 'Production record updated.');
    }

    /* ── destroy ─────────────────────────────────────── */

    public function destroy(DailyProduction $production)
    {
        $date = $production->date->toDateString();
        $prodId = $production->id;
        $production->delete();
        $this->recalculateFrom($date);

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

    /* ── recalculate cumulative stockpiles from a date ── */

    /**
     * Re-computes uncrushed_stockpile and unmilled_stockpile for every record
     * on or after $fromDate, in chronological (date ASC, id ASC) order.
     * Each row's stockpile = previous row's stockpile + current row's in/out.
     */
    private function recalculateFrom(string $fromDate): void
    {
        // Seed from the record immediately before $fromDate
        $seed = DailyProduction::where('date', '<', $fromDate)
                               ->orderByDesc('date')->orderByDesc('id')
                               ->first();

        $prevUncrushed = $seed ? (float) $seed->uncrushed_stockpile : 0.0;
        $prevUnmilled  = $seed ? (float) $seed->unmilled_stockpile  : 0.0;

        $records = DailyProduction::where('date', '>=', $fromDate)
                                  ->orderBy('date')->orderBy('id')
                                  ->get();

        foreach ($records as $record) {
            $uncrushed = $prevUncrushed + (float) $record->ore_hoisted - (float) $record->ore_crushed;
            $unmilled  = $prevUnmilled  + (float) $record->ore_crushed - (float) $record->ore_milled;

            $record->updateQuietly([
                'uncrushed_stockpile' => $uncrushed,
                'unmilled_stockpile'  => $unmilled,
            ]);

            $prevUncrushed = $uncrushed;
            $prevUnmilled  = $unmilled;
        }
    }
}

