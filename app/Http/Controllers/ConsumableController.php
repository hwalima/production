<?php
namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\ConsumableStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsumableController extends Controller
{
    // ── Catalog index ─────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $category = $request->input('category', 'all');

        $consumables = Consumable::query()
            ->when($category !== 'all', fn($q) => $q->where('category', $category))
            ->withSum(['movements as stock_in_qty'  => fn($q) => $q->where('direction', 'in')],  'quantity')
            ->withSum(['movements as stock_out_qty' => fn($q) => $q->where('direction', 'out')], 'quantity')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $c->current_stock = (float)($c->stock_in_qty ?? 0) - (float)($c->stock_out_qty ?? 0);
                $c->unit_cost     = (float)$c->units_per_pack > 0
                    ? (float)$c->pack_cost / (float)$c->units_per_pack : 0;
                $c->low_stock     = (float)$c->reorder_level > 0
                    && $c->current_stock <= (float)$c->reorder_level;
                $c->out_of_stock  = $c->current_stock <= 0;
                return $c;
            });

        $categories   = Consumable::distinct()->pluck('category')->sort()->values();
        $lowStockCount = $consumables->where('low_stock', true)->count();
        $totalItems   = $consumables->count();

        return view('consumables.index', compact(
            'consumables', 'category', 'categories',
            'lowStockCount', 'totalItems'
        ));
    }

    // ── Catalog create / store ────────────────────────────────────────────
    public function create()
    {
        return view('consumables.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'category'       => 'required|string|max:50',
            'description'    => 'nullable|string|max:500',
            'purchase_unit'  => 'required|string|max:50',
            'use_unit'       => 'required|string|max:50',
            'units_per_pack' => 'required|numeric|min:0.0001',
            'pack_cost'      => 'required|numeric|min:0',
            'reorder_level'  => 'nullable|numeric|min:0',
            'is_active'      => 'nullable|boolean',
        ]);

        $data['reorder_level'] = $data['reorder_level'] ?? 0;
        $data['is_active']     = true;

        Consumable::create($data);

        return redirect()->route('consumables.index')->with('success', 'Item added to the stores catalog.');
    }

    // ── Detail & history ──────────────────────────────────────────────────
    public function show(Consumable $consumable)
    {
        $movements = $consumable->movements()
            ->with('user')
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(25);

        $stockIn    = (float) $consumable->movements()->where('direction', 'in')->sum('quantity');
        $stockOut   = (float) $consumable->movements()->where('direction', 'out')->sum('quantity');
        $stock      = $stockIn - $stockOut;
        $unitCost   = (float) $consumable->units_per_pack > 0
            ? (float) $consumable->pack_cost / (float) $consumable->units_per_pack : 0;
        $totalSpent = (float) $consumable->movements()->where('direction', 'in')->sum('total_cost');
        $totalUsed  = (float) $consumable->movements()->where('direction', 'out')->sum('total_cost');

        return view('consumables.show', compact(
            'consumable', 'movements',
            'stock', 'stockIn', 'stockOut',
            'unitCost', 'totalSpent', 'totalUsed'
        ));
    }

    // ── Edit / update ─────────────────────────────────────────────────────
    public function edit(Consumable $consumable)
    {
        return view('consumables.edit', compact('consumable'));
    }

    public function update(Request $request, Consumable $consumable)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'category'       => 'required|string|max:50',
            'description'    => 'nullable|string|max:500',
            'purchase_unit'  => 'required|string|max:50',
            'use_unit'       => 'required|string|max:50',
            'units_per_pack' => 'required|numeric|min:0.0001',
            'pack_cost'      => 'required|numeric|min:0',
            'reorder_level'  => 'nullable|numeric|min:0',
        ]);

        $data['reorder_level'] = $data['reorder_level'] ?? 0;

        $consumable->update($data);

        return redirect()->route('consumables.show', $consumable)
            ->with('success', 'Consumable updated successfully.');
    }

    public function destroy(Consumable $consumable)
    {
        $consumable->delete();
        return redirect()->route('consumables.index')->with('success', 'Item removed from catalog.');
    }

    // ── Receive stock (IN) ────────────────────────────────────────────────
    public function receiveForm(Consumable $consumable)
    {
        $unitCost = (float) $consumable->units_per_pack > 0
            ? (float) $consumable->pack_cost / (float) $consumable->units_per_pack : 0;
        $stock    = (float) $consumable->movements()->where('direction', 'in')->sum('quantity')
                  - (float) $consumable->movements()->where('direction', 'out')->sum('quantity');

        return view('consumables.receive', compact('consumable', 'unitCost', 'stock'));
    }

    public function receiveStock(Request $request, Consumable $consumable)
    {
        $request->validate([
            'packs'         => 'required|numeric|min:0.0001',
            'pack_cost'     => 'nullable|numeric|min:0',
            'movement_date' => 'required|date',
            'reference'     => 'nullable|string|max:200',
            'notes'         => 'nullable|string|max:500',
        ]);

        // Allow updating the stored pack cost on each delivery
        $packCost = $request->filled('pack_cost')
            ? (float) $request->pack_cost
            : (float) $consumable->pack_cost;

        if ($request->filled('pack_cost')) {
            $consumable->update(['pack_cost' => $packCost]);
        }

        $packs    = (float) $request->packs;
        $quantity = $packs * (float) $consumable->units_per_pack;
        $unitCost = (float) $consumable->units_per_pack > 0
            ? $packCost / (float) $consumable->units_per_pack : 0;

        ConsumableStockMovement::create([
            'consumable_id' => $consumable->id,
            'user_id'       => Auth::id(),
            'type'          => 'purchase',
            'direction'     => 'in',
            'quantity'      => $quantity,
            'packs'         => $packs,
            'unit_cost'     => $unitCost,
            'total_cost'    => $quantity * $unitCost,
            'movement_date' => $request->movement_date,
            'reference'     => $request->reference,
            'notes'         => $request->notes,
        ]);

        return redirect()->route('consumables.show', $consumable)
            ->with('success', sprintf(
                'Received %s %s(s) — %s pack(s) × %s %s.',
                number_format($quantity, 2),
                $consumable->use_unit,
                number_format($packs, 2),
                $consumable->purchase_unit,
                $consumable->name
            ));
    }

    // ── Use / issue stock (OUT) ───────────────────────────────────────────
    public function useForm(Consumable $consumable)
    {
        $stock    = (float) $consumable->movements()->where('direction', 'in')->sum('quantity')
                  - (float) $consumable->movements()->where('direction', 'out')->sum('quantity');
        $unitCost = (float) $consumable->units_per_pack > 0
            ? (float) $consumable->pack_cost / (float) $consumable->units_per_pack : 0;

        return view('consumables.use', compact('consumable', 'stock', 'unitCost'));
    }

    public function useStock(Request $request, Consumable $consumable)
    {
        $request->validate([
            'quantity'      => 'required|numeric|min:0.0001',
            'movement_date' => 'required|date',
            'type'          => 'required|in:usage,adjustment,return',
            'reference'     => 'nullable|string|max:200',
            'notes'         => 'nullable|string|max:500',
        ]);

        $type      = $request->type;
        $direction = ($type === 'return') ? 'in' : 'out';
        $quantity  = (float) $request->quantity;
        $unitCost  = (float) $consumable->units_per_pack > 0
            ? (float) $consumable->pack_cost / (float) $consumable->units_per_pack : 0;

        ConsumableStockMovement::create([
            'consumable_id' => $consumable->id,
            'user_id'       => Auth::id(),
            'type'          => $type,
            'direction'     => $direction,
            'quantity'      => $quantity,
            'packs'         => null,
            'unit_cost'     => $unitCost,
            'total_cost'    => $quantity * $unitCost,
            'movement_date' => $request->movement_date,
            'reference'     => $request->reference,
            'notes'         => $request->notes,
        ]);

        $label = match($type) {
            'return'     => 'Returned',
            'adjustment' => 'Adjusted',
            default      => 'Issued',
        };

        return redirect()->route('consumables.show', $consumable)
            ->with('success', "{$label}: {$quantity} {$consumable->use_unit}(s) of {$consumable->name}.");
    }

    // ── Delete a single movement ──────────────────────────────────────────
    public function deleteMovement(Consumable $consumable, ConsumableStockMovement $movement)
    {
        abort_if($movement->consumable_id !== $consumable->id, 404);
        $movement->delete();

        return redirect()->route('consumables.show', $consumable)
            ->with('success', 'Stock movement deleted.');
    }
}
