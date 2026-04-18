<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlastingRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiBlastingController extends Controller
{
    /**
     * GET /api/v1/blasting
     * Query params: from, to, per_page
     */
    public function index(Request $request)
    {
        $request->validate([
            'from'     => 'nullable|date_format:Y-m-d',
            'to'       => 'nullable|date_format:Y-m-d',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $now     = Carbon::now();
        $from    = $request->filled('from') ? $request->input('from') : $now->startOfMonth()->toDateString();
        $to      = $request->filled('to')   ? $request->input('to')   : $now->endOfMonth()->toDateString();
        $perPage = min((int) $request->input('per_page', 30), 100);

        return response()->json(
            BlastingRecord::whereBetween('date', [$from, $to])
                ->orderByDesc('date')
                ->paginate($perPage)
        );
    }
}
