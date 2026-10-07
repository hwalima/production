<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMachineRuntimeRequest;
use App\Http\Requests\UpdateMachineRuntimeRequest;
use App\Models\AuditLog;
use App\Models\Machine;
use App\Models\MachineRuntime;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class MachineRuntimeController extends Controller
{
    public function create(Machine $machine)
    {
        abort_unless($machine->is_active, 404);

        return view('machines.runtimes.create', compact('machine'));
    }

    public function store(StoreMachineRuntimeRequest $request, Machine $machine)
    {
        abort_unless($machine->is_active, 404);
        $data = $request->validated();
        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $this->ensureNoOverlap($machine, $start, $end);

        $runtime = $machine->runtimes()->create([
            'machine_code' => $machine->machine_code,
            'description' => $machine->description,
            'service_after_hours' => $machine->service_interval_hours,
            'start_time' => $start,
            'end_time' => $end,
            'hours_run' => round(($end->getTimestamp() - $start->getTimestamp()) / 3600, 2),
        ]);

        AuditLog::record('machine_runtime_created', "Added runtime for {$machine->machine_code}", 'MachineRuntime', $runtime->id);

        return redirect()->route('machines.show', $machine)->with('success', 'Runtime recorded.');
    }

    public function edit(MachineRuntime $machineRuntime)
    {
        return view('machines.runtimes.edit', compact('machineRuntime'));
    }

    public function update(UpdateMachineRuntimeRequest $request, MachineRuntime $machineRuntime)
    {
        $data = $request->validated();
        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $machine = $machineRuntime->machine;
        $wasDue = $machine?->isServiceDue() ?? false;
        if ($machine) {
            $this->ensureNoOverlap($machine, $start, $end, $machineRuntime->id);
        }

        $machineRuntime->update([
            'start_time' => $start,
            'end_time' => $end,
            'hours_run' => round(($end->getTimestamp() - $start->getTimestamp()) / 3600, 2),
        ]);
        if ($machine && $wasDue && !$machine->fresh()->load(['latestRuntime', 'latestService'])->isServiceDue()) {
            $machine->update(['service_alert_sent_at' => null]);
        }
        AuditLog::record('machine_runtime_updated', "Updated runtime for {$machineRuntime->machine_code}", 'MachineRuntime', $machineRuntime->id);

        return redirect()->route('machines.show', $machineRuntime->machine_id)->with('success', 'Runtime updated.');
    }

    public function destroy(MachineRuntime $machineRuntime)
    {
        $machine = $machineRuntime->machine;
        $wasDue = $machine?->isServiceDue() ?? false;
        $runtimeId = $machineRuntime->id;
        $machineRuntime->delete();
        if ($machine && $wasDue && !$machine->fresh()->load(['latestRuntime', 'latestService'])->isServiceDue()) {
            $machine->update(['service_alert_sent_at' => null]);
        }
        AuditLog::record('machine_runtime_deleted', "Deleted runtime #{$runtimeId} for {$machine?->machine_code}", 'MachineRuntime', $runtimeId);

        return $machine
            ? redirect()->route('machines.show', $machine)->with('success', 'Runtime deleted.')
            : redirect()->route('machines.index')->with('success', 'Runtime deleted.');
    }

    private function ensureNoOverlap(Machine $machine, Carbon $start, Carbon $end, ?int $ignoreId = null): void
    {
        $query = $machine->runtimes()
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'start_time' => 'This time range overlaps another runtime recorded for this machine.',
            ]);
        }
    }
}
