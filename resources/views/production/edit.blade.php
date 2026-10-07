@extends('layouts.app')
@section('title', 'Edit Plant Production')
@section('page-title', 'Plant Production')
@section('content')
<div style="max-width:680px;">
    <div class="page-header">
        <h1 class="page-title">Edit Plant Production</h1>
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
                    <label class="fc-label">Shift <span style="color:#ef4444;">*</span></label>
                    <select name="shift" id="shift" class="fc-input" required>
                        <option value="">— Select shift —</option>
                        @foreach($shifts as $s)
                        <option value="{{ $s }}" {{ old('shift', $production->shift) === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('shift')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Mining Site <span style="color:#ef4444;">*</span></label>
                    <select name="mining_site" id="mining_site" class="fc-input" required>
                        <option value="">— Select site —</option>
                        @foreach($miningSites as $site)
                        <option value="{{ $site }}" {{ old('mining_site', $production->mining_site) === $site ? 'selected' : '' }}>{{ $site }}</option>
                        @endforeach
                    </select>
                    @error('mining_site')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Plant processing --}}
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Ore Crushed (t)</label>
                    <input type="number" name="ore_crushed" id="ore_crushed" step="0.01" class="fc-input"
                           value="{{ old('ore_crushed', $production->ore_crushed) }}" required>
                    @error('ore_crushed')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Total Ore Milled (t)</label>
                    <input type="number" name="ore_milled" id="ore_milled" step="0.01" class="fc-input"
                           value="{{ old('ore_milled', $production->ore_milled) }}" required>
                    @error('ore_milled')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Total Ore Milled Target (t) <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
                    <input type="number" name="ore_milled_target" id="ore_milled_target" step="0.01" class="fc-input"
                           value="{{ old('ore_milled_target', $production->ore_milled_target) }}">
                    @error('ore_milled_target')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">R.O. Mine Milled (t)</label>
                    <input type="number" name="ro_mine_milled" id="ro_mine_milled" step="0.01" min="0" class="fc-input"
                           value="{{ old('ro_mine_milled', $production->ro_mine_milled) }}" required>
                    @error('ro_mine_milled')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Sanda Milled (t)</label>
                    <input type="number" name="sanda_milled" id="sanda_milled" step="0.01" min="0" class="fc-input"
                           value="{{ old('sanda_milled', $production->sanda_milled) }}" required>
                    <label style="display:flex;align-items:center;gap:6px;font-size:.72rem;color:#6b7280;margin-top:5px;">
                        <input type="checkbox" name="sanda_milled_manual" id="sanda_milled_manual" value="1"
                               {{ old('sanda_milled_manual', $production->sanda_milled_manual) ? 'checked' : '' }}>
                        Keep a manual Sanda value
                    </label>
                    <p style="font-size:.72rem;color:#6b7280;margin-top:4px;">Prefilled as Total Ore Milled minus R.O. Mine Milled. Check the box to keep a manual override.</p>
                    @error('sanda_milled')<p class="fc-error">{{ $message }}</p>@enderror
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
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Includes {{ number_format($mineHoistedForDate, 2) }} t hoisted for this date, minus crushed.</p>
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
    const prevUncrushed = {{ $prev ? (float)$prev->uncrushed_stockpile : 0 }} + {{ (float)$mineHoistedForDate }};
    const prevUnmilled  = {{ $prev ? (float)$prev->unmilled_stockpile  : 0 }};

    function fmt(n) { return n.toLocaleString('en', {minimumFractionDigits:2, maximumFractionDigits:2}); }

    function updateCalcs() {
        const crushed = parseFloat(document.getElementById('ore_crushed').value)       || 0;
        const milled  = parseFloat(document.getElementById('ore_milled').value)        || 0;

        document.getElementById('uncrushed_preview').value = fmt(prevUncrushed - crushed) + ' t';
        document.getElementById('unmilled_preview').value  = fmt(prevUnmilled  + crushed - milled)  + ' t';

        const total = parseFloat(document.getElementById('ore_milled').value) || 0;
        const ro = parseFloat(document.getElementById('ro_mine_milled').value) || 0;
        const sanda = document.getElementById('sanda_milled');
        const manual = document.getElementById('sanda_milled_manual');
        if (!manual.checked) sanda.value = Math.max(0, total - ro).toFixed(2);
    }

    ['ore_crushed','ore_milled','ro_mine_milled'].forEach(id => document.getElementById(id)?.addEventListener('input', updateCalcs));
    document.getElementById('sanda_milled').addEventListener('input', function() {
        document.getElementById('sanda_milled_manual').checked = true;
    });
    document.getElementById('sanda_milled_manual').addEventListener('change', updateCalcs);
    updateCalcs();
})();
</script>
@endpush
@endsection
