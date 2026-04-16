@extends('layouts.app')
@section('title', 'Edit Production')
@section('page-title', 'Daily Production')
@section('content')
<div style="max-width:680px;">
    <div class="page-header">
        <h1 class="page-title">Edit Daily Production</h1>
        <a href="{{ route('production.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('production.update', $production) }}" method="POST" id="prodForm">
            @csrf
            @method('PUT')

            {{-- Date, Shift, Mining Site --}}
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date</label>
                <input type="date" name="date" id="date" class="fc-input"
                       value="{{ old('date', $production->date->format('Y-m-d')) }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Shift</label>
                    <select name="shift" id="shift" class="fc-input">
                        <option value="">— Select shift —</option>
                        @foreach($shifts as $s)
                        <option value="{{ $s }}" {{ old('shift', $production->shift) === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('shift')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Mining Site</label>
                    <select name="mining_site" id="mining_site" class="fc-input">
                        <option value="">— Select site —</option>
                        @foreach($miningSites as $site)
                        <option value="{{ $site }}" {{ old('mining_site', $production->mining_site) === $site ? 'selected' : '' }}>{{ $site }}</option>
                        @endforeach
                    </select>
                    @error('mining_site')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Hoisting --}}
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Ore Hoisted (t)</label>
                    <input type="number" name="ore_hoisted" id="ore_hoisted" step="0.01" class="fc-input"
                           value="{{ old('ore_hoisted', $production->ore_hoisted) }}" required>
                    @error('ore_hoisted')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Ore Hoisted Target (t) <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
                    <input type="number" name="ore_hoisted_target" id="ore_hoisted_target" step="0.01" class="fc-input"
                           value="{{ old('ore_hoisted_target', $production->ore_hoisted_target) }}">
                    @error('ore_hoisted_target')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Waste Hoisted (t)</label>
                    <input type="number" name="waste_hoisted" id="waste_hoisted" step="0.01" class="fc-input"
                           value="{{ old('waste_hoisted', $production->waste_hoisted) }}" required>
                    @error('waste_hoisted')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Ore Milled Target (t) <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
                    <input type="number" name="ore_milled_target" id="ore_milled_target" step="0.01" class="fc-input"
                           value="{{ old('ore_milled_target', $production->ore_milled_target) }}">
                    @error('ore_milled_target')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Gold Smelted (g)</label>
                    <input type="number" name="gold_smelted" id="gold_smelted" step="0.01" class="fc-input"
                           value="{{ old('gold_smelted', $production->gold_smelted) }}" required>
                    @error('gold_smelted')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Purity %</label>
                    <input type="number" name="purity_percentage" id="purity_percentage" step="0.01" min="0" max="100" class="fc-input"
                           value="{{ old('purity_percentage', $production->purity_percentage) }}" required>
                    @error('purity_percentage')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Fidelity Price ({{ $currencySymbol }}/g)</label>
                <input type="number" name="fidelity_price" id="fidelity_price" step="0.01" class="fc-input"
                       value="{{ old('fidelity_price', $production->fidelity_price) }}" required>
                @error('fidelity_price')<p class="fc-error">{{ $message }}</p>@enderror
            </div>

            {{-- Auto-Calculated (frozen / readonly) --}}
            <span class="fc-auto-label">&#9679; Auto-Calculated — Locked</span>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Uncrushed Stockpile (t)</label>
                    <input type="text" id="uncrushed_preview" class="fc-input fc-frozen" readonly tabindex="-1">
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Prev {{ number_format($prev?->uncrushed_stockpile ?? 0, 2) }} t + Hoisted &minus; Crushed</p>
                </div>
                <div>
                    <label class="fc-label">Unmilled Stockpile (t)</label>
                    <input type="text" id="unmilled_preview" class="fc-input fc-frozen" readonly tabindex="-1">
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Prev {{ number_format($prev?->unmilled_stockpile ?? 0, 2) }} t + Crushed &minus; Milled</p>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Record</button>
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
    }

    ['ore_hoisted','ore_crushed','ore_milled','gold_smelted','fidelity_price','purity_percentage']
        .forEach(id => document.getElementById(id)?.addEventListener('input', updateCalcs));
    updateCalcs();
})();
</script>
@endpush
@endsection
