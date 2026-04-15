<?php
namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\BlastingRecord;
use App\Models\Chemical;
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

        $totalOre    = $productions->sum('ore_milled');
        $totalGold   = $productions->sum('gold_smelted');
        $totalProfit = $productions->sum('profit_calculated');
        $avgPurity   = $productions->avg('purity_percentage');

        return view('reports.production', compact(
            'productions', 'month', 'totalOre', 'totalGold', 'totalProfit', 'avgPurity'
        ));
    }

    public function consumables(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $blasting  = BlastingRecord::whereBetween('date', [$start, $end])->get();
        $chemicals = Chemical::whereBetween('date', [$start, $end])->get();

        return view('reports.consumables', compact('blasting', 'chemicals', 'month'));
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

        $totalOre    = $productions->sum('ore_milled');
        $totalGold   = $productions->sum('gold_smelted');
        $totalProfit = $productions->sum('profit_calculated');
        $avgPurity   = $productions->avg('purity_percentage');

        $data = array_merge($this->pdfSettings(), compact(
            'productions', 'month', 'totalOre', 'totalGold', 'totalProfit', 'avgPurity'
        ));

        $filename = 'production-report-' . $month . '.pdf';

        return Pdf::loadView('pdf.production', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    public function consumablesPdf(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $blasting  = BlastingRecord::whereBetween('date', [$start, $end])->get();
        $chemicals = Chemical::whereBetween('date', [$start, $end])->get();

        $data = array_merge($this->pdfSettings(), compact('blasting', 'chemicals', 'month'));

        $filename = 'consumables-report-' . $month . '.pdf';

        return Pdf::loadView('pdf.consumables', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
