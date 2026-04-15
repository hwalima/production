<?php
namespace App\Http\Controllers;

use App\Models\DrillingRecord;
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
        DrillingRecord::create($request->validated());
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
        return redirect()->route('drilling.index')->with('success', 'Drilling record updated.');
    }

    public function destroy(DrillingRecord $drilling)
    {
        $drilling->delete();
        return redirect()->route('drilling.index')->with('success', 'Drilling record deleted.');
    }
}
