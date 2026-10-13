<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\MiningRecord;
use App\Models\Consumable;
use App\Models\ConsumableStockMovement;
use App\Models\LabourEnergy;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function production(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $productions = DailyProduction::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();
        [$miningByKey, $totHoisted, $totWaste, $totHoistTgt] = $this->miningSummary($start, $end);
        $totHoistVar = $totHoistTgt > 0 ? round($totHoistTgt - $totHoisted, 2) : null;

        $totalOre  = $productions->sum('ore_milled');
        $totalGold = $productions->sum('gold_smelted');
        $avgPurity = $productions->avg('purity_percentage');
        $totMillTgt = $productions->whereNotNull('ore_milled_target')->sum('ore_milled_target');
        $totMillVar = $productions->whereNotNull('ore_milled_target')->count()
            ? round($totMillTgt - $totalOre, 2) : null;

        return view('reports.production', compact(
            'productions', 'month', 'totalOre', 'totalGold', 'avgPurity',
            'miningByKey', 'totHoisted', 'totWaste', 'totHoistTgt', 'totHoistVar',
            'totMillTgt', 'totMillVar'
        ));
    }

    public function consumables(Request $request)
    {
        [$consumables, $totalValue, $lowStockCount] = $this->fetchStoresSnapshot();
        return view('reports.consumables', compact('consumables', 'totalValue', 'lowStockCount'));
    }

    public function accounts(Request $request)
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        // ── Gold Revenue ──────────────────────────────────────────
        $productions = DailyProduction::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get()
            ->map(function ($p) {
                $p->gold_revenue = round(
                    (float)$p->gold_smelted * ((float)$p->purity_percentage / 100) * (float)$p->fidelity_price,
                    2
                );
                return $p;
            });

        $totalGoldRevenue = $productions->sum('gold_revenue');
        $totalGoldGrams   = $productions->sum('gold_smelted');

        // ── Consumables Cost (actual usage issues) ────────────────
        $consumableIssues = ConsumableStockMovement::with('consumable:id,name,category,use_unit')
            ->whereBetween('movement_date', [$from->toDateString(), $to->toDateString()])
            ->where('direction', 'out')
            ->where('type', 'usage')
            ->orderBy('movement_date')
            ->get()
            ->map(function (ConsumableStockMovement $movement) {
                $movement->category = $movement->consumable?->category ?? 'uncategorised';
                return $movement;
            });

        $totalConsumablesCost = $consumableIssues->sum('total_cost');

        // ── Operating costs ───────────────────────────────────────
        $labourBreakdown = LabourEnergy::whereBetween('date', [$from, $to])
            ->selectRaw(
                'SUM(zesa_cost) as zesa, SUM(diesel_cost) as diesel, SUM(labour_cost) as labour, '
                . 'SUM(mining_cost) as mining, SUM(milling_cost) as milling, '
                . 'SUM(maintenance_cost) as maintenance, SUM(transport_cost) as transport'
            )
            ->first();

        $totalPowerCost = (float) ($labourBreakdown->zesa ?? 0)
                        + (float) ($labourBreakdown->diesel ?? 0);
        $totalLabourCost = (float) ($labourBreakdown->labour ?? 0);
        $totalMiningCost = (float) ($labourBreakdown->mining ?? 0);
        $totalMillingCost = (float) ($labourBreakdown->milling ?? 0);
        $totalMaintenanceCost = (float) ($labourBreakdown->maintenance ?? 0);
        $totalTransportCost = (float) ($labourBreakdown->transport ?? 0);

        // ── Totals ────────────────────────────────────────────────
        $totalCosts   = $totalMiningCost + $totalMillingCost + $totalLabourCost
                      + $totalPowerCost + $totalConsumablesCost
                      + $totalMaintenanceCost + $totalTransportCost;
        $profitLoss   = $totalGoldRevenue - $totalCosts;

        return view('reports.accounts', compact(
            'from', 'to',
            'productions', 'totalGoldRevenue', 'totalGoldGrams',
            'consumableIssues', 'totalConsumablesCost',
            'labourBreakdown', 'totalMiningCost', 'totalMillingCost', 'totalLabourCost',
            'totalPowerCost', 'totalMaintenanceCost', 'totalTransportCost',
            'totalCosts', 'profitLoss'
        ));
    }

    // ── PDF helpers ────────────────────────────────────────────────

    private function pdfSettings(): array
    {
        $settings   = Setting::all()->pluck('value', 'key');
        $logoPath   = $settings['logo_path'] ?? null;
        $logoBase64 = null;

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $absPath    = storage_path('app/public/' . $logoPath);
            $mime       = mime_content_type($absPath);
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(
                Storage::disk('public')->get($logoPath)
            );
        }

        return [
            'logoBase64'      => $logoBase64,
            'companyName'     => $settings['company_name']     ?? config('app.name'),
            'companyLocation' => $settings['company_location'] ?? ($settings['company_address'] ?? ''),
            'companyPhone'    => $settings['company_phone']    ?? '',
            'companyEmail'    => $settings['company_email']    ?? '',
        ];
    }

    public function productionPdf(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $productions = DailyProduction::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $totalOre      = $productions->sum('ore_milled');
        $totalGold     = $productions->sum('gold_smelted');
        $avgPurity     = $productions->avg('purity_percentage') ?? 0;

        [$miningByKey, $totHoisted, $totWaste, $totHoistTgt] = $this->miningSummary($start, $end);
        $totCrushed    = $productions->sum('ore_crushed');
        $totMillTgt    = $productions->whereNotNull('ore_milled_target')->sum('ore_milled_target');
        $totHoistVar   = $totHoistTgt > 0 ? round($totHoistTgt - $totHoisted, 2) : null;
        $totMillVar    = $productions->whereNotNull('ore_milled_target')->count()
                            ? round($totMillTgt - $totalOre, 2) : null;

        $filterFrom    = $start->format('d M Y');
        $filterTo      = $end->format('d M Y');

        $data = array_merge($this->pdfSettings(), compact(
            'productions', 'month', 'totalOre', 'totalGold', 'avgPurity',
            'totHoisted', 'totWaste', 'totCrushed',
            'totHoistTgt', 'totHoistVar', 'totMillTgt', 'totMillVar',
            'filterFrom', 'filterTo'
        ));

        $filename = 'production-report-' . $month . '.pdf';

        return Pdf::loadView('pdf.production', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    public function consumablesPdf(Request $request)
    {
        [$consumables, $totalValue, $lowStockCount] = $this->fetchStoresSnapshot();

        $data = array_merge($this->pdfSettings(), compact('consumables', 'totalValue', 'lowStockCount'));

        $filename = 'stores-inventory-' . now()->format('Y-m-d') . '.pdf';

        return Pdf::loadView('pdf.consumables', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    public function accountsPdf(Request $request)
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $productions = DailyProduction::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get()
            ->map(function ($p) {
                $p->gold_revenue = round(
                    (float)$p->gold_smelted * ((float)$p->purity_percentage / 100) * (float)$p->fidelity_price,
                    2
                );
                return $p;
            });

        $totalGoldRevenue = $productions->sum('gold_revenue');
        $totalGoldGrams   = $productions->sum('gold_smelted');

        $consumableIssues = ConsumableStockMovement::with('consumable:id,name,category,use_unit')
            ->whereBetween('movement_date', [$from->toDateString(), $to->toDateString()])
            ->where('direction', 'out')
            ->where('type', 'usage')
            ->orderBy('movement_date')
            ->get()
            ->map(function (ConsumableStockMovement $movement) {
                $movement->category = $movement->consumable?->category ?? 'uncategorised';
                return $movement;
            });

        $totalConsumablesCost = $consumableIssues->sum('total_cost');

        $labourBreakdown = LabourEnergy::whereBetween('date', [$from, $to])
            ->selectRaw(
                'SUM(zesa_cost) as zesa, SUM(diesel_cost) as diesel, SUM(labour_cost) as labour, '
                . 'SUM(mining_cost) as mining, SUM(milling_cost) as milling, '
                . 'SUM(maintenance_cost) as maintenance, SUM(transport_cost) as transport'
            )
            ->first();

        $totalPowerCost = (float) ($labourBreakdown->zesa ?? 0)
                        + (float) ($labourBreakdown->diesel ?? 0);
        $totalLabourCost = (float) ($labourBreakdown->labour ?? 0);
        $totalMiningCost = (float) ($labourBreakdown->mining ?? 0);
        $totalMillingCost = (float) ($labourBreakdown->milling ?? 0);
        $totalMaintenanceCost = (float) ($labourBreakdown->maintenance ?? 0);
        $totalTransportCost = (float) ($labourBreakdown->transport ?? 0);

        $totalCosts = $totalMiningCost + $totalMillingCost + $totalLabourCost
                    + $totalPowerCost + $totalConsumablesCost
                    + $totalMaintenanceCost + $totalTransportCost;
        $profitLoss = $totalGoldRevenue - $totalCosts;
        $isProfitable = $profitLoss >= 0;

        $filterFrom = $from->format('d M Y');
        $filterTo   = $to->format('d M Y');

        $data = array_merge($this->pdfSettings(), compact(
            'from', 'to', 'filterFrom', 'filterTo',
            'productions', 'totalGoldRevenue', 'totalGoldGrams',
            'consumableIssues', 'totalConsumablesCost',
            'labourBreakdown', 'totalMiningCost', 'totalMillingCost', 'totalLabourCost',
            'totalPowerCost', 'totalMaintenanceCost', 'totalTransportCost',
            'totalCosts', 'profitLoss', 'isProfitable'
        ));

        $filename = 'accounts-report-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.pdf';

        return Pdf::loadView('pdf.accounts', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    private function miningSummary($from, $to): array
    {
        $groups = MiningRecord::whereBetween('date', [$from, $to])
            ->selectRaw('date, shift, mining_site, SUM(ore_hoisted) as ore_hoisted, SUM(ore_hoisted_target) as ore_hoisted_target, SUM(waste_hoisted) as waste_hoisted')
            ->groupBy('date', 'shift', 'mining_site')
            ->get();

        $byKey = [];
        foreach ($groups as $group) {
            $key = implode('|', [substr((string) $group->date, 0, 10), $group->shift ?? '', $group->mining_site ?? '']);
            $byKey[$key] = $group;
        }

        $totals = MiningRecord::whereBetween('date', [$from, $to])
            ->selectRaw('SUM(ore_hoisted) as ore_hoisted, SUM(ore_hoisted_target) as ore_hoisted_target, SUM(waste_hoisted) as waste_hoisted')
            ->first();

        return [
            $byKey,
            (float) ($totals->ore_hoisted ?? 0),
            (float) ($totals->waste_hoisted ?? 0),
            (float) ($totals->ore_hoisted_target ?? 0),
        ];
    }

    // ── Shared stores snapshot ─────────────────────────────────────
    private function fetchStoresSnapshot(): array
    {
        $consumables = Consumable::withSum(
                ['movements as stock_in_qty'  => fn($q) => $q->where('direction', 'in')],  'quantity')
            ->withSum(
                ['movements as stock_out_qty' => fn($q) => $q->where('direction', 'out')], 'quantity')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $c->current_stock = (float)($c->stock_in_qty ?? 0) - (float)($c->stock_out_qty ?? 0);
                $c->unit_cost     = (float)$c->units_per_pack > 0
                    ? (float)$c->pack_cost / (float)$c->units_per_pack : 0;
                $c->low_stock     = (float)$c->reorder_level > 0
                    && $c->current_stock <= (float)$c->reorder_level;
                $c->out_of_stock  = $c->current_stock <= 0;
                $c->stock_value   = max(0, $c->current_stock) * $c->unit_cost;
                return $c;
            });

        $totalValue    = $consumables->sum('stock_value');
        $lowStockCount = $consumables->where('low_stock', true)->count();

        return [$consumables, $totalValue, $lowStockCount];
    }
}
