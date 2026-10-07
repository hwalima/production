<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyProduction;
use App\Models\MiningRecord;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiProductionController extends Controller
{
    private function dateRange(Request $request): array
    {
        $now  = Carbon::now();
        $from = $request->filled('from') ? $request->input('from') : $now->startOfMonth()->toDateString();
        $to   = $request->filled('to')   ? $request->input('to')   : $now->endOfMonth()->toDateString();
        if ($from > $to) {
            $from = Carbon::now()->startOfMonth()->toDateString();
        }
        return [$from, $to];
    }

    /**
     * GET /api/v1/production
     * Paginated list of daily production records.
     *
     * Query params: from, to, shift, per_page
     */
    public function index(Request $request)
    {
        $request->validate([
            'from'     => 'nullable|date_format:Y-m-d',
            'to'       => 'nullable|date_format:Y-m-d',
            'shift'    => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        [$from, $to] = $this->dateRange($request);
        $perPage     = min((int) $request->input('per_page', 30), 100);

        $query = DailyProduction::whereBetween('date', [$from, $to]);

        if ($request->filled('shift')) {
            $query->where('shift', $request->input('shift'));
        }

        $miningByKey = MiningRecord::whereBetween('date', [$from, $to])
            ->get()
            ->groupBy(fn($record) => implode('|', [
                $record->date->toDateString(),
                $record->shift ?? '',
                $record->mining_site ?? '',
            ]))
            ->map(fn($records) => [
                'ore_hoisted_adjusted_t' => round((float) $records->sum('ore_hoisted'), 2),
                'ore_hoisted_target_t' => round((float) $records->sum('ore_hoisted_target'), 2),
                'waste_hoisted_t' => round((float) $records->sum('waste_hoisted'), 2),
            ]);

        $records = $query->orderByDesc('date')->orderByDesc('id')->paginate($perPage);
        $records->getCollection()->transform(function (DailyProduction $production) use ($miningByKey) {
            $key = implode('|', [
                $production->date->toDateString(),
                $production->shift ?? '',
                $production->mining_site ?? '',
            ]);
            $mining = $miningByKey->get($key, [
                'ore_hoisted_adjusted_t' => 0,
                'ore_hoisted_target_t' => null,
                'waste_hoisted_t' => 0,
            ]);
            $production->setAttribute('ore_hoisted', $mining['ore_hoisted_adjusted_t']);
            $production->setAttribute('ore_hoisted_target', $mining['ore_hoisted_target_t']);
            $production->setAttribute('waste_hoisted', $mining['waste_hoisted_t']);
            $production->setAttribute('mining', $mining);
            return $production;
        });
        return response()->json($records);
    }

    /**
     * GET /api/v1/production/summary
     * Aggregated totals + shift breakdown for a date range.
     *
     * Query params: from, to, shift
     */
    public function summary(Request $request)
    {
        $request->validate([
            'from'  => 'nullable|date_format:Y-m-d',
            'to'    => 'nullable|date_format:Y-m-d',
            'shift' => 'nullable|string|max:50',
        ]);

        [$from, $to] = $this->dateRange($request);

        $base = DailyProduction::whereBetween('date', [$from, $to]);
        $miningBase = MiningRecord::whereBetween('date', [$from, $to]);
        if ($request->filled('shift')) {
            $base->where('shift', $request->input('shift'));
            $miningBase->where('shift', $request->input('shift'));
        }

        $totals = (clone $base)->selectRaw('
            COUNT(*)                        as records,
            SUM(ore_crushed)                as ore_crushed_t,
            SUM(ore_milled)                 as ore_milled_t,
            SUM(ro_mine_milled)             as ro_mine_milled_t,
            SUM(sanda_milled)               as sanda_milled_t,
            SUM(ore_milled_target)          as ore_milled_target_t,
            SUM(gold_smelted)               as gold_smelted_g,
            AVG(purity_percentage)          as avg_purity_pct
        ')->first();
        $miningTotals = (clone $miningBase)->selectRaw('
            SUM(ore_hoisted) as ore_hoisted_t,
            SUM(ore_hoisted_target) as ore_hoisted_target_t,
            SUM(waste_hoisted) as waste_hoisted_t
        ')->first();
        $totals->ore_hoisted_t = $miningTotals->ore_hoisted_t;
        $totals->ore_hoisted_target_t = $miningTotals->ore_hoisted_target_t;
        $totals->waste_hoisted_t = $miningTotals->waste_hoisted_t;

        $shiftBreakdown = DailyProduction::whereBetween('date', [$from, $to])
            ->selectRaw("COALESCE(shift,'Unassigned') as shift,
                         COUNT(*) as records,
                         SUM(gold_smelted) as gold_g,
                         SUM(ore_milled) as ore_milled_t,
                         AVG(purity_percentage) as avg_purity_pct")
            ->groupByRaw("COALESCE(shift,'Unassigned')")
            ->orderBy('shift')
            ->get();

        $goldTarget = (float) (Setting::where('key', 'gold_monthly_target')->value('value') ?? 3500);

        return response()->json([
            'data' => [
                'from'            => $from,
                'to'              => $to,
                'gold_target_g'   => $goldTarget,
                'totals'          => $totals,
                'shift_breakdown' => $shiftBreakdown,
            ],
        ]);
    }
}
