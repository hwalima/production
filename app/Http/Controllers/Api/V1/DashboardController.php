<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActionItem;
use App\Models\Consumable;
use App\Models\DailyProduction;
use App\Models\Machine;
use App\Models\MiningRecord;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard
     * Returns key operational KPIs for the current day and month.
     */
    public function index(Request $request)
    {
        $now   = Carbon::now();
        $today = $now->toDateString();
        $start = $now->copy()->startOfMonth()->toDateString();
        $end   = $now->copy()->endOfMonth()->toDateString();

        // Gold totals
        $goldToday  = (float) DailyProduction::where('date', $today)->sum('gold_smelted');
        $goldMtd    = (float) DailyProduction::whereBetween('date', [$start, $end])->sum('gold_smelted');
        $oreMtd     = (float) DailyProduction::whereBetween('date', [$start, $end])->sum('ore_milled');
        $miningToday = MiningRecord::whereDate('date', $today)->selectRaw('
            SUM(ore_hoisted) as ore_hoisted_t,
            SUM(waste_hoisted) as waste_hoisted_t
        ')->first();
        $miningMtd = MiningRecord::whereBetween('date', [$start, $end])->selectRaw('
            SUM(ore_hoisted) as ore_hoisted_t,
            SUM(waste_hoisted) as waste_hoisted_t
        ')->first();
        $goldTarget = (float) (Setting::where('key', 'gold_monthly_target')->value('value') ?? 3500);
        $goldMtdPct = $goldTarget > 0 ? round(($goldMtd / $goldTarget) * 100, 1) : null;

        // Overdue action items (any date range — system-wide)
        $overdueActionItems = ActionItem::overdueCount();

        // Low-stock consumables
        $lowStockCount = Consumable::where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->withSum(['movements as stock_in'  => fn($q) => $q->where('direction', 'in')],  'quantity')
            ->withSum(['movements as stock_out' => fn($q) => $q->where('direction', 'out')], 'quantity')
            ->get()
            ->filter(fn($c) => ((float)($c->stock_in ?? 0) - (float)($c->stock_out ?? 0)) <= (float)$c->reorder_level)
            ->count();

        // Machines overdue for service
        $machinesOverdue = Machine::where('is_active', true)
            ->with(['latestRuntime', 'latestService'])
            ->get()
            ->filter(fn(Machine $machine) => $machine->latestRuntime && $machine->isServiceDue())
            ->count();

        // MTD shift breakdown
        $shiftBreakdown = DailyProduction::whereBetween('date', [$start, $end])
            ->selectRaw("COALESCE(shift,'Unassigned') as shift, COUNT(*) as records,
                         SUM(gold_smelted) as gold_g, SUM(ore_milled) as ore_milled_t,
                         SUM(ro_mine_milled) as ro_mine_milled_t, SUM(sanda_milled) as sanda_milled_t,
                         AVG(purity_percentage) as avg_purity_pct")
            ->groupByRaw("COALESCE(shift,'Unassigned')")
            ->orderBy('shift')
            ->get();
        $miningByShift = MiningRecord::whereBetween('date', [$start, $end])
            ->selectRaw("COALESCE(shift,'Unassigned') as shift,
                         SUM(ore_hoisted) as ore_hoisted_t,
                         SUM(waste_hoisted) as waste_hoisted_t")
            ->groupByRaw("COALESCE(shift,'Unassigned')")
            ->get()
            ->keyBy('shift');
        foreach ($miningByShift as $shiftName => $mining) {
            $shift = $shiftBreakdown->firstWhere('shift', $shiftName);
            if (!$shift) {
                $shift = (object) [
                    'shift' => $shiftName,
                    'records' => 0,
                    'gold_g' => 0,
                    'ore_milled_t' => 0,
                    'ro_mine_milled_t' => 0,
                    'sanda_milled_t' => 0,
                    'avg_purity_pct' => null,
                ];
                $shiftBreakdown->push($shift);
            }
            $shift->ore_hoisted_t = (float) ($mining->ore_hoisted_t ?? 0);
            $shift->waste_hoisted_t = (float) ($mining->waste_hoisted_t ?? 0);
        }
        $shiftBreakdown = $shiftBreakdown->sortBy('shift')->values();

        // Recent 7 production records
        $recentProduction = DailyProduction::orderByDesc('date')->orderByDesc('id')
            ->limit(7)
            ->get(['id', 'date', 'shift', 'mining_site', 'gold_smelted', 'ore_milled', 'ro_mine_milled', 'sanda_milled', 'purity_percentage']);

        return response()->json([
            'month'                    => $now->format('Y-m'),
            'gold_today_g'             => round($goldToday, 2),
            'gold_mtd_g'               => round($goldMtd, 2),
            'gold_target_g'            => $goldTarget,
            'gold_mtd_pct'             => $goldMtdPct,
            'ore_milled_mtd_t'         => round($oreMtd, 2),
            'ore_hoisted_today_t'      => round((float) ($miningToday->ore_hoisted_t ?? 0), 2),
            'ore_hoisted_mtd_t'        => round((float) ($miningMtd->ore_hoisted_t ?? 0), 2),
            'waste_hoisted_mtd_t'      => round((float) ($miningMtd->waste_hoisted_t ?? 0), 2),
            'overdue_action_items'     => $overdueActionItems,
            'low_stock_consumables'    => $lowStockCount,
            'machines_overdue_service' => $machinesOverdue,
            'shift_breakdown'          => $shiftBreakdown,
            'recent_production'        => $recentProduction,
        ]);
    }
}
