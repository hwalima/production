<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LabourEnergy;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiLabourEnergyController extends Controller
{
    /**
     * GET /api/v1/labour-energy
     * Paginated labour & energy records with department cost breakdown.
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
            LabourEnergy::with(['deptCosts.department:id,name'])
                ->whereBetween('date', [$from, $to])
                ->orderByDesc('date')
                ->paginate($perPage)
        );
    }
}
