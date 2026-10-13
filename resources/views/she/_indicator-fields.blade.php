<style>
    .she-form-wrap { max-width:1080px; }
    .she-meta-grid { margin-bottom:22px; }
    .she-indicator-heading {
        margin-bottom:14px;
        padding-bottom:10px;
        border-bottom:1px solid var(--topbar-border);
        color:#6b7280;
        font-size:.72rem;
        font-weight:700;
        letter-spacing:.08em;
        text-transform:uppercase;
    }
    .she-indicator-heading span {
        font-weight:400;
        letter-spacing:0;
        text-transform:none;
    }
    .she-indicator-grid {
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
        margin-bottom:22px;
    }
    .she-indicator-card {
        min-width:0;
        padding:14px;
        border:1px solid var(--topbar-border);
        border-radius:12px;
        background:var(--bg);
    }
    .she-indicator-card h2 {
        margin:0 0 12px;
        color:var(--text);
        font-size:.88rem;
        font-weight:650;
        line-height:1.35;
    }
    .she-indicator-fields {
        display:grid;
        grid-template-columns:minmax(100px,.38fr) minmax(0,1fr);
        align-items:start;
        gap:12px;
    }
    .she-indicator-card .fc-label { display:block; margin-bottom:6px; font-size:.75rem; }
    .she-indicator-card textarea.fc-input { min-height:72px; resize:vertical; }
    @media(max-width:680px) {
        .she-form-wrap { max-width:100%; }
        .she-indicator-grid { grid-template-columns:1fr; gap:10px; }
    }
    @media(max-width:420px) {
        .she-indicator-fields { grid-template-columns:1fr; gap:8px; }
    }
</style>

<div class="she-indicator-heading">
    Indicators <span>(leave each value blank for 0)</span>
</div>
<div class="she-indicator-grid">
    @foreach($indicatorLabels as $field => $label)
    <section class="she-indicator-card" aria-labelledby="she-indicator-{{ $field }}">
        <h2 id="she-indicator-{{ $field }}">{{ $label }}</h2>
        <div class="she-indicator-fields">
            <div>
                <label class="fc-label" for="she-value-{{ $field }}">Value</label>
                <input id="she-value-{{ $field }}" type="number" name="{{ $field }}" step="1" min="0"
                       class="fc-input"
                       value="{{ old($field, $indicator && $indicator->{$field} > 0 ? $indicator->{$field} : '') }}"
                       placeholder="0">
                @error($field)<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="fc-label" for="she-description-{{ $field }}">Description</label>
                <textarea id="she-description-{{ $field }}" name="{{ $field }}_description"
                          class="fc-input" rows="2" maxlength="5000"
                          placeholder="Optional details">{{ old($field . '_description', $indicator ? $indicator->{$field . '_description'} : '') }}</textarea>
                @error($field . '_description')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>
    @endforeach
</div>
