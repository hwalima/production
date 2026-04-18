<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiActionItemController extends Controller
{
    /**
     * GET /api/v1/action-items
     * Paginated list of action items.
     *
     * Query params: from, to, status (not_started|in_progress|pending|completed|overdue),
     *               priority (high|medium|low), per_page
     */
    public function index(Request $request)
    {
        $request->validate([
            'from'     => 'nullable|date_format:Y-m-d',
            'to'       => 'nullable|date_format:Y-m-d',
            'status'   => 'nullable|in:not_started,in_progress,pending,completed,overdue',
            'priority' => 'nullable|in:high,medium,low',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $now  = Carbon::now();
        $from = $request->filled('from') ? $request->input('from') : $now->startOfMonth()->toDateString();
        $to   = $request->filled('to')   ? $request->input('to')   : $now->endOfMonth()->toDateString();
        $perPage = min((int) $request->input('per_page', 30), 100);

        $query = ActionItem::with('department:id,name')
            ->whereBetween('reported_date', [$from, $to]);

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'overdue') {
                $query->whereNotIn('status', ['completed'])
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', Carbon::today());
            } else {
                $query->where('status', $status);
            }
        }

        $records = $query
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderByDesc('reported_date')
            ->paginate($perPage);

        return response()->json($records);
    }
}
