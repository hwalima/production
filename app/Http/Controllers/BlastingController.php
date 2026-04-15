<?php
namespace App\Http\Controllers;

use App\Models\BlastingRecord;
use App\Http\Requests\StoreBlastingRecordRequest;
use App\Http\Requests\UpdateBlastingRecordRequest;
use Illuminate\Http\Request;

class BlastingController extends Controller
{
    public function index(Request $request)
    {
        $now        = \Carbon\Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();

        $records = BlastingRecord::whereBetween('date', [$filterFrom, $filterTo])
            ->orderByDesc('date')->paginate(30)->withQueryString();

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('blasting.index', compact('records', 'filterFrom', 'filterTo', 'isDefaultRange'));
    }

    public function create()
    {
        return view('blasting.create');
    }

    public function store(StoreBlastingRecordRequest $request)
    {
        BlastingRecord::create($request->validated());
        return redirect()->route('blasting.index')->with('success', 'Blasting record added.');
    }

    public function show(BlastingRecord $blasting)
    {
        return view('blasting.show', compact('blasting'));
    }

    public function edit(BlastingRecord $blasting)
    {
        return view('blasting.edit', compact('blasting'));
    }

    public function update(UpdateBlastingRecordRequest $request, BlastingRecord $blasting)
    {
        $blasting->update($request->validated());
        return redirect()->route('blasting.index')->with('success', 'Blasting record updated.');
    }

    public function destroy(BlastingRecord $blasting)
    {
        $blasting->delete();
        return redirect()->route('blasting.index')->with('success', 'Blasting record deleted.');
    }
}
