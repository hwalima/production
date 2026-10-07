<div class="form-card">
    <form action="{{ $action }}" method="POST" id="miningForm">
        @csrf
        @if($method === 'PUT') @method('PUT') @endif

        <div class="fc-grid" style="margin-bottom:14px;">
            <div>
                <label class="fc-label">Date <span style="color:#ef4444;">*</span></label>
                <input type="date" name="date" class="fc-input" value="{{ old('date', $miningRecord?->date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="fc-label">Shift <span style="color:#ef4444;">*</span></label>
                <select name="shift" class="fc-input" required>
                    <option value="">— Select shift —</option>
                    @foreach($shifts as $shift)
                    <option value="{{ $shift }}" {{ old('shift', $miningRecord?->shift) === $shift ? 'selected' : '' }}>{{ $shift }}</option>
                    @endforeach
                </select>
                @error('shift')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div style="margin-bottom:14px;">
            <label class="fc-label">Mining Site <span style="color:#ef4444;">*</span></label>
            <select name="mining_site" class="fc-input" required>
                <option value="">— Select site —</option>
                @foreach($miningSites as $site)
                <option value="{{ $site }}" {{ old('mining_site', $miningRecord?->mining_site) === $site ? 'selected' : '' }}>{{ $site }}</option>
                @endforeach
            </select>
            @error('mining_site')<p class="fc-error">{{ $message }}</p>@enderror
        </div>

        <div class="fc-grid" style="margin-bottom:14px;">
            <div>
                <label class="fc-label">Ore Hoisted Before Skip Factor (t) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="ore_hoisted_entered" id="ore_hoisted_entered" step="0.01" min="0" class="fc-input"
                       value="{{ old('ore_hoisted_entered', $miningRecord?->ore_hoisted_entered) }}" required>
                @error('ore_hoisted_entered')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="fc-label">Ore Hoisted Target (t) <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
                <input type="number" name="ore_hoisted_target" step="0.01" min="0" class="fc-input"
                       value="{{ old('ore_hoisted_target', $miningRecord?->ore_hoisted_target) }}">
                @error('ore_hoisted_target')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="fc-grid" style="margin-bottom:14px;">
            <div>
                <label class="fc-label">Waste Hoisted (t) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="waste_hoisted" step="0.01" min="0" class="fc-input"
                       value="{{ old('waste_hoisted', $miningRecord?->waste_hoisted) }}" required>
                @error('waste_hoisted')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="fc-label">Applied Skip Factor</label>
                <input type="text" class="fc-input" value="{{ number_format($skipFactor, 4) }}" readonly>
                <p style="font-size:.72rem;color:#6b7280;margin-top:4px;">Adjusted ore hoisted: <strong id="adjustedOre">0.00 t</strong></p>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">{{ $miningRecord ? 'Update Record' : 'Save Record' }}</button>
            <a href="{{ route('assay.index', ['tab' => 'mining']) }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
(function() {
    const input = document.getElementById('ore_hoisted_entered');
    const output = document.getElementById('adjustedOre');
    const factor = {{ $skipFactor }};
    function update() {
        const entered = Number.parseFloat(input.value) || 0;
        output.textContent = (entered * factor).toFixed(2) + ' t';
    }
    input.addEventListener('input', update);
    update();
})();
</script>
@endpush
