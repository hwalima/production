<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Consumable;
use Illuminate\Http\Request;

class ApiConsumableController extends Controller
{
    /**
     * GET /api/v1/consumables
     * Paginated list of active consumables with current stock levels.
     *
     * Query params: category, per_page
     */
    public function index(Request $request)
    {
        $request->validate([
            'category' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = min((int) $request->input('per_page', 50), 100);

        $page = Consumable::query()
            ->when($request->filled('category'), fn($q) => $q->where('category', $request->input('category')))
            ->where('is_active', true)
            ->withSum(['movements as stock_in'  => fn($q) => $q->where('direction', 'in')],  'quantity')
            ->withSum(['movements as stock_out' => fn($q) => $q->where('direction', 'out')], 'quantity')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate($perPage)
            ->through(function ($c) {
                $c->current_stock = (float)($c->stock_in ?? 0) - (float)($c->stock_out ?? 0);
                $c->unit_cost     = (float)$c->units_per_pack > 0
                    ? round((float)$c->pack_cost / (float)$c->units_per_pack, 4) : 0;
                $c->low_stock     = (float)$c->reorder_level > 0
                    && $c->current_stock <= (float)$c->reorder_level;
                $c->out_of_stock  = $c->current_stock <= 0;
                unset($c->stock_in, $c->stock_out);
                return $c;
            });

        return response()->json($page);
    }

    /**
     * GET /api/v1/consumables/low-stock
     * All items at or below their reorder level.
     */
    public function lowStock(Request $request)
    {
        $items = Consumable::where('is_active', true)
            ->withSum(['movements as stock_in'  => fn($q) => $q->where('direction', 'in')],  'quantity')
            ->withSum(['movements as stock_out' => fn($q) => $q->where('direction', 'out')], 'quantity')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $c->current_stock = (float)($c->stock_in ?? 0) - (float)($c->stock_out ?? 0);
                $c->unit_cost     = (float)$c->units_per_pack > 0
                    ? round((float)$c->pack_cost / (float)$c->units_per_pack, 4) : 0;
                $c->deficit       = max(0, (float)$c->reorder_level - $c->current_stock);
                $c->out_of_stock  = $c->current_stock <= 0;
                unset($c->stock_in, $c->stock_out);
                return $c;
            })
            ->filter(fn($c) => (float)$c->reorder_level > 0 && $c->current_stock <= (float)$c->reorder_level)
            ->values();

        return response()->json(['data' => $items, 'count' => $items->count()]);
    }
}
