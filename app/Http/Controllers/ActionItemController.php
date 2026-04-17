<?php
namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\AuditLog;
use App\Models\MiningDepartment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
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
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
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

        Cache::forget('ai_overdue_count');

        AuditLog::record('action_item_created', "Added action item: priority={$request->priority}, dept ID={$request->mining_department_id}", 'ActionItem');

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

        Cache::forget('ai_overdue_count');

        AuditLog::record('action_item_updated', "Updated action item #{$actionItem->id}: status={$request->status}, priority={$request->priority}", 'ActionItem', $actionItem->id);

        return redirect()->route('action-items.index')
            ->with('success', 'Action item updated.');
    }

    public function destroy(ActionItem $actionItem)
    {
        $itemId = $actionItem->id;
        $actionItem->delete();
        Cache::forget('ai_overdue_count');
        AuditLog::record('action_item_deleted', "Deleted action item #{$itemId}", 'ActionItem', $itemId);
        return redirect()->route('action-items.index')
            ->with('success', 'Action item deleted.');
    }

    public function pdf(Request $request)
    {
        $now        = Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();

        $departments = MiningDepartment::active()->orderBy('name')->get();

        $items = ActionItem::with('department')
            ->whereBetween('reported_date', [$filterFrom, $filterTo])
            ->orderBy('mining_department_id')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->get()
            ->groupBy('mining_department_id');

        // Build logo/company data (same helper pattern as ReportController)
        $settings   = Setting::all()->pluck('value', 'key');
        $logoPath   = $settings['logo_path'] ?? null;
        $logoBase64 = null;
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $absPath    = storage_path('app/public/' . $logoPath);
            $mime       = mime_content_type($absPath);
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(
                Storage::disk('public')->get($logoPath)
            );
        }

        $data = [
            'logoBase64'      => $logoBase64,
            'companyName'     => $settings['company_name']     ?? config('app.name'),
            'companyLocation' => $settings['company_location'] ?? ($settings['company_address'] ?? ''),
            'companyPhone'    => $settings['company_phone']    ?? '',
            'companyEmail'    => $settings['company_email']    ?? '',
            'departments'     => $departments,
            'items'           => $items,
            'filterFrom'      => $filterFrom,
            'filterTo'        => $filterTo,
        ];

        $filename = 'action-items-' . $filterFrom . '--' . $filterTo . '.pdf';

        return Pdf::loadView('pdf.action-items', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}
