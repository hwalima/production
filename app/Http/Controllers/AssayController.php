<?php
namespace App\Http\Controllers;

use App\Models\AssayResult;
use App\Http\Requests\StoreAssayResultRequest;
use App\Http\Requests\UpdateAssayResultRequest;
use Illuminate\Http\Request;

class AssayController extends Controller
{
    public function index(Request $request)
    {
        $fire = AssayResult::where('type', 'fire_assay')->orderByDesc('date')->paginate(10, ['*'], 'fire');
        $goc = AssayResult::where('type', 'gold_on_carbon')->orderByDesc('date')->paginate(10, ['*'], 'goc');
        $bottle = AssayResult::where('type', 'bottle_roll')->orderByDesc('date')->paginate(10, ['*'], 'bottle');
        return view('assay.index', compact('fire', 'goc', 'bottle'));
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
