@extends('layouts.app')
@section('title', 'SHE Report — ' . $periodDate->format('F Y'))
@section('page-title', 'SHE')
@section('content')

@php
// ── Build row totals, column totals, grand total ──────────────────
$rowTotals = [];
$colTotals = array_fill_keys($departments, 0.0);
$grandTotal = 0.0;

foreach (array_keys($indicatorLabels) as $field) {
    $rowTotal = 0.0;
    foreach ($departments as $dept) {
        $val = (float)($indicators[$dept]?->{$field} ?? 0);
        $rowTotal           += $val;
        $colTotals[$dept]   += $val;
    }
    $rowTotals[$field] = $rowTotal;
    $grandTotal        += $rowTotal;
}
@endphp

<div class="page-header">
    <h1 class="page-title">SHE Report</h1>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <form method="GET" action="{{ route('she.index') }}" style="display:flex;gap:6px;align-items:center;">
            <input type="month" name="period" value="{{ $period }}" class="fc-input" style="width:160px;padding:6px 10px;">
            <button type="submit" class="btn-add" style="padding:7px 14px;font-size:.8rem;">View</button>
        </form>
        @if(auth()->user()->canWrite())
        <a href="{{ route('she.indicators.edit', ['period' => $period]) }}"
           style="padding:7px 14px;font-size:.8rem;background:rgba(252,185,19,.12);color:#fcb913;border:1px solid rgba(252,185,19,.4);border-radius:8px;text-decoration:none;font-weight:600;">
            ✎ Edit Indicators
        </a>
        <a href="{{ route('she.requirements.edit', ['period' => $period]) }}"
           style="padding:7px 14px;font-size:.8rem;background:rgba(34,197,94,.1);color:#22c55e;border:1px solid rgba(34,197,94,.4);border-radius:8px;text-decoration:none;font-weight:600;">
            ✎ Edit Requirements
        </a>
        @endif
    </div>
</div>

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid #22c55e;color:#22c55e;border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:.82rem;">
    {{ session('success') }}
</div>
@endif

<p style="font-size:.78rem;color:#9ca3af;margin-bottom:20px;">
    Period: <strong style="color:#fcb913;">{{ $periodDate->format('F Y') }}</strong>
</p>

{{-- ══════════════════ SHE INDICATORS ══════════════════ --}}
<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
    <div style="width:3px;height:18px;background:#fcb913;border-radius:2px;"></div>
    <h2 style="font-size:.82rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#fcb913;margin:0;">SHE INDICATORS</h2>
</div>

<div class="data-card" style="margin-bottom:28px;">
    <div class="tbl-scroll">
    <table class="data-table" style="min-width:640px;">
        <thead>
            <tr>
                <th style="min-width:160px;"></th>
                @foreach($departments as $dept)
                <th class="th-c" style="white-space:nowrap;font-size:.75rem;letter-spacing:.04em;text-transform:uppercase;">
                    {{ $deptLabels[$dept] }}
                </th>
                @endforeach
                <th class="th-r" style="background:rgba(252,185,19,.08);color:#fcb913;font-size:.75rem;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap;">
                    TOTALS
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($indicatorLabels as $field => $label)
            @php $rowTotal = $rowTotals[$field]; @endphp
            <tr>
                <td style="font-size:.82rem;font-weight:600;">{{ $label }}</td>
                @foreach($departments as $dept)
                @php $val = (float)($indicators[$dept]?->{$field} ?? 0); @endphp
                <td class="td-c" style="{{ $val > 0 ? 'color:#fcb913;font-weight:700;' : 'color:#374151;' }}">
                    {{ $val > 0 ? number_format($val, 2) : '-' }}
                </td>
                @endforeach
                <td class="td-r" style="font-weight:700;{{ $rowTotal > 0 ? 'color:#fcb913;background:rgba(252,185,19,.04);' : 'color:#374151;' }}">
                    {{ $rowTotal > 0 ? number_format($rowTotal, 2) : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:rgba(252,185,19,.06);border-top:2px solid rgba(252,185,19,.3);">
                <td style="font-weight:700;font-size:.78rem;color:#9ca3af;"></td>
                @foreach($departments as $dept)
                <td class="td-c" style="font-weight:700;font-size:.9rem;color:var(--text);">
                    {{ $colTotals[$dept] > 0 ? number_format($colTotals[$dept], 2) : '' }}
                </td>
                @endforeach
                <td class="td-r" style="font-weight:800;font-size:1rem;color:#fcb913;">
                    {{ $grandTotal > 0 ? number_format($grandTotal, 2) : '-' }}
                </td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

{{-- ══════════════════ REQUIREMENTS ══════════════════ --}}
@php
$catColors = [
    'she'         => '#22c55e',
    'mining'      => '#ef4444',
    'engineering' => '#3b82f6',
    'plant'       => '#a855f7',
];
@endphp

@foreach($catLabels as $cat => $catLabel)
@if(isset($requirementItems[$cat]) && $requirementItems[$cat]->count())

<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
    <div style="width:3px;height:18px;background:{{ $catColors[$cat] ?? '#9ca3af' }};border-radius:2px;"></div>
    <h2 style="font-size:.82rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:{{ $catColors[$cat] ?? '#9ca3af' }};margin:0;">{{ strtoupper($catLabel) }}</h2>
</div>

<div class="data-card" style="margin-bottom:24px;">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th style="width:180px;">Unit of Measure</th>
                <th class="th-r" style="width:100px;">Unit</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requirementItems[$cat] as $item)
            @php $entry = $entries[$item->id] ?? null; @endphp
            <tr>
                <td style="font-weight:600;font-size:.82rem;">{{ $item->name }}</td>
                <td style="color:#9ca3af;font-size:.78rem;">{{ $item->unit_of_measure ?: '—' }}</td>
                <td class="td-r" style="{{ $entry && $entry->unit_value !== null ? 'color:'.($catColors[$cat] ?? '#fcb913').';font-weight:700;' : 'color:#374151;' }}">
                    {{ $entry && $entry->unit_value !== null ? number_format($entry->unit_value, 2) : '-' }}
                </td>
                <td style="color:#9ca3af;font-size:.78rem;">{{ $entry?->notes ?: '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

@endif
@endforeach

@if($requirementItems->isEmpty())
<div style="padding:32px;text-align:center;color:#6b7280;font-size:.85rem;background:var(--card);border-radius:12px;margin-top:8px;">
    No requirement items configured yet.
    @if(auth()->user()->canWrite())
    <a href="{{ route('she.requirements.edit', ['period' => $period]) }}" style="color:#22c55e;margin-left:4px;">Add items →</a>
    @endif
</div>
@endif

@endsection
