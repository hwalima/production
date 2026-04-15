@extends('layouts.app')
@section('title', 'Edit ' . $consumable->name)
@section('page-title', 'Stores')
@section('content')
<div style="max-width:700px;">
    <div class="page-header">
        <h1 class="page-title">Edit: {{ $consumable->name }}</h1>
        <a href="{{ route('consumables.show', $consumable) }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('consumables.update', $consumable) }}" method="POST">
            @csrf @method('PUT')

            <h2 class="fc-section">Item Details</h2>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div style="grid-column:1/-1;">
                    <label class="fc-label">Item Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" class="fc-input"
                           value="{{ old('name', $consumable->name) }}" required>
                    @error('name')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Category <span style="color:#ef4444;">*</span></label>
                    <select name="category" class="fc-input" required>
                        @foreach(['blasting'=>'🧨 Blasting','chemicals'=>'⚗️ Chemicals','mechanical'=>'🔧 Mechanical','ppe'=>'🦺 PPE','general'=>'📦 General'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ old('category',$consumable->category)===$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Description</label>
                    <input type="text" name="description" class="fc-input"
                           value="{{ old('description', $consumable->description) }}">
                    @error('description')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <h2 class="fc-section">Purchase &amp; Use Units</h2>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Purchase Unit <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="purchase_unit" class="fc-input"
                           value="{{ old('purchase_unit', $consumable->purchase_unit) }}"
                           list="purchase-unit-list" required>
                    <datalist id="purchase-unit-list">
                        @foreach(['box','drum','litre','kg','roll','bag','carton','each','set','pair','crate','pallet'] as $u)
                        <option value="{{ $u }}">
                        @endforeach
                    </datalist>
                    @error('purchase_unit')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Use Unit <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="use_unit" class="fc-input"
                           value="{{ old('use_unit', $consumable->use_unit) }}"
                           list="use-unit-list" required>
                    <datalist id="use-unit-list">
                        @foreach(['fuse','litre','kg','each','piece','tablet','gram','metre','sheet','pair'] as $u)
                        <option value="{{ $u }}">
                        @endforeach
                    </datalist>
                    @error('use_unit')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Units per Pack <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="units_per_pack" id="unitsPerPack" class="fc-input"
                           value="{{ old('units_per_pack', $consumable->units_per_pack) }}"
                           step="any" min="0.0001" oninput="calcUnitCost()" required>
                    @error('units_per_pack')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Pack Cost ({{ $currencySymbol }}) <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="pack_cost" id="packCost" class="fc-input"
                           value="{{ old('pack_cost', $consumable->pack_cost) }}"
                           step="0.01" min="0" oninput="calcUnitCost()" required>
                    @error('pack_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div style="background:rgba(252,185,19,.08);border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px;">
                <span style="font-size:.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;">Unit Cost</span>
                <span id="unitCostDisplay" style="font-size:1.4rem;font-weight:800;color:#fcb913;">{{ $currencySymbol }}{{ number_format($consumable->unit_cost, 4) }}</span>
                <span style="font-size:.78rem;color:#9ca3af;">per {{ $consumable->use_unit }} (auto-calculated)</span>
            </div>

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Reorder Level</label>
                    <input type="number" name="reorder_level" class="fc-input"
                           value="{{ old('reorder_level', $consumable->reorder_level) }}"
                           step="any" min="0">
                    <p style="font-size:.68rem;color:#9ca3af;margin-top:3px;">In {{ $consumable->use_unit }}s. 0 = no alert.</p>
                    @error('reorder_level')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Changes</button>
                <a href="{{ route('consumables.show', $consumable) }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const SYM = '{{ $currencySymbol }}';
function calcUnitCost() {
    const packs = parseFloat(document.getElementById('unitsPerPack').value) || 0;
    const cost  = parseFloat(document.getElementById('packCost').value)     || 0;
    const unit  = packs > 0 ? cost / packs : 0;
    document.getElementById('unitCostDisplay').textContent = SYM + unit.toFixed(4);
}
</script>
@endpush
@endsection
