@extends('layouts.app')
@section('title', 'Receive Stock — ' . $consumable->name)
@section('page-title', 'Stores')
@section('content')
<div style="max-width:560px;">
    <div class="page-header">
        <h1 class="page-title">Receive Stock</h1>
        <a href="{{ route('consumables.show', $consumable) }}" class="btn-cancel">&larr; Back</a>
    </div>

    {{-- Item summary --}}
    <div style="background:rgba(34,197,94,.06);border:1px solid rgba(34,197,94,.25);border-radius:12px;padding:14px 18px;margin-bottom:20px;">
        <div style="font-weight:800;font-size:1rem;color:var(--text);">{{ $consumable->name }}</div>
        <div style="font-size:.78rem;color:#9ca3af;margin-top:4px;display:flex;gap:16px;flex-wrap:wrap;">
            <span>
                <strong style="color:var(--text);">Category:</strong>
                {{ \App\Models\Consumable::categoryLabel($consumable->category) }}
            </span>
            <span>
                <strong style="color:var(--text);">Catalog price:</strong>
                {{ $currencySymbol }}{{ number_format($consumable->pack_cost, 2) }} / {{ $consumable->purchase_unit }}
            </span>
            <span>
                <strong style="color:var(--text);">Unit cost:</strong>
                <span style="color:#fcb913;font-weight:700;">{{ $currencySymbol }}{{ number_format($unitCost, 4) }}</span>
                / {{ $consumable->use_unit }}
            </span>
            <span>
                <strong style="color:var(--text);">Current stock:</strong>
                <span style="color:{{ $stock<=0?'#ef4444':($consumable->reorder_level>0&&$stock<=$consumable->reorder_level?'#f59e0b':'#22c55e') }};font-weight:700;">
                    {{ number_format($stock, $stock==intval($stock)?0:2) }} {{ $consumable->use_unit }}s
                </span>
            </span>
        </div>
    </div>

    <div class="form-card">
        <form action="{{ route('consumables.receive', $consumable) }}" method="POST">
            @csrf

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Number of {{ ucfirst($consumable->purchase_unit) }}s received <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="packs" id="packsInput" class="fc-input"
                           value="{{ old('packs') }}" step="any" min="0.0001"
                           oninput="calcTotal()" required autofocus>
                    @error('packs')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Pack cost this delivery ({{ $currencySymbol }})</label>
                    <input type="number" name="pack_cost" id="packCostInput" class="fc-input"
                           value="{{ old('pack_cost', $consumable->pack_cost) }}"
                           step="0.01" min="0" oninput="calcTotal()">
                    <p style="font-size:.68rem;color:#9ca3af;margin-top:3px;">Leave unchanged to keep catalog price. Updating will revise all future unit costs.</p>
                    @error('pack_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Auto-total display --}}
            <div style="background:rgba(252,185,19,.08);border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;gap:24px;flex-wrap:wrap;align-items:center;">
                <div>
                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;">Units being received</div>
                    <div id="totalUnits" style="font-size:1.4rem;font-weight:800;color:#22c55e;">0</div>
                    <div style="font-size:.72rem;color:#9ca3af;">{{ $consumable->use_unit }}s</div>
                </div>
                <div>
                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;">Delivery value</div>
                    <div id="totalValue" style="font-size:1.4rem;font-weight:800;color:#fcb913;">{{ $currencySymbol }}0.00</div>
                </div>
            </div>

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Date Received <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="movement_date" class="fc-input"
                           value="{{ old('movement_date', date('Y-m-d')) }}" required>
                    @error('movement_date')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Delivery Note / Reference</label>
                    <input type="text" name="reference" class="fc-input"
                           value="{{ old('reference') }}" placeholder="DN-12345, LPO ref, etc.">
                    @error('reference')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div style="grid-column:1/-1;">
                    <label class="fc-label">Notes</label>
                    <input type="text" name="notes" class="fc-input"
                           value="{{ old('notes') }}" placeholder="Optional additional notes">
                    @error('notes')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Confirm Receipt</button>
                <a href="{{ route('consumables.show', $consumable) }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const UPP = {{ (float)$consumable->units_per_pack }};
const SYM = '{{ $currencySymbol }}';
function calcTotal() {
    const packs = parseFloat(document.getElementById('packsInput').value)    || 0;
    const cost  = parseFloat(document.getElementById('packCostInput').value) || 0;
    const units = packs * UPP;
    const value = packs * cost;
    document.getElementById('totalUnits').textContent = Number.isInteger(units) ? units : units.toFixed(2);
    document.getElementById('totalValue').textContent = SYM + value.toFixed(2);
}
</script>
@endpush
@endsection
