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
            <div class="tile-label">Total Absenteeism</div>
            <div class="tile-value">{{ number_format($totalAbsenteeism) }}</div>
            <div style="font-size:7px;color:#9ca3af;margin-top:2px;">Leave + Offdays + Sick + AWOL</div>
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
            <th class="th-c">Leave</th>
            <th class="th-c">Offdays</th>
            <th class="th-c">Sick</th>
            <th class="th-c">IOD</th>
            <th class="th-c">AWOL</th>
            <th class="th-c">Terminations</th>
        </tr>
    </thead>
    <tbody>
        @foreach($deptSummary as $dept => $row)
        <tr>
            <td style="font-weight:600;">{{ $dept }}</td>
            <td class="td-c">{{ $row['medical_injury_case'] > 0 ? number_format($row['medical_injury_case']) : '-' }}</td>
            <td class="td-c" style="{{ $row['fatal_incident'] > 0 ? 'color:#b91c1c;font-weight:700;' : '' }}">
                {{ $row['fatal_incident'] > 0 ? number_format($row['fatal_incident']) : '-' }}
            </td>
            <td class="td-c" style="{{ $row['lti'] > 0 ? 'color:#b45309;font-weight:700;' : '' }}">
                {{ $row['lti'] > 0 ? number_format($row['lti']) : '-' }}
            </td>
            <td class="td-c">{{ $row['nlti'] > 0 ? number_format($row['nlti']) : '-' }}</td>
            <td class="td-c">{{ $row['leave'] > 0 ? number_format($row['leave']) : '-' }}</td>
            <td class="td-c">{{ $row['offdays'] > 0 ? number_format($row['offdays']) : '-' }}</td>
            <td class="td-c">{{ $row['sick'] > 0 ? number_format($row['sick']) : '-' }}</td>
            <td class="td-c">{{ $row['iod'] > 0 ? number_format($row['iod']) : '-' }}</td>
            <td class="td-c">{{ $row['awol'] > 0 ? number_format($row['awol']) : '-' }}</td>
            <td class="td-c">{{ $row['terminations'] > 0 ? number_format($row['terminations']) : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="td-c">{{ $totals['medical_injury_case'] > 0 ? number_format($totals['medical_injury_case']) : '-' }}</td>
            <td class="td-c">{{ $totals['fatal_incident'] > 0 ? number_format($totals['fatal_incident']) : '-' }}</td>
            <td class="td-c">{{ $totals['lti'] > 0 ? number_format($totals['lti']) : '-' }}</td>
            <td class="td-c">{{ $totals['nlti'] > 0 ? number_format($totals['nlti']) : '-' }}</td>
            <td class="td-c">{{ $totals['leave'] > 0 ? number_format($totals['leave']) : '-' }}</td>
            <td class="td-c">{{ $totals['offdays'] > 0 ? number_format($totals['offdays']) : '-' }}</td>
            <td class="td-c">{{ $totals['sick'] > 0 ? number_format($totals['sick']) : '-' }}</td>
            <td class="td-c">{{ $totals['iod'] > 0 ? number_format($totals['iod']) : '-' }}</td>
            <td class="td-c">{{ $totals['awol'] > 0 ? number_format($totals['awol']) : '-' }}</td>
            <td class="td-c">{{ $totals['terminations'] > 0 ? number_format($totals['terminations']) : '-' }}</td>
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
            <th class="th-c">Leave</th>
            <th class="th-c">Offdays</th>
            <th class="th-c">Sick</th>
            <th class="th-c">IOD</th>
            <th class="th-c">AWOL</th>
            <th class="th-c">Terminations</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $rec)
        <tr>
            <td style="white-space:nowrap;font-weight:600;">{{ $rec->date->format('d M Y') }}</td>
            <td>{{ $rec->department?->name ?? '—' }}</td>
            <td class="td-c">{{ $rec->medical_injury_case > 0 ? number_format($rec->medical_injury_case) : '-' }}</td>
            <td class="td-c" style="{{ $rec->fatal_incident > 0 ? 'color:#b91c1c;font-weight:700;' : '' }}">
                {{ $rec->fatal_incident > 0 ? number_format($rec->fatal_incident) : '-' }}
            </td>
            <td class="td-c" style="{{ $rec->lti > 0 ? 'color:#b45309;font-weight:700;' : '' }}">
                {{ $rec->lti > 0 ? number_format($rec->lti) : '-' }}
            </td>
            <td class="td-c">{{ $rec->nlti > 0 ? number_format($rec->nlti) : '-' }}</td>
            <td class="td-c">{{ $rec->leave > 0 ? number_format($rec->leave) : '-' }}</td>
            <td class="td-c">{{ $rec->offdays > 0 ? number_format($rec->offdays) : '-' }}</td>
            <td class="td-c">{{ $rec->sick > 0 ? number_format($rec->sick) : '-' }}</td>
            <td class="td-c">{{ $rec->iod > 0 ? number_format($rec->iod) : '-' }}</td>
            <td class="td-c">{{ $rec->awol > 0 ? number_format($rec->awol) : '-' }}</td>
            <td class="td-c">{{ $rec->terminations > 0 ? number_format($rec->terminations) : '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="12" class="td-c" style="color:#9ca3af;font-style:italic;">No SHE indicator records for this period.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">TOTALS</td>
            <td class="td-c">{{ $totals['medical_injury_case'] > 0 ? number_format($totals['medical_injury_case']) : '-' }}</td>
            <td class="td-c">{{ $totals['fatal_incident'] > 0 ? number_format($totals['fatal_incident']) : '-' }}</td>
            <td class="td-c">{{ $totals['lti'] > 0 ? number_format($totals['lti']) : '-' }}</td>
            <td class="td-c">{{ $totals['nlti'] > 0 ? number_format($totals['nlti']) : '-' }}</td>
            <td class="td-c">{{ $totals['leave'] > 0 ? number_format($totals['leave']) : '-' }}</td>
            <td class="td-c">{{ $totals['offdays'] > 0 ? number_format($totals['offdays']) : '-' }}</td>
            <td class="td-c">{{ $totals['sick'] > 0 ? number_format($totals['sick']) : '-' }}</td>
            <td class="td-c">{{ $totals['iod'] > 0 ? number_format($totals['iod']) : '-' }}</td>
            <td class="td-c">{{ $totals['awol'] > 0 ? number_format($totals['awol']) : '-' }}</td>
            <td class="td-c">{{ $totals['terminations'] > 0 ? number_format($totals['terminations']) : '-' }}</td>
        </tr>
    </tfoot>
</table>

@endsection
