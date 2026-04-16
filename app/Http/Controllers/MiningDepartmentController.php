<?php

namespace App\Http\Controllers;

use App\Models\MiningDepartment;
use Illuminate\Http\Request;

class MiningDepartmentController extends Controller
{
    public function index()
    {
        $departments = MiningDepartment::orderBy('name')->get();
        return view('settings.mining-departments', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:mining_departments,name',
            'description' => 'nullable|string|max:255',
        ]);

        MiningDepartment::create([
            'name'        => trim($request->name),
            'description' => trim($request->description ?? ''),
            'is_active'   => true,
        ]);

        return redirect()->route('mining-departments.index')->with('success', 'Department added.');
    }

    public function edit(MiningDepartment $miningDepartment)
    {
        $departments = MiningDepartment::orderBy('name')->get();
        return view('settings.mining-departments', ['departments' => $departments, 'editing' => $miningDepartment]);
    }

    public function update(Request $request, MiningDepartment $miningDepartment)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:mining_departments,name,' . $miningDepartment->id,
            'description' => 'nullable|string|max:255',
        ]);

        $miningDepartment->update([
            'name'        => trim($request->name),
            'description' => trim($request->description ?? ''),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('mining-departments.index')->with('success', 'Department updated.');
    }

    public function destroy(MiningDepartment $miningDepartment)
    {
        $miningDepartment->delete();
        return redirect()->route('mining-departments.index')->with('success', 'Department deleted.');
    }

    public function toggle(MiningDepartment $miningDepartment)
    {
        $miningDepartment->update(['is_active' => !$miningDepartment->is_active]);
        return redirect()->route('mining-departments.index');
    }
}
