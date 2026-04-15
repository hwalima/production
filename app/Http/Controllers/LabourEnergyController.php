<?php
namespace App\Http\Controllers;

use App\Models\LabourEnergy;
use App\Http\Requests\StoreLabourEnergyRequest;
use App\Http\Requests\UpdateLabourEnergyRequest;
use Illuminate\Http\Request;
use App\Models\Setting;

class LabourEnergyController extends Controller
{
    public function index(Request $request)
    {
        $records = LabourEnergy::orderByDesc('date')->paginate(30);
        $defaults = [
            'zesa' => Setting::where('key', 'zesa_daily')->value('value'),
            'diesel' => Setting::where('key', 'diesel_daily')->value('value'),
            'labour' => Setting::where('key', 'labour_daily')->value('value'),
        ];
        return view('labour_energy.index', compact('records', 'defaults'));
    }

    public function create()
    {
        $defaults = [
            'zesa' => Setting::where('key', 'zesa_daily')->value('value'),
            'diesel' => Setting::where('key', 'diesel_daily')->value('value'),
            'labour' => Setting::where('key', 'labour_daily')->value('value'),
        ];
        return view('labour_energy.create', compact('defaults'));
    }

    public function store(StoreLabourEnergyRequest $request)
    {
        LabourEnergy::create($request->validated());
        return redirect()->route('labour_energy.index')->with('success', 'Labour & Energy record added.');
    }

    public function show(LabourEnergy $labour_energy)
    {
        return view('labour_energy.show', compact('labour_energy'));
    }

    public function edit(LabourEnergy $labour_energy)
    {
        $defaults = [
            'zesa' => Setting::where('key', 'zesa_daily')->value('value'),
            'diesel' => Setting::where('key', 'diesel_daily')->value('value'),
            'labour' => Setting::where('key', 'labour_daily')->value('value'),
        ];
        return view('labour_energy.edit', compact('labour_energy', 'defaults'));
    }

    public function update(UpdateLabourEnergyRequest $request, LabourEnergy $labour_energy)
    {
        $labour_energy->update($request->validated());
        return redirect()->route('labour_energy.index')->with('success', 'Labour & Energy record updated.');
    }

    public function destroy(LabourEnergy $labour_energy)
    {
        $labour_energy->delete();
        return redirect()->route('labour_energy.index')->with('success', 'Labour & Energy record deleted.');
    }
}
