@extends('pdf.layout')

@section('report-title', 'SHE Indicators Report')
@section('report-subtitle', $filterFrom . ' – ' . $filterTo)

@section('content')

{{-- ── Summary tiles ──────────────────────────────────────────── --}}
<table class="summary-grid">
    <tr>
        <td style="width:20%;border-top:3px solid #001a4d;">
            <div class="tile-label">Total Records</div>
            <div class="tile-value">{{ number_format($records->count()) }}</div>
            <div style="font-size:7px;color:#9ca3af;margin-top:2px;">indicator entries</div>
        </td>
        <td style="width:20%;border-top:3px solid #b91c1c;">
            <div class="tile-label">Total Incidents</div>
            <div class="tile-value" style="color:#b91c1c;">{{ number_format($totalIncidents) }}</div>
            <div style="font-size:7px;color:#9ca3af;margin-top:2px;">MIC + Fatal + LTI + NLTI</div>
        </td>
        <td style="width:20%;border-top:3px solid #b91c1c;">
            <div class="tile-label">Fatalities</div>
            <div class="tile-value" style="color:{{ $totals['fatal_incident'] > 0 ? '#b91c1c' : '#16a34a' }};">
                {{ number_format($totals['fatal_incident']) }}
            </div>
        </td>
        <td style="width:20%;border-top:3px solid #b45309;">
            <div class="tile-label">LTI Count</div>
            <div class="tile-value gold">{{ number_format($totals['lti']) }}</div>
        </td>
        <td style="width:20%;border-top:3px solid #6b7280;">
            <div class="tile-label">Environmental Incidents &amp; Penalties</div>
            <div class="tile-value">{{ number_format($totalEnvironmental) }}</div>
        </td>
    </tr>
</table>

{{-- ── Department Summary ──────────────────────────────────────── --}}
@if($deptSummary->count() > 1)
<div class="section-heading">Summary by Department</div>
<table class="data-table" style="margin-bottom:18px;font-size:7.5px;">
    <thead>
        <tr>
            <th>Department</th>
            <th class="th-c">Med. Injury</th>
            <th class="th-c">Fatal</th>
            <th class="th-c">LTI</th>
            <th class="th-c">NLTI</th>
            <th class="th-c">IOD</th>
            <th class="th-c">Environmental Incidents &amp; Penalties</th>
        </tr>
    </thead>
    <tbody>
        @foreach($deptSummary as $dept => $row)
        <tr>
            <td style="font-weight:600;">{{ $dept }}</td>
            @foreach($indicatorLabels as $field => $label)
            <td class="td-c">{{ $row[$field] > 0 ? number_format($row[$field]) : '-' }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            @foreach($indicatorLabels as $field => $label)
            <td class="td-c">{{ $totals[$field] > 0 ? number_format($totals[$field]) : '-' }}</td>
            @endforeach
        </tr>
    </tfoot>
</table>
@endif

{{-- ── Detail Records ──────────────────────────────────────────── --}}
<div class="section-heading">Indicator Records</div>
<table class="data-table" style="font-size:7.5px;">
    <thead>
        <tr>
            <th>Date</th>
            <th>Department</th>
            <th class="th-c">Med. Injury</th>
            <th class="th-c">Fatal</th>
            <th class="th-c">LTI</th>
            <th class="th-c">NLTI</th>
            <th class="th-c">IOD</th>
            <th class="th-c">Environmental Incidents &amp; Penalties</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $rec)
        <tr>
            <td style="white-space:nowrap;font-weight:600;">{{ $rec->date->format('d M Y') }}</td>
            <td>{{ $rec->department?->name ?? '—' }}</td>
            @foreach($indicatorLabels as $field => $label)
            <td class="td-c">
                {{ $rec->{$field} > 0 ? number_format($rec->{$field}) : '-' }}
                @if($rec->{$field . '_description'})
                <div style="margin-top:2px;color:#6b7280;font-size:6px;text-align:left;">{{ $rec->{$field . '_description'} }}</div>
                @endif
            </td>
            @endforeach
        </tr>
        @empty
        <tr>
            <td colspan="{{ 2 + count($indicatorLabels) }}" class="td-c" style="color:#9ca3af;font-style:italic;">No SHE indicator records for this period.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">TOTALS</td>
            @foreach($indicatorLabels as $field => $label)
            <td class="td-c">{{ $totals[$field] > 0 ? number_format($totals[$field]) : '-' }}</td>
            @endforeach
        </tr>
    </tfoot>
</table>

@endsection
