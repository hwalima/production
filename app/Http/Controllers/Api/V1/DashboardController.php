<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActionItem;
use App\Models\Consumable;
use App\Models\DailyProduction;
use App\Models\MachineRuntime;
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
        $machinesOverdue = MachineRuntime::whereNotNull('next_service_date')
            ->where('next_service_date', '<', $today)
            ->count();

        // MTD shift breakdown
        $shiftBreakdown = DailyProduction::whereBetween('date', [$start, $end])
            ->selectRaw("COALESCE(shift,'Unassigned') as shift, COUNT(*) as records,
                         SUM(gold_smelted) as gold_g, SUM(ore_milled) as ore_milled_t,
                         AVG(purity_percentage) as avg_purity_pct")
            ->groupByRaw("COALESCE(shift,'Unassigned')")
            ->orderBy('shift')
            ->get();

        // Recent 7 production records
        $recentProduction = DailyProduction::orderByDesc('date')->orderByDesc('id')
            ->limit(7)
            ->get(['id', 'date', 'shift', 'mining_site', 'gold_smelted', 'ore_milled', 'purity_percentage']);

        return response()->json([
            'month'                    => $now->format('Y-m'),
            'gold_today_g'             => round($goldToday, 2),
            'gold_mtd_g'               => round($goldMtd, 2),
            'gold_target_g'            => $goldTarget,
            'gold_mtd_pct'             => $goldMtdPct,
            'ore_milled_mtd_t'         => round($oreMtd, 2),
            'overdue_action_items'     => $overdueActionItems,
            'low_stock_consumables'    => $lowStockCount,
            'machines_overdue_service' => $machinesOverdue,
            'shift_breakdown'          => $shiftBreakdown,
            'recent_production'        => $recentProduction,
        ]);
    }
}
