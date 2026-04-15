@extends('layouts.app')
@section('title', 'Add Production')
@section('page-title', 'Daily Production')
@section('content')
<div style="max-width:680px;">
    <div class="page-header">
        <h1 class="page-title">Add Daily Production</h1>
        <a href="{{ route('production.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('production.store') }}" method="POST" id="prodForm">
            @csrf

            {{-- Date, Shift, Mining Site --}}
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date</label>
                <input type="date" name="date" id="date" class="fc-input" value="{{ old('date') }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Shift</label>
                    <select name="shift" id="shift" class="fc-input">
                        <option value="">— Select shift —</option>
                        @foreach($shifts as $s)
                        <option value="{{ $s }}" {{ old('shift') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('shift')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Mining Site</label>
                    <select name="mining_site" id="mining_site" class="fc-input">
                        <option value="">— Select site —</option>
                        @foreach($miningSites as $site)
                        <option value="{{ $site }}" {{ old('mining_site') === $site ? 'selected' : '' }}>{{ $site }}</option>
                        @endforeach
                    </select>
                    @error('mining_site')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Hoisting --}}
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Ore Hoisted (t)</label>
                    <input type="number" name="ore_hoisted" id="ore_hoisted" step="0.01" class="fc-input" value="{{ old('ore_hoisted') }}" required>
                    @error('ore_hoisted')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Waste Hoisted (t)</label>
                    <input type="number" name="waste_hoisted" id="waste_hoisted" step="0.01" class="fc-input" value="{{ old('waste_hoisted') }}" required>
                    @error('waste_hoisted')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Crushing & Milling --}}
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Ore Crushed (t)</label>
                    <input type="number" name="ore_crushed" id="ore_crushed" step="0.01" class="fc-input" value="{{ old('ore_crushed') }}" required>
                    @error('ore_crushed')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Ore Milled (t)</label>
                    <input type="number" name="ore_milled" id="ore_milled" step="0.01" class="fc-input" value="{{ old('ore_milled') }}" required>
                    @error('ore_milled')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Gold --}}
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Gold Smelted (kg)</label>
                    <input type="number" name="gold_smelted" id="gold_smelted" step="0.01" class="fc-input" value="{{ old('gold_smelted') }}" required>
                    @error('gold_smelted')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Purity %</label>
                    <input type="number" name="purity_percentage" id="purity_percentage" step="0.01" min="0" max="100" class="fc-input" value="{{ old('purity_percentage') }}" required>
                    @error('purity_percentage')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Fidelity Price ($/kg)</label>
                <input type="number" name="fidelity_price" id="fidelity_price" step="0.01" class="fc-input" value="{{ old('fidelity_price') }}" required>
                @error('fidelity_price')<p class="fc-error">{{ $message }}</p>@enderror
            </div>

            {{-- Auto-Calculated (frozen / readonly) --}}
            <span class="fc-auto-label">&#9679; Auto-Calculated — Locked</span>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Uncrushed Stockpile (t)</label>
                    <input type="text" id="uncrushed_preview" class="fc-input fc-frozen" readonly
                           placeholder="Enter ore values above…" tabindex="-1">
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Prev {{ number_format($prev?->uncrushed_stockpile ?? 0, 2) }} t + Hoisted &minus; Crushed</p>
                </div>
                <div>
                    <label class="fc-label">Unmilled Stockpile (t)</label>
                    <input type="text" id="unmilled_preview" class="fc-input fc-frozen" readonly
                           placeholder="Enter ore values above…" tabindex="-1">
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Prev {{ number_format($prev?->unmilled_stockpile ?? 0, 2) }} t + Crushed &minus; Milled</p>
                </div>
                <div>
                    <label class="fc-label">Profit Calculated ($)</label>
                    <input type="text" id="profit_preview" class="fc-input fc-frozen" readonly
                           placeholder="Enter gold values above…" tabindex="-1">
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Gold &times; Price &times; Purity%</p>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Record</button>
                <a href="{{ route('production.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function() {
    const prevUncrushed = {{ $prev ? (float)$prev->uncrushed_stockpile : 0 }};
    const prevUnmilled  = {{ $prev ? (float)$prev->unmilled_stockpile  : 0 }};

    function fmt(n) { return n.toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2}); }

    function updateCalcs() {
        const hoisted = parseFloat(document.getElementById('ore_hoisted').value)       || 0;
        const crushed = parseFloat(document.getElementById('ore_crushed').value)       || 0;
        const milled  = parseFloat(document.getElementById('ore_milled').value)        || 0;
        const smelted = parseFloat(document.getElementById('gold_smelted').value)      || 0;
        const price   = parseFloat(document.getElementById('fidelity_price').value)    || 0;
        const purity  = parseFloat(document.getElementById('purity_percentage').value) || 0;

        document.getElementById('uncrushed_preview').value = fmt(prevUncrushed + hoisted - crushed) + ' t';
        document.getElementById('unmilled_preview').value  = fmt(prevUnmilled  + crushed - milled)  + ' t';
        document.getElementById('profit_preview').value    = '$' + fmt(smelted * price * purity / 100);
    }

    ['ore_hoisted','ore_crushed','ore_milled','gold_smelted','fidelity_price','purity_percentage']
        .forEach(id => document.getElementById(id)?.addEventListener('input', updateCalcs));
    updateCalcs();
})();
</script>
@endpush
@endsection
