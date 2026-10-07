<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::with(['latestRuntime', 'latestService'])
            ->orderBy('machine_code')
            ->paginate(30);
        $canManageMachines = in_array(auth()->user()->role, ['super_admin', 'admin', 'manager'], true);

        return view('machines.index', compact('machines', 'canManageMachines'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_code' => 'required|string|max:255|unique:machines,machine_code',
            'description' => 'required|string|max:255',
            'service_interval_hours' => 'required|integer|min:1',
        ]);

        $machine = Machine::create($data);
        AuditLog::record('machine_registered', "Registered machine {$machine->machine_code}", 'Machine', $machine->id);

        return redirect()->route('machines.show', $machine)->with('success', 'Machine registered.');
    }

    public function show(Machine $machine)
    {
        $machine->load(['latestRuntime', 'latestService']);
        $runtimes = $machine->runtimes()->orderByDesc('start_time')->paginate(15, ['*'], 'runtime_page');
        $services = $machine->services()->orderByDesc('serviced_at')->get();
        $canManageMachines = in_array(auth()->user()->role, ['super_admin', 'admin', 'manager'], true);

        return view('machines.show', compact('machine', 'runtimes', 'services', 'canManageMachines'));
    }

    public function edit(Machine $machine)
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine)
    {
        $serviceIntervalChanged = (int) $request->input('service_interval_hours') !== $machine->service_interval_hours;
        $data = $request->validate([
            'machine_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('machines', 'machine_code')->ignore($machine->id),
            ],
            'description' => 'required|string|max:255',
            'service_interval_hours' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if ($serviceIntervalChanged) {
            $data['service_alert_sent_at'] = null;
        }
        $machine->update($data);
        AuditLog::record('machine_updated', "Updated machine {$machine->machine_code}", 'Machine', $machine->id);

        return redirect()->route('machines.show', $machine)->with('success', 'Machine updated.');
    }

    public function destroy(Machine $machine)
    {
        $machine->update(['is_active' => false]);
        AuditLog::record('machine_deactivated', "Deactivated machine {$machine->machine_code}", 'Machine', $machine->id);

        return redirect()->route('machines.index')->with('success', 'Machine deactivated. Its runtime and service history has been kept.');
    }
}
