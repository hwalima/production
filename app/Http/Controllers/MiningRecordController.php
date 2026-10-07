<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MiningRecord;
use App\Models\Setting;
use App\Services\StockpileService;
use Illuminate\Http\Request;

class MiningRecordController extends Controller
{
    public function create()
    {
        return view('assay.mining.create', [
            'shifts' => \App\Models\Shift::active()->orderBy('name')->pluck('name'),
            'miningSites' => \App\Models\MiningSite::active()->orderBy('name')->pluck('name'),
            'skipFactor' => (float) (Setting::where('key', 'skip_factor')->value('value') ?? 0.8),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:50',
            'mining_site' => 'required|string|max:100',
            'ore_hoisted_entered' => 'required|numeric|min:0',
            'ore_hoisted_target' => 'nullable|numeric|min:0',
            'waste_hoisted' => 'required|numeric|min:0',
        ]);

        $factor = (float) (Setting::where('key', 'skip_factor')->value('value') ?? 0.8);
        $data['skip_factor'] = $factor;
        $data['ore_hoisted'] = round((float) $data['ore_hoisted_entered'] * $factor, 2);

        $record = MiningRecord::create($data);
        app(StockpileService::class)->recalculateFrom($record->date->toDateString());

        AuditLog::record('mining_record_created', "Added mining record for {$record->date->toDateString()}", 'MiningRecord', $record->id);

        return redirect()->route('assay.index', ['tab' => 'mining'])->with('success', 'Mining record added.');
    }

    public function edit(MiningRecord $miningRecord)
    {
        return view('assay.mining.edit', [
            'miningRecord' => $miningRecord,
            'shifts' => \App\Models\Shift::active()->orderBy('name')->pluck('name'),
            'miningSites' => \App\Models\MiningSite::active()->orderBy('name')->pluck('name'),
            'skipFactor' => (float) (Setting::where('key', 'skip_factor')->value('value') ?? 0.8),
        ]);
    }

    public function update(Request $request, MiningRecord $miningRecord)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:50',
            'mining_site' => 'required|string|max:100',
            'ore_hoisted_entered' => 'required|numeric|min:0',
            'ore_hoisted_target' => 'nullable|numeric|min:0',
            'waste_hoisted' => 'required|numeric|min:0',
        ]);

        $factor = (float) (Setting::where('key', 'skip_factor')->value('value') ?? 0.8);
        $data['skip_factor'] = $factor;
        $data['ore_hoisted'] = round((float) $data['ore_hoisted_entered'] * $factor, 2);

        $oldDate = $miningRecord->date->toDateString();
        $miningRecord->update($data);
        app(StockpileService::class)->recalculateFrom(min($oldDate, $miningRecord->date->toDateString()));

        AuditLog::record('mining_record_updated', "Updated mining record #{$miningRecord->id}", 'MiningRecord', $miningRecord->id);

        return redirect()->route('assay.index', ['tab' => 'mining'])->with('success', 'Mining record updated.');
    }

    public function destroy(MiningRecord $miningRecord)
    {
        $date = $miningRecord->date->toDateString();
        $id = $miningRecord->id;
        $miningRecord->delete();
        app(StockpileService::class)->recalculateFrom($date);

        AuditLog::record('mining_record_deleted', "Deleted mining record #{$id}", 'MiningRecord', $id);

        return redirect()->route('assay.index', ['tab' => 'mining'])->with('success', 'Mining record deleted.');
    }
}
