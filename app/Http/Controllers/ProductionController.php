<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
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

        return redirect()->route('production.index')->with('success', 'Production record updated.');
    }

    /* ── destroy ─────────────────────────────────────── */

    public function destroy(DailyProduction $production)
    {
        $date = $production->date->toDateString();
        $production->delete();
        $this->recalculateFrom($date);

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

