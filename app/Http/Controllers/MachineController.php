<?php
namespace App\Http\Controllers;

use App\Models\MachineRuntime;
use App\Http\Requests\StoreMachineRuntimeRequest;
use App\Http\Requests\UpdateMachineRuntimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $now        = \Carbon\Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();

        $machines = MachineRuntime::whereBetween(DB::raw('DATE(start_time)'), [$filterFrom, $filterTo])
            ->orderByDesc('start_time')->paginate(30)->withQueryString();

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('machines.index', compact('machines', 'filterFrom', 'filterTo', 'isDefaultRange'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(StoreMachineRuntimeRequest $request)
    {
        $data = $request->validated();
        $data['next_service_date'] = Carbon::parse($data['end_time'])->addDays($data['service_after_hours']);
        MachineRuntime::create($data);
        return redirect()->route('machines.index')->with('success', 'Machine runtime added.');
    }

    public function show(MachineRuntime $machine)
    {
        return view('machines.show', compact('machine'));
    }

    public function edit(MachineRuntime $machine)
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(UpdateMachineRuntimeRequest $request, MachineRuntime $machine)
    {
        $data = $request->validated();
        $data['next_service_date'] = Carbon::parse($data['end_time'])->addDays($data['service_after_hours']);
        $machine->update($data);
        return redirect()->route('machines.index')->with('success', 'Machine runtime updated.');
    }

    public function destroy(MachineRuntime $machine)
    {
        $machine->delete();
        return redirect()->route('machines.index')->with('success', 'Machine runtime deleted.');
    }
}
