<?php
namespace App\Http\Controllers;

use App\Models\LabourEnergy;
use App\Models\AuditLog;
use App\Models\MiningDepartment;
use App\Http\Requests\StoreLabourEnergyRequest;
use App\Http\Requests\UpdateLabourEnergyRequest;
use Illuminate\Http\Request;
use App\Models\Setting;

class LabourEnergyController extends Controller
{
    public function index(Request $request)
    {
        $records = LabourEnergy::with('deptCosts.department')->orderByDesc('date')->paginate(30);
        return view('labour_energy.index', compact('records'));
    }

    public function create()
    {
        $ds = Setting::whereIn('key', ['zesa_daily', 'diesel_daily'])->pluck('value', 'key');
        $defaults = [
            'zesa'   => $ds->get('zesa_daily'),
            'diesel' => $ds->get('diesel_daily'),
        ];
        $departments = MiningDepartment::active()->orderBy('name')->get();
        return view('labour_energy.create', compact('defaults', 'departments'));
    }

    public function store(StoreLabourEnergyRequest $request)
    {
        $data = $request->validated();
        $data['labour_cost'] = 0; // will be recalculated after dept costs saved
        $record = LabourEnergy::create($data);

        foreach ($request->input('dept_costs', []) as $deptId => $cost) {
            if (is_numeric($cost) && (float) $cost > 0) {
                $record->deptCosts()->create([
                    'mining_department_id' => $deptId,
                    'labour_cost'          => $cost,
                ]);
            }
        }

        $record->syncLabourTotal();

        AuditLog::record('labour_energy_created', "Added labour & energy record for {$data['date']}", 'LabourEnergy', $record->id);

        return redirect()->route('labour-energy.index')->with('success', 'Labour & Energy record added.');
    }

    public function show(LabourEnergy $labour_energy)
    {
        $labour_energy->load('deptCosts.department');
        return view('labour_energy.show', compact('labour_energy'));
    }

    public function edit(LabourEnergy $labour_energy)
    {
        $ds = Setting::whereIn('key', ['zesa_daily', 'diesel_daily'])->pluck('value', 'key');
        $defaults = [
            'zesa'   => $ds->get('zesa_daily'),
            'diesel' => $ds->get('diesel_daily'),
        ];
        $departments = MiningDepartment::active()->orderBy('name')->get();
        $existingCosts = $labour_energy->deptCosts()->pluck('labour_cost', 'mining_department_id');
        return view('labour_energy.edit', compact('labour_energy', 'defaults', 'departments', 'existingCosts'));
    }

    public function update(UpdateLabourEnergyRequest $request, LabourEnergy $labour_energy)
    {
        $data = $request->validated();
        unset($data['labour_cost']); // managed via dept costs
        $labour_energy->update($data);

        // Sync dept costs: delete then recreate
        $labour_energy->deptCosts()->delete();
        foreach ($request->input('dept_costs', []) as $deptId => $cost) {
            if (is_numeric($cost) && (float) $cost > 0) {
                $labour_energy->deptCosts()->create([
                    'mining_department_id' => $deptId,
                    'labour_cost'          => $cost,
                ]);
            }
        }

        $labour_energy->syncLabourTotal();

        AuditLog::record('labour_energy_updated', "Updated labour & energy record for {$labour_energy->date->toDateString()}", 'LabourEnergy', $labour_energy->id);

        return redirect()->route('labour-energy.index')->with('success', 'Labour & Energy record updated.');
    }

    public function destroy(LabourEnergy $labour_energy)
    {
        $leDate = $labour_energy->date->toDateString();
        $leId   = $labour_energy->id;
        $labour_energy->delete();
        AuditLog::record('labour_energy_deleted', "Deleted labour & energy record for {$leDate}", 'LabourEnergy', $leId);
        return redirect()->route('labour-energy.index')->with('success', 'Labour & Energy record deleted.');
    }
}
