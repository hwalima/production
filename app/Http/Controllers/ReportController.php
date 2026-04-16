<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\Consumable;
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

        $totalOre  = $productions->sum('ore_milled');
        $totalGold = $productions->sum('gold_smelted');
        $avgPurity = $productions->avg('purity_percentage');

        return view('reports.production', compact(
            'productions', 'month', 'totalOre', 'totalGold', 'avgPurity'
        ));
    }

    public function consumables(Request $request)
    {
        [$consumables, $totalValue, $lowStockCount] = $this->fetchStoresSnapshot();
        return view('reports.consumables', compact('consumables', 'totalValue', 'lowStockCount'));
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

        $totalOre  = $productions->sum('ore_milled');
        $totalGold = $productions->sum('gold_smelted');
        $avgPurity = $productions->avg('purity_percentage');

        $filterFrom = $start->format('d M Y');
        $filterTo   = $end->format('d M Y');

        $data = array_merge($this->pdfSettings(), compact(
            'productions', 'month', 'totalOre', 'totalGold', 'avgPurity',
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
