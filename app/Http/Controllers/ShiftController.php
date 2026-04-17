<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('name')->get();
        return view('settings.shifts', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:shifts,name',
        ]);

        Shift::create([
            'name'      => trim($request->name),
            'is_active' => true,
        ]);

        AuditLog::record('shift_created', "Added shift: {$request->name}", 'Shift');

        return redirect()->route('shifts.index')->with('success', 'Shift added.');
    }

    public function edit(Shift $shift)
    {
        $shifts = Shift::orderBy('name')->get();
        return view('settings.shifts', compact('shifts', 'shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:shifts,name,' . $shift->id,
        ]);

        $shift->update([
            'name'      => trim($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('shift_updated', "Updated shift: {$shift->name}", 'Shift', $shift->id);

        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift)
    {
        $shiftId   = $shift->id;
        $shiftName = $shift->name;
        $shift->delete();
        AuditLog::record('shift_deleted', "Deleted shift: {$shiftName}", 'Shift', $shiftId);
        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }

    /** Toggle active status via PATCH */
    public function toggle(Shift $shift)
    {
        $shift->update(['is_active' => !$shift->is_active]);
        $status = $shift->is_active ? 'activated' : 'deactivated';
        AuditLog::record('shift_toggled', "Shift {$shift->name} {$status}", 'Shift', $shift->id);
        return redirect()->route('shifts.index');
    }
}
