<?php

namespace App\Http\Controllers;

use App\Models\MiningDepartment;
use App\Models\AuditLog;
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

        AuditLog::record('department_created', "Added mining department: {$request->name}", 'MiningDepartment');

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

        AuditLog::record('department_updated', "Updated mining department: {$miningDepartment->name}", 'MiningDepartment', $miningDepartment->id);

        return redirect()->route('mining-departments.index')->with('success', 'Department updated.');
    }

    public function destroy(MiningDepartment $miningDepartment)
    {
        $deptId   = $miningDepartment->id;
        $deptName = $miningDepartment->name;
        $miningDepartment->delete();
        AuditLog::record('department_deleted', "Deleted mining department: {$deptName}", 'MiningDepartment', $deptId);
        return redirect()->route('mining-departments.index')->with('success', 'Department deleted.');
    }

    public function toggle(MiningDepartment $miningDepartment)
    {
        $miningDepartment->update(['is_active' => !$miningDepartment->is_active]);
        $status = $miningDepartment->is_active ? 'activated' : 'deactivated';
        AuditLog::record('department_toggled', "Mining department {$miningDepartment->name} {$status}", 'MiningDepartment', $miningDepartment->id);
        return redirect()->route('mining-departments.index');
    }
}
