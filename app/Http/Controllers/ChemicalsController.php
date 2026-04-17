<?php
namespace App\Http\Controllers;

use App\Models\Chemical;
use App\Models\AuditLog;
use App\Http\Requests\StoreChemicalRequest;
use App\Http\Requests\UpdateChemicalRequest;
use Illuminate\Http\Request;

class ChemicalsController extends Controller
{
    public function index(Request $request)
    {
        $now        = \Carbon\Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();

        $chemicals = Chemical::whereBetween('date', [$filterFrom, $filterTo])
            ->orderByDesc('date')->paginate(30)->withQueryString();

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('chemicals.index', compact('chemicals', 'filterFrom', 'filterTo', 'isDefaultRange'));
    }

    public function create()
    {
        return view('chemicals.create');
    }

    public function store(StoreChemicalRequest $request)
    {
        $record = Chemical::create($request->validated());
        AuditLog::record('chemicals_created', "Added chemical record for {$record->date}", 'Chemical', $record->id);
        return redirect()->route('chemicals.index')->with('success', 'Chemical record added.');
    }

    public function show(Chemical $chemical)
    {
        return view('chemicals.show', compact('chemical'));
    }

    public function edit(Chemical $chemical)
    {
        return view('chemicals.edit', compact('chemical'));
    }

    public function update(UpdateChemicalRequest $request, Chemical $chemical)
    {
        $chemical->update($request->validated());
        AuditLog::record('chemicals_updated', "Updated chemical record #{$chemical->id} for {$chemical->date}", 'Chemical', $chemical->id);
        return redirect()->route('chemicals.index')->with('success', 'Chemical record updated.');
    }

    public function destroy(Chemical $chemical)
    {
        $chemId   = $chemical->id;
        $chemDate = $chemical->date;
        $chemical->delete();
        AuditLog::record('chemicals_deleted', "Deleted chemical record #{$chemId} for {$chemDate}", 'Chemical', $chemId);
        return redirect()->route('chemicals.index')->with('success', 'Chemical record deleted.');
    }
}
