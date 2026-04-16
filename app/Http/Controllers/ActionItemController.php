<?php
namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\MiningDepartment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActionItemController extends Controller
{
    public function index(Request $request)
    {
        $now        = Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        // Load departments that have items in range, plus all active ones for grouping
        $departments = MiningDepartment::active()->orderBy('name')->get();

        $items = ActionItem::with('department')
            ->whereBetween('reported_date', [$filterFrom, $filterTo])
            ->orderBy('mining_department_id')
            ->orderByRaw("FIELD(priority,'high','medium','low')")
            ->get()
            ->groupBy('mining_department_id');

        $overdueCount = ActionItem::overdueCount();

        return view('action-items.index', compact(
            'departments', 'items', 'filterFrom', 'filterTo', 'isDefaultRange', 'overdueCount'
        ));
    }

    public function create()
    {
        return view('action-items.create', [
            'departments' => MiningDepartment::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mining_department_id' => 'required|exists:mining_departments,id',
            'comment'              => 'required|string|max:1000',
            'priority'             => 'required|in:high,medium,low',
            'status'               => 'required|in:not_started,in_progress,pending,completed',
            'due_date'             => 'nullable|date',
            'reported_date'        => 'required|date',
        ]);

        ActionItem::create($request->only([
            'mining_department_id', 'comment', 'priority',
            'status', 'due_date', 'reported_date',
        ]));

        return redirect()->route('action-items.index')
            ->with('success', 'Action item added.');
    }

    public function edit(ActionItem $actionItem)
    {
        return view('action-items.edit', [
            'item'        => $actionItem,
            'departments' => MiningDepartment::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ActionItem $actionItem)
    {
        $request->validate([
            'mining_department_id' => 'required|exists:mining_departments,id',
            'comment'              => 'required|string|max:1000',
            'priority'             => 'required|in:high,medium,low',
            'status'               => 'required|in:not_started,in_progress,pending,completed',
            'due_date'             => 'nullable|date',
            'reported_date'        => 'required|date',
        ]);

        $actionItem->update($request->only([
            'mining_department_id', 'comment', 'priority',
            'status', 'due_date', 'reported_date',
        ]));

        return redirect()->route('action-items.index')
            ->with('success', 'Action item updated.');
    }

    public function destroy(ActionItem $actionItem)
    {
        $actionItem->delete();
        return redirect()->route('action-items.index')
            ->with('success', 'Action item deleted.');
    }
}
