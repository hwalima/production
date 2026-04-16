@extends('layouts.app')
@section('title', 'SHE Requirements')
@section('page-title', 'SHE')
@section('content')

@php
$catColors = [
    'she'         => '#22c55e',
    'mining'      => '#ef4444',
    'engineering' => '#3b82f6',
    'plant'       => '#a855f7',
];
@endphp

<div style="max-width:900px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">SHE Requirements</h1>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:2px;">
                <a href="{{ route('she.index', ['period' => $period]) }}" style="color:#fcb913;">SHE Report</a>
                &rsaquo; Edit Requirements
            </p>
        </div>
        <a href="{{ route('she.index', ['period' => $period]) }}" class="btn-cancel">&larr; Back</a>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,.1);border:1px solid #22c55e;color:#22c55e;border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:.82rem;">
        {{ session('success') }}
    </div>
    @endif

    {{-- ══ ADD ITEM FORM (admin+) ══ --}}
    @if(auth()->user()->isAdminOrAbove())
    <div class="form-card" style="margin-bottom:24px;">
        <p style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#fcb913;margin-bottom:12px;">● Add Requirement Item</p>
        <form method="POST" action="{{ route('she.items.store') }}"
              style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            @csrf
            <div style="min-width:140px;">
                <label class="fc-label">Category <span style="color:#ef4444;">*</span></label>
                <select name="category" class="fc-input" required>
                    @foreach($catLabels as $cat => $catLabel)
                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $catLabel }}</option>
                    @endforeach
                </select>
                @error('category')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="flex:2;min-width:180px;">
                <label class="fc-label">Item Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="fc-input"
                       placeholder="e.g. First Aid Kits Checked" required>
                @error('name')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="min-width:130px;">
                <label class="fc-label">Unit of Measure</label>
                <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure') }}"
                       class="fc-input" placeholder="e.g. Count, Hours">
            </div>
            <div style="min-width:70px;">
                <label class="fc-label">Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                       class="fc-input" min="0" style="width:70px;">
            </div>
            <div>
                <button type="submit" class="btn-add" style="padding:9px 18px;font-size:.82rem;">+ Add</button>
            </div>
        </form>
    </div>
    @endif

    {{-- ══ ENTRY FORM (values for selected period) ══ --}}
    <div class="form-card">
        <form method="POST" action="{{ route('she.requirements.store') }}">
            @csrf

            <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,.07);">
                <label class="fc-label" style="margin:0;white-space:nowrap;">Reporting Period:</label>
                <input type="month" name="period" value="{{ old('period', $period) }}"
                       class="fc-input" style="width:180px;" required>
            </div>

            @forelse($groupedItems as $cat => $items)
            <div style="margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <div style="width:3px;height:14px;background:{{ $catColors[$cat] ?? '#9ca3af' }};border-radius:2px;flex-shrink:0;"></div>
                    <span style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:{{ $catColors[$cat] ?? '#9ca3af' }};">
                        {{ $catLabels[$cat] }}
                    </span>
                </div>

                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left;padding:6px 10px;font-size:.7rem;font-weight:700;text-transform:uppercase;color:#6b7280;border-bottom:1px solid rgba(255,255,255,.06);">Item</th>
                            <th style="padding:6px 10px;font-size:.7rem;font-weight:700;text-transform:uppercase;color:#6b7280;border-bottom:1px solid rgba(255,255,255,.06);width:150px;">Unit of Measure</th>
                            <th style="padding:6px 10px;font-size:.7rem;font-weight:700;text-transform:uppercase;color:#6b7280;border-bottom:1px solid rgba(255,255,255,.06);width:110px;text-align:center;">Unit</th>
                            <th style="padding:6px 10px;font-size:.7rem;font-weight:700;text-transform:uppercase;color:#6b7280;border-bottom:1px solid rgba(255,255,255,.06);">Notes</th>
                            @if(auth()->user()->isAdminOrAbove())
                            <th style="width:40px;border-bottom:1px solid rgba(255,255,255,.06);"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php $entry = $entries[$item->id] ?? null; @endphp
                        <tr style="{{ $loop->even ? 'background:rgba(255,255,255,.02);' : '' }}">
                            <td style="padding:7px 10px;font-size:.82rem;font-weight:600;">{{ $item->name }}</td>
                            <td style="padding:7px 10px;font-size:.78rem;color:#9ca3af;">{{ $item->unit_of_measure ?: '—' }}</td>
                            <td style="padding:7px 8px;text-align:center;">
                                <input type="number"
                                       name="entries[{{ $item->id }}][unit_value]"
                                       value="{{ old("entries.{$item->id}.unit_value", $entry?->unit_value) }}"
                                       step="0.01" min="0" placeholder="—"
                                       style="width:90px;background:var(--input-bg);border:1px solid var(--topbar-border);border-radius:6px;
                                              padding:5px 7px;color:var(--text);font-size:.82rem;text-align:center;"
                                       onfocus="this.style.borderColor='{{ $catColors[$cat] ?? '#fcb913' }}'"
                                       onblur="this.style.borderColor='var(--topbar-border)'">
                            </td>
                            <td style="padding:7px 8px;">
                                <input type="text"
                                       name="entries[{{ $item->id }}][notes]"
                                       value="{{ old("entries.{$item->id}.notes", $entry?->notes) }}"
                                       maxlength="255" placeholder="Optional notes"
                                       style="width:100%;background:var(--input-bg);border:1px solid var(--topbar-border);border-radius:6px;
                                              padding:5px 8px;color:var(--text);font-size:.78rem;">
                            </td>
                            @if(auth()->user()->isAdminOrAbove())
                            <td style="padding:7px 6px;text-align:center;">
                                <form method="POST" action="{{ route('she.items.destroy', $item) }}"
                                      onsubmit="event.preventDefault();confirmDelete('Delete «{{ addslashes($item->name) }}»?',this)"
                                      style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn act-delete" title="Delete item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @empty
            <p style="text-align:center;color:#6b7280;padding:24px;font-size:.85rem;">
                No requirement items yet. Add items using the form above.
            </p>
            @endforelse

            @if($groupedItems->isNotEmpty())
            <div class="form-actions" style="margin-top:8px;">
                <button type="submit" class="btn-submit">Save Requirements</button>
                <a href="{{ route('she.index', ['period' => $period]) }}" class="btn-cancel">Cancel</a>
            </div>
            @endif
        </form>
    </div>

</div>
@endsection
