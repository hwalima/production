@extends('layouts.app')
@section('title', 'Add Consumable')
@section('page-title', 'Stores')
@section('content')
<div style="max-width:700px;">
    <div class="page-header">
        <h1 class="page-title">Add Store Item</h1>
        <a href="{{ route('consumables.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('consumables.store') }}" method="POST">
            @csrf

            {{-- Basic info --}}
            <h2 class="fc-section">Item Details</h2>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div class="sm:col-span-2" style="grid-column:1/-1;">
                    <label class="fc-label">Item Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" class="fc-input" value="{{ old('name') }}"
                           placeholder="e.g. Safety Fuse, ANFO, Cyanide" required>
                    @error('name')<p class="fc-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="fc-label">Category <span style="color:#ef4444;">*</span></label>
                    <select name="category" class="fc-input" required>
                        <option value="">— Select —</option>
                        @foreach(['blasting'=>'🧨 Blasting','chemicals'=>'⚗️ Chemicals','mechanical'=>'🔧 Mechanical','ppe'=>'🦺 PPE','general'=>'📦 General'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ old('category')===$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="fc-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="fc-label">Description</label>
                    <input type="text" name="description" class="fc-input" value="{{ old('description') }}"
                           placeholder="Optional notes about this item">
                    @error('description')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Unit structure --}}
            <h2 class="fc-section">Purchase &amp; Use Units</h2>
            <div style="background:rgba(252,185,19,.06);border:1px solid rgba(252,185,19,.2);border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:.78rem;color:#9ca3af;line-height:1.6;">
                <strong style="color:#fcb913;">Example:</strong>
                Safety fuses are sold in a <em>Box</em> of <em>100 fuses</em> costing <em>{{ $currencySymbol }}100</em>.
                The system will calculate each <em>fuse</em> costs <em>{{ $currencySymbol }}1.00</em>.
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Purchase Unit <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="purchase_unit" class="fc-input" value="{{ old('purchase_unit') }}"
                           placeholder="box, drum, litre, kg, roll, bag, carton, each"
                           list="purchase-unit-list" required>
                    <datalist id="purchase-unit-list">
                        @foreach(['box','drum','litre','kg','roll','bag','carton','each','set','pair','crate','pallet'] as $u)
                        <option value="{{ $u }}">
                        @endforeach
                    </datalist>
                    <p style="font-size:.68rem;color:#9ca3af;margin-top:3px;">How is it sold? (the unit you buy)</p>
                    @error('purchase_unit')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Use Unit <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="use_unit" class="fc-input" value="{{ old('use_unit') }}"
                           placeholder="fuse, litre, kg, each, piece, tablet"
                           list="use-unit-list" required>
                    <datalist id="use-unit-list">
                        @foreach(['fuse','litre','kg','each','piece','tablet','gram','metre','sheet','pair'] as $u)
                        <option value="{{ $u }}">
                        @endforeach
                    </datalist>
                    <p style="font-size:.68rem;color:#9ca3af;margin-top:3px;">Smallest unit you consume / issue</p>
                    @error('use_unit')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Units per Pack <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="units_per_pack" id="unitsPerPack" class="fc-input"
                           value="{{ old('units_per_pack', 1) }}" step="any" min="0.0001"
                           oninput="calcUnitCost()" required>
                    <p style="font-size:.68rem;color:#9ca3af;margin-top:3px;">How many <em>use units</em> in one <em>purchase unit</em>?</p>
                    @error('units_per_pack')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Pack Cost ({{ $currencySymbol }}) <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="pack_cost" id="packCost" class="fc-input"
                           value="{{ old('pack_cost', 0) }}" step="0.01" min="0"
                           oninput="calcUnitCost()" required>
                    <p style="font-size:.68rem;color:#9ca3af;margin-top:3px;">Cost of one purchase unit</p>
                    @error('pack_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Computed unit cost display --}}
            <div style="background:rgba(252,185,19,.08);border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px;">
                <span style="font-size:.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;">Unit Cost</span>
                <span id="unitCostDisplay" style="font-size:1.4rem;font-weight:800;color:#fcb913;">{{ $currencySymbol }}0.0000</span>
                <span style="font-size:.78rem;color:#9ca3af;">per use unit (auto-calculated)</span>
            </div>

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Reorder Level</label>
                    <input type="number" name="reorder_level" class="fc-input"
                           value="{{ old('reorder_level', 0) }}" step="any" min="0">
                    <p style="font-size:.68rem;color:#9ca3af;margin-top:3px;">Alert when stock falls to this quantity (in use units). 0 = no alert.</p>
                    @error('reorder_level')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Add to Catalog</button>
                <a href="{{ route('consumables.index') }}" class="btn-cancel">Cancel</a>
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
calcUnitCost();
</script>
@endpush
@endsection
