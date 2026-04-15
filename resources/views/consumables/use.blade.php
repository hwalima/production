@extends('layouts.app')
@section('title', 'Issue / Use — ' . $consumable->name)
@section('page-title', 'Stores')
@section('content')
<div style="max-width:520px;">
    <div class="page-header">
        <h1 class="page-title">Issue / Use Stock</h1>
        <a href="{{ route('consumables.show', $consumable) }}" class="btn-cancel">&larr; Back</a>
    </div>

    {{-- Current stock summary --}}
    @php
        $outOfStock = $stock <= 0;
        $lowStock   = !$outOfStock && $consumable->reorder_level > 0 && $stock <= $consumable->reorder_level;
    @endphp
    <div style="background:{{ $outOfStock ? 'rgba(239,68,68,.06)' : ($lowStock ? 'rgba(245,158,11,.06)' : 'rgba(34,197,94,.04)') }};
                border:2px solid {{ $outOfStock ? '#ef4444' : ($lowStock ? '#f59e0b' : '#22c55e') }};
                border-radius:12px;padding:16px 20px;margin-bottom:20px;text-align:center;">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
                    color:{{ $outOfStock ? '#dc2626' : ($lowStock ? '#d97706' : '#16a34a') }};margin-bottom:4px;">
            {{ $outOfStock ? '❌ OUT OF STOCK' : ($lowStock ? '⚠️ LOW STOCK' : '✅ In Stock') }}
        </div>
        <div style="font-size:2rem;font-weight:900;color:{{ $outOfStock ? '#dc2626' : ($lowStock ? '#d97706' : 'var(--text)') }};">
            {{ number_format($stock, $stock==intval($stock)?0:2) }}
            <span style="font-size:.9rem;font-weight:600;color:#9ca3af;">{{ $consumable->use_unit }}s available</span>
        </div>
        <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">
            {{ $consumable->name }} &nbsp;·&nbsp; unit cost: {{ $currencySymbol }}{{ number_format($unitCost, 4) }}
        </div>
    </div>

    <div class="form-card">
        <form action="{{ route('consumables.use', $consumable) }}" method="POST">
            @csrf

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Quantity ({{ $consumable->use_unit }}s) <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="quantity" id="quantityInput" class="fc-input"
                           value="{{ old('quantity') }}" step="any" min="0.0001"
                           oninput="calcCost()" required autofocus
                           {{ $outOfStock ? 'placeholder="No stock available"' : '' }}>
                    @error('quantity')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Movement Type <span style="color:#ef4444;">*</span></label>
                    <select name="type" id="typeSelect" class="fc-input" required onchange="updateType()">
                        <option value="usage"      {{ old('type')==='usage'      ?'selected':'' }}>⛏ Usage (normal consumption)</option>
                        <option value="adjustment" {{ old('type')==='adjustment' ?'selected':'' }}>📋 Adjustment (stock correction)</option>
                        <option value="return"     {{ old('type')==='return'     ?'selected':'' }}>↩ Return to Stock</option>
                    </select>
                    @error('type')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Cost estimate --}}
            <div id="costBanner" style="background:rgba(252,185,19,.08);border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;gap:24px;flex-wrap:wrap;align-items:center;">
                <div>
                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;" id="costLabel">Estimated cost</div>
                    <div id="costDisplay" style="font-size:1.4rem;font-weight:800;color:#fcb913;">{{ $currencySymbol }}0.00</div>
                </div>
                <div id="remainingBlock">
                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;">Remaining after</div>
                    <div id="remainingDisplay" style="font-size:1.4rem;font-weight:800;color:var(--text);">{{ number_format($stock, 0) }}</div>
                    <div style="font-size:.72rem;color:#9ca3af;">{{ $consumable->use_unit }}s</div>
                </div>
            </div>

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="movement_date" class="fc-input"
                           value="{{ old('movement_date', date('Y-m-d')) }}" required>
                    @error('movement_date')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Job / Area Reference</label>
                    <input type="text" name="reference" class="fc-input"
                           value="{{ old('reference') }}" placeholder="Stope 6, blast ref, work order…">
                    @error('reference')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div style="grid-column:1/-1;">
                    <label class="fc-label">Notes</label>
                    <input type="text" name="notes" class="fc-input"
                           value="{{ old('notes') }}" placeholder="Optional notes">
                    @error('notes')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit" id="submitBtn">Confirm Issue</button>
                <a href="{{ route('consumables.show', $consumable) }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const UC    = {{ (float)$unitCost }};
const STOCK = {{ (float)$stock }};
const SYM   = '{{ $currencySymbol }}';

function updateType() {
    const t = document.getElementById('typeSelect').value;
    const isReturn = t === 'return';
    document.getElementById('costLabel').textContent      = isReturn ? 'Value being returned' : 'Estimated cost';
    document.getElementById('remainingBlock').style.display = isReturn ? 'none' : '';
    document.getElementById('submitBtn').textContent      = isReturn ? 'Confirm Return' : 'Confirm Issue';
    calcCost();
}

function calcCost() {
    const qty  = parseFloat(document.getElementById('quantityInput').value) || 0;
    const cost = qty * UC;
    document.getElementById('costDisplay').textContent = SYM + cost.toFixed(2);

    const t = document.getElementById('typeSelect').value;
    if (t !== 'return') {
        const rem = STOCK - qty;
        document.getElementById('remainingDisplay').textContent =
            (rem < 0 ? '−' : '') + Math.abs(rem).toFixed(rem == Math.floor(rem) ? 0 : 2);
        document.getElementById('remainingDisplay').style.color = rem < 0 ? '#ef4444' : 'var(--text)';
    }
}
updateType();
</script>
@endpush
@endsection
