<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineServiceController extends Controller
{
    public function store(Request $request, Machine $machine)
    {
        $data = $request->validate([
            'serviced_at' => 'required|date|before_or_equal:now',
            'notes' => 'nullable|string|max:2000',
        ]);

        $service = $machine->services()->create($data);
        $machine->update(['service_alert_sent_at' => null]);
        AuditLog::record('machine_serviced', "Recorded service for {$machine->machine_code}", 'MachineService', $service->id);

        return redirect()->route('machines.show', $machine)->with('success', 'Service recorded and operating-hour tracking reset.');
    }
}
