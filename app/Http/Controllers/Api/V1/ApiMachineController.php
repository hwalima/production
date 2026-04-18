<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MachineRuntime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiMachineController extends Controller
{
    /**
     * GET /api/v1/machines
     * Paginated machine runtime records.
     *
     * Query params: from, to, filter=overdue, per_page
     */
    public function index(Request $request)
    {
        $request->validate([
            'from'     => 'nullable|date_format:Y-m-d',
            'to'       => 'nullable|date_format:Y-m-d',
            'filter'   => 'nullable|in:overdue',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $now  = Carbon::now();
        $perPage = min((int) $request->input('per_page', 30), 100);

        if ($request->input('filter') === 'overdue') {
            $query = MachineRuntime::whereNotNull('next_service_date')
                ->where('next_service_date', '<', $now->toDateString());
        } else {
            $from = $request->filled('from') ? $request->input('from') : $now->startOfMonth()->toDateString();
            $to   = $request->filled('to')   ? $request->input('to')   : $now->endOfMonth()->toDateString();
            $query = MachineRuntime::whereBetween(DB::raw('DATE(start_time)'), [$from, $to]);
        }

        return response()->json(
            $query->orderByDesc('start_time')->paginate($perPage)
        );
    }
}
