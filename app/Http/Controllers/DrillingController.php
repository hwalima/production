<?php
namespace App\Http\Controllers;

use App\Models\DrillingRecord;
use App\Models\AuditLog;
use App\Http\Requests\StoreDrillingRecordRequest;
use App\Http\Requests\UpdateDrillingRecordRequest;
use Illuminate\Http\Request;

class DrillingController extends Controller
{
    public function index(Request $request)
    {
        $now        = \Carbon\Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();

        $records = DrillingRecord::whereBetween('date', [$filterFrom, $filterTo])
            ->orderByDesc('date')->paginate(30)->withQueryString();

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('drilling.index', compact('records', 'filterFrom', 'filterTo', 'isDefaultRange'));
    }

    public function create()
    {
        return view('drilling.create');
    }

    public function store(StoreDrillingRecordRequest $request)
    {
        $record = DrillingRecord::create($request->validated());
        AuditLog::record('drilling_created', "Added drilling record for {$record->date}", 'DrillingRecord', $record->id);
        return redirect()->route('drilling.index')->with('success', 'Drilling record added.');
    }

    public function show(DrillingRecord $drilling)
    {
        return view('drilling.show', compact('drilling'));
    }

    public function edit(DrillingRecord $drilling)
    {
        return view('drilling.edit', compact('drilling'));
    }

    public function update(UpdateDrillingRecordRequest $request, DrillingRecord $drilling)
    {
        $drilling->update($request->validated());
        AuditLog::record('drilling_updated', "Updated drilling record #{$drilling->id} for {$drilling->date}", 'DrillingRecord', $drilling->id);
        return redirect()->route('drilling.index')->with('success', 'Drilling record updated.');
    }

    public function destroy(DrillingRecord $drilling)
    {
        $drillingId   = $drilling->id;
        $drillingDate = $drilling->date;
        $drilling->delete();
        AuditLog::record('drilling_deleted', "Deleted drilling record #{$drillingId} for {$drillingDate}", 'DrillingRecord', $drillingId);
        return redirect()->route('drilling.index')->with('success', 'Drilling record deleted.');
    }
}
