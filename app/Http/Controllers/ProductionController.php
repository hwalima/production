<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\Shift;
use App\Models\MiningSite;
use App\Http\Requests\StoreDailyProductionRequest;
use App\Http\Requests\UpdateDailyProductionRequest;
use Illuminate\Http\Request;

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

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('production.index', compact('productions', 'filterFrom', 'filterTo', 'isDefaultRange'));
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

