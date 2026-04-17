<?php
namespace App\Http\Controllers;

use App\Models\AssayResult;
use App\Models\DailyProduction;
use App\Http\Requests\StoreAssayResultRequest;
use App\Http\Requests\UpdateAssayResultRequest;
use Illuminate\Http\Request;

class AssayController extends Controller
{
    public function index(Request $request)
    {
        $fire   = AssayResult::where('type', 'fire_assay')->orderByDesc('date')->paginate(10, ['*'], 'fire');
        $goc    = AssayResult::where('type', 'gold_on_carbon')->orderByDesc('date')->paginate(10, ['*'], 'goc');
        $bottle = AssayResult::where('type', 'bottle_roll')->orderByDesc('date')->paginate(10, ['*'], 'bottle');
        return view('assay.index', compact('fire', 'goc', 'bottle'));
    }

    public function trends(Request $request)
    {
        $from = $request->get('from', now()->subDays(90)->format('Y-m-d'));
        $to   = $request->get('to',   now()->format('Y-m-d'));

        // Clamp to sane range
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        // All assay results in range
        $assays = AssayResult::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['type', 'date', 'assay_value', 'description']);

        // Daily production purity + gold in range
        $production = DailyProduction::whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['date', 'purity_percentage', 'gold_smelted', 'ore_milled']);

        // ── Collect all unique date strings ──────────────────────────────
        $allDates = $assays->pluck('date')->map(fn($d) => $d->format('Y-m-d'))
            ->merge($production->pluck('date')->map(fn($d) => $d->format('Y-m-d')))
            ->unique()->sort()->values()->toArray();

        // ── Per-type assay grade aligned to $allDates ────────────────────
        $typesMeta = [
            'fire_assay'     => ['label' => 'Fire Assay',     'color' => '#ef4444'],
            'gold_on_carbon' => ['label' => 'Gold on Carbon', 'color' => '#fcb913'],
            'bottle_roll'    => ['label' => 'Bottle Roll',    'color' => '#3b82f6'],
        ];

        $chartData = [];
        foreach ($typesMeta as $type => $meta) {
            $grouped = $assays->where('type', $type)
                ->groupBy(fn($r) => $r->date->format('Y-m-d'));

            $aligned = [];
            $rawValues = [];
            foreach ($allDates as $d) {
                if (isset($grouped[$d]) && $grouped[$d]->count()) {
                    $v = round($grouped[$d]->avg('assay_value'), 4);
                    $aligned[]  = $v;
                    $rawValues[] = $v;
                } else {
                    $aligned[] = null;
                }
            }

            // 7-point rolling average (over non-null values only)
            $rolling = [];
            $window  = 7;
            $nonNulls = [];
            foreach ($aligned as $val) {
                if ($val !== null) {
                    $nonNulls[] = $val;
                    $slice = array_slice($nonNulls, -$window);
                    $rolling[] = round(array_sum($slice) / count($slice), 4);
                } else {
                    $rolling[] = null;
                }
            }

            // Trend direction: compare first-half avg vs second-half avg
            $halfLen  = max(1, intdiv(count($rawValues), 2));
            $firstAvg = count($rawValues) >= 2 ? array_sum(array_slice($rawValues, 0, $halfLen)) / $halfLen : null;
            $lastAvg  = count($rawValues) >= 2 ? array_sum(array_slice($rawValues, -$halfLen)) / $halfLen  : null;
            $trend    = ($firstAvg !== null && $lastAvg !== null)
                ? ($lastAvg > $firstAvg * 1.03 ? 'up' : ($lastAvg < $firstAvg * 0.97 ? 'down' : 'flat'))
                : 'flat';

            $chartData[$type] = [
                'label'   => $meta['label'],
                'color'   => $meta['color'],
                'aligned' => $aligned,
                'rolling' => $rolling,
                'stats'   => count($rawValues) > 0 ? [
                    'count' => count($rawValues),
                    'avg'   => round(array_sum($rawValues) / count($rawValues), 4),
                    'max'   => round(max($rawValues), 4),
                    'min'   => round(min($rawValues), 4),
                    'last'  => round(end($rawValues), 4),
                    'trend' => $trend,
                ] : null,
            ];
        }

        // ── Purity % aligned to $allDates ────────────────────────────────
        $prodGrouped = $production->groupBy(fn($r) => $r->date->format('Y-m-d'));
        $purityAligned = [];
        $purityRaw     = [];
        foreach ($allDates as $d) {
            if (isset($prodGrouped[$d])) {
                $avg = round($prodGrouped[$d]->avg('purity_percentage'), 2);
                $purityAligned[] = $avg > 0 ? $avg : null;
                if ($avg > 0) $purityRaw[] = $avg;
            } else {
                $purityAligned[] = null;
            }
        }

        $purityStats = count($purityRaw) > 0 ? [
            'count' => count($purityRaw),
            'avg'   => round(array_sum($purityRaw) / count($purityRaw), 2),
            'max'   => round(max($purityRaw), 2),
            'min'   => round(min($purityRaw), 2),
            'last'  => round(end($purityRaw), 2),
        ] : null;

        $chartLabels = array_map(
            fn($d) => \Carbon\Carbon::parse($d)->format('d M'),
            $allDates
        );

        return view('assay.trends', compact(
            'chartData', 'chartLabels',
            'purityAligned', 'purityStats',
            'from', 'to'
        ));
    }

    public function create()
    {
        return view('assay.create');
    }

    public function store(StoreAssayResultRequest $request)
    {
        AssayResult::create($request->validated());
        return redirect()->route('assay.index')->with('success', 'Assay result added.');
    }

    public function show(AssayResult $assay)
    {
        return view('assay.show', compact('assay'));
    }

    public function edit(AssayResult $assay)
    {
        return view('assay.edit', compact('assay'));
    }

    public function update(UpdateAssayResultRequest $request, AssayResult $assay)
    {
        $assay->update($request->validated());
        return redirect()->route('assay.index')->with('success', 'Assay result updated.');
    }

    public function destroy(AssayResult $assay)
    {
        $assay->delete();
        return redirect()->route('assay.index')->with('success', 'Assay result deleted.');
    }
}
