<?php

namespace App\Services;

use App\Models\DailyProduction;
use App\Models\MiningRecord;

class StockpileService
{
    public function recalculateFrom(string $fromDate): void
    {
        $seed = DailyProduction::where('date', '<', $fromDate)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        $uncrushed = $seed ? (float) $seed->uncrushed_stockpile : 0.0;
        $unmilled = $seed ? (float) $seed->unmilled_stockpile : 0.0;
        $miningBeforeRange = MiningRecord::where('date', '<', $fromDate);
        if ($seed) {
            $miningBeforeRange->whereDate('date', '>', $seed->date->toDateString());
        }
        $uncrushed += (float) $miningBeforeRange->sum('ore_hoisted');

        $miningByDate = MiningRecord::where('date', '>=', $fromDate)
            ->selectRaw('date, SUM(ore_hoisted) as ore_hoisted')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($record) => substr((string) $record->date, 0, 10));

        $productionByDate = DailyProduction::where('date', '>=', $fromDate)
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($record) => $record->date->toDateString());

        $dates = $miningByDate->keys()
            ->merge($productionByDate->keys())
            ->unique()
            ->sort()
            ->values();

        foreach ($dates as $date) {
            $uncrushed += (float) ($miningByDate->get($date)->ore_hoisted ?? 0);

            foreach ($productionByDate->get($date, collect()) as $production) {
                $uncrushed -= (float) $production->ore_crushed;
                $unmilled += (float) $production->ore_crushed - (float) $production->ore_milled;

                $production->updateQuietly([
                    'uncrushed_stockpile' => $uncrushed,
                    'unmilled_stockpile' => $unmilled,
                ]);
            }
        }
    }
}
