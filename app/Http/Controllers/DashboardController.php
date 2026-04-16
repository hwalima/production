<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\MachineRuntime;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to'   => 'nullable|date_format:Y-m-d',
        ]);

        $now = Carbon::now();

        // ── Date range (defaults to current month) ─────────────────────────
        $filterFrom = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : $now->copy()->startOfMonth();
        $filterTo   = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : $now->copy()->endOfMonth();

        // Clamp: from cannot be after to
        if ($filterFrom->gt($filterTo)) {
            $filterFrom = $filterTo->copy()->startOfMonth();
        }

        $filterFromStr = $filterFrom->toDateString();
        $filterToStr   = $filterTo->toDateString();

        // ── Production aggregates for selected range ───────────────────────
        $rangeRows = DailyProduction::whereBetween('date', [$filterFromStr, $filterToStr])->get();

        $oreHoistedMonth   = $rangeRows->sum('ore_hoisted');
        $wasteHoistedMonth = $rangeRows->sum('waste_hoisted');
        $oreMilledMonth    = $rangeRows->sum('ore_milled');
        $goldSmeltedMonth  = $rangeRows->sum('gold_smelted');
        $avgPurity         = $rangeRows->avg('purity_percentage') ?? 0;
        $daysRecorded      = $rangeRows->count();

        // Computed metrics
        $strippingRatio = $oreHoistedMonth > 0
            ? round($wasteHoistedMonth / $oreHoistedMonth, 2)
            : 0;

        $impliedGrade = $oreMilledMonth > 0
            ? round($goldSmeltedMonth / $oreMilledMonth, 3)
            : 0;

        $millingEfficiency = $oreHoistedMonth > 0
            ? round(($oreMilledMonth / $oreHoistedMonth) * 100, 1)
            : 0;

        $dashSettings  = Setting::whereIn('key', ['gold_monthly_target','company_location','mine_latitude','mine_longitude'])
                            ->pluck('value', 'key');
        $goldTarget    = (float) ($dashSettings->get('gold_monthly_target') ?? 3500);
        $goldTargetPct = $goldTarget > 0 ? min(100, round(($goldSmeltedMonth / $goldTarget) * 100, 1)) : 0;

        $avgDailyGold   = $daysRecorded > 0 ? $goldSmeltedMonth / $daysRecorded : 0;

        // Projection: always based on current month pace
        $daysInMonth   = (int) $now->daysInMonth;
        $dayOfMonth    = (int) $now->day;
        $goldProjected = $dayOfMonth > 0
            ? round(($goldSmeltedMonth / max(1, $daysRecorded)) * $daysInMonth, 3)
            : 0;

        // ── Machines status ───────────────────────────────────────────────
        $allMachines     = MachineRuntime::all();
        $machinesTotal   = $allMachines->count();
        $machinesOverdue = $allMachines->filter(fn($m) =>
            $m->next_service_date && Carbon::parse($m->next_service_date)->lt($now)
        )->count();
        $machinesDueSoon = $allMachines->filter(fn($m) =>
            $m->next_service_date &&
            !Carbon::parse($m->next_service_date)->lt($now) &&
            Carbon::parse($m->next_service_date)->diffInDays($now) <= 7
        )->count();

        // ── Production trend for selected range ───────────────────────────
        $trend = DailyProduction::whereBetween('date', [$filterFromStr, $filterToStr])
            ->orderBy('date')
            ->get(['date', 'ore_hoisted', 'waste_hoisted', 'ore_crushed', 'ore_milled', 'gold_smelted']);

        $trendLabels       = $trend->pluck('date')->map(fn($d) => $d->format('M d'))->toArray();
        $trendOreHoisted   = $trend->pluck('ore_hoisted')->map(fn($v) => (float) $v)->toArray();
        $trendWasteHoisted = $trend->pluck('waste_hoisted')->map(fn($v) => (float) $v)->toArray();
        $trendOreCrushed   = $trend->pluck('ore_crushed')->map(fn($v) => (float) $v)->toArray();
        $trendOreMilled    = $trend->pluck('ore_milled')->map(fn($v) => (float) $v)->toArray();
        $trendGoldSmelted  = $trend->pluck('gold_smelted')->map(fn($v) => (float) $v)->toArray();

        $mineLocation  = $dashSettings->get('company_location') ?? 'Filabusi, Zimbabwe';
        $mineLat       = (float) ($dashSettings->get('mine_latitude') ?: -20.52);
        $mineLon       = (float) ($dashSettings->get('mine_longitude') ?: 29.33);

        // ── Quick-access preset ranges ────────────────────────────────────
        $isDefaultRange = $filterFromStr === $now->copy()->startOfMonth()->toDateString()
                       && $filterToStr   === $now->copy()->endOfMonth()->toDateString();

        return view('dashboard', compact(
            'oreHoistedMonth', 'wasteHoistedMonth', 'oreMilledMonth',
            'goldSmeltedMonth', 'avgPurity',
            'daysRecorded', 'daysInMonth', 'dayOfMonth',
            'strippingRatio', 'impliedGrade', 'millingEfficiency',
            'goldTarget', 'goldTargetPct', 'goldProjected',
            'avgDailyGold',
            'machinesTotal', 'machinesOverdue', 'machinesDueSoon',
            'trendLabels', 'trendOreHoisted', 'trendWasteHoisted',
            'trendOreCrushed', 'trendOreMilled', 'trendGoldSmelted',
            'mineLocation', 'mineLat', 'mineLon',
            'filterFromStr', 'filterToStr', 'isDefaultRange'
        ));
    }
}
