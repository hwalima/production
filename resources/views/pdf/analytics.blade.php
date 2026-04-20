@extends('pdf.layout')

@section('report-title', 'Analytics Report')
@section('report-subtitle', \Carbon\Carbon::parse($from)->format('d M Y') . ' – ' . \Carbon\Carbon::parse($to)->format('d M Y'))

@section('content')

{{-- ── Summary tiles ────────────────────────────────────────────── --}}
<table class="summary-grid">
    <tr>
        <td style="border-top:3px solid #b45309;">
            <div class="tile-label">Gold Smelted</div>
            <div class="tile-value gold">{{ number_format($totalGoldSmelted, 2) }} g</div>
        </td>
        <td style="border-top:3px solid #0369a1;">
            <div class="tile-label">Ore Milled</div>
            <div class="tile-value" style="color:#0369a1;">{{ number_format($totalOreMilled, 0) }} t</div>
        </td>
        <td style="border-top:3px solid {{ $avgMillRecovery !== null && $avgMillRecovery >= 85 ? '#16a34a' : '#b91c1c' }};">
            <div class="tile-label">Avg Mill Recovery</div>
            <div class="tile-value {{ $avgMillRecovery !== null && $avgMillRecovery >= 85 ? 'green' : '' }}" style="{{ $avgMillRecovery !== null && $avgMillRecovery < 85 ? 'color:#b91c1c;' : '' }}">
                {{ $avgMillRecovery !== null ? $avgMillRecovery.'%' : 'N/A' }}
            </div>
        </td>
        <td style="border-top:3px solid #6b7280;">
            <div class="tile-label">Total Costs</div>
            <div class="tile-value">${{ $totalAllCosts > 0 ? number_format($totalAllCosts, 0) : 'N/A' }}</div>
        </td>
        <td style="border-top:3px solid #6b7280;">
            <div class="tile-label">AISC per gram</div>
            <div class="tile-value">{{ $avgAisc !== null ? '$'.number_format($avgAisc,2) : 'N/A' }}</div>
        </td>
        <td style="border-top:3px solid #6b7280;">
            <div class="tile-label">Cost per tonne</div>
            <div class="tile-value">{{ $avgCostPerTonne !== null ? '$'.number_format($avgCostPerTonne,2) : 'N/A' }}</div>
        </td>
    </tr>
</table>

{{-- ── 1. Mill Recovery --}}
@if($hasAssayData && count($recoveryTrendData) > 0)
<div class="section-heading">1. Mill Recovery % (Daily)</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-r">Gold Smelted (g)</th>
            <th class="th-r">Ore Milled (t)</th>
            <th class="th-r">Fire Assay (g/t)</th>
            <th class="th-r">Recovery %</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prodByDayPdf as $row)
        @php
            $grade    = (float)($fireAssayByDatePdf[$row->day] ?? 0);
            $recovery = ($grade > 0 && (float)$row->milled > 0)
                ? round(((float)$row->gold / ((float)$row->milled * $grade)) * 100, 1)
                : null;
        @endphp
        <tr>
            <td>{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</td>
            <td class="td-r">{{ number_format((float)$row->gold, 3) }}</td>
            <td class="td-r">{{ number_format((float)$row->milled, 1) }}</td>
            <td class="td-r">{{ $grade > 0 ? $grade : '—' }}</td>
            <td class="td-r" style="{{ $recovery !== null && $recovery < 75 ? 'color:#b91c1c;font-weight:700;' : ($recovery !== null && $recovery >= 85 ? 'color:#16a34a;font-weight:700;' : '') }}">
                {{ $recovery !== null ? $recovery.'%' : '—' }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Average / Total</td>
            <td class="td-r">{{ number_format($totalGoldSmelted, 3) }} g</td>
            <td class="td-r">{{ number_format($totalOreMilled, 1) }} t</td>
            <td class="td-r">—</td>
            <td class="td-r">{{ $avgMillRecovery !== null ? $avgMillRecovery.'%' : '—' }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── 2. AISC + 4. Cost per Tonne --}}
@if(count($aiscLabels) > 0)
<div class="section-heading">2. AISC per Gram &amp; 4. Cost per Tonne Milled (Monthly)</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Month</th>
            <th class="th-r">AISC ($/g)</th>
            <th class="th-r">Cost/Tonne ($)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($aiscLabels as $i => $lbl)
        <tr>
            <td>{{ $lbl }}</td>
            <td class="td-r">{{ $aiscData[$i] !== null ? '$'.number_format($aiscData[$i],2) : '—' }}</td>
            <td class="td-r">{{ isset($cptData[$i]) && $cptData[$i] !== null ? '$'.number_format($cptData[$i],2) : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Period Avg</td>
            <td class="td-r">{{ $avgAisc !== null ? '$'.number_format($avgAisc,2) : '—' }}</td>
            <td class="td-r">{{ $avgCostPerTonne !== null ? '$'.number_format($avgCostPerTonne,2) : '—' }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── 3. Grade Reconciliation --}}
@if(count(array_filter($gradeRecFire)) > 0 || count(array_filter($gradeRecImplied)) > 0)
<div class="section-heading">3. Grade Reconciliation</div>
@php
    $avgFirePdf    = count(array_filter($gradeRecFire)) > 0    ? round(array_sum(array_filter($gradeRecFire))    / count(array_filter($gradeRecFire)),    4) : null;
    $avgImpliedPdf = count(array_filter($gradeRecImplied)) > 0 ? round(array_sum(array_filter($gradeRecImplied)) / count(array_filter($gradeRecImplied)), 4) : null;
@endphp
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-r">Fire Assay (g/t)</th>
            <th class="th-r">Implied Grade (g/t)</th>
            <th class="th-r">Gap</th>
        </tr>
    </thead>
    <tbody>
        @foreach($gradeRecLabels as $i => $lbl)
        @php
            $fa   = $gradeRecFire[$i] ?? null;
            $imp  = $gradeRecImplied[$i] ?? null;
            $gap  = ($fa && $imp) ? round($imp - $fa, 4) : null;
        @endphp
        @if($fa !== null || $imp !== null)
        <tr>
            <td>{{ $lbl }}</td>
            <td class="td-r">{{ $fa !== null ? $fa : '—' }}</td>
            <td class="td-r">{{ $imp !== null ? $imp : '—' }}</td>
            <td class="td-r" style="{{ $gap !== null && $gap < 0 ? 'color:#b91c1c;' : ($gap !== null && $gap > 0 ? 'color:#16a34a;' : '') }}">
                {{ $gap !== null ? ($gap >= 0 ? '+' : '').$gap : '—' }}
            </td>
        </tr>
        @endif
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Period Average</td>
            <td class="td-r">{{ $avgFirePdf !== null ? $avgFirePdf : '—' }}</td>
            <td class="td-r">{{ $avgImpliedPdf !== null ? $avgImpliedPdf : '—' }}</td>
            <td class="td-r">{{ ($avgFirePdf && $avgImpliedPdf) ? ($avgImpliedPdf - $avgFirePdf >= 0 ? '+' : '').round($avgImpliedPdf - $avgFirePdf, 4) : '—' }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── 5. MoM / YTD --}}
<div class="section-heading">5. Month-over-Month &amp; Year-to-Date</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Metric</th>
            <th class="th-r">Selected Period</th>
            <th class="th-r">Prior Period ({{ \Carbon\Carbon::parse($prevFrom)->format('M Y') }})</th>
            <th class="th-r">Change</th>
            <th class="th-r">YTD ({{ \Carbon\Carbon::parse($from)->format('Y') }})</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Gold Smelted (g)</td>
            <td class="td-r">{{ number_format($totalGoldSmelted, 2) }}</td>
            <td class="td-r">{{ number_format($prevGold, 2) }}</td>
            <td class="td-r" style="{{ $momGoldDelta !== null && $momGoldDelta < 0 ? 'color:#b91c1c;font-weight:700;' : ($momGoldDelta !== null && $momGoldDelta > 0 ? 'color:#16a34a;font-weight:700;' : '') }}">
                {{ $momGoldDelta !== null ? ($momGoldDelta >= 0 ? '+' : '').$momGoldDelta.'%' : '—' }}
            </td>
            <td class="td-r">{{ number_format($ytdGold, 2) }}</td>
        </tr>
        <tr>
            <td>Ore Milled (t)</td>
            <td class="td-r">{{ number_format($totalOreMilled, 0) }}</td>
            <td class="td-r">{{ number_format($prevMilled, 0) }}</td>
            <td class="td-r" style="{{ $momMilledDelta !== null && $momMilledDelta < 0 ? 'color:#b91c1c;font-weight:700;' : ($momMilledDelta !== null && $momMilledDelta > 0 ? 'color:#16a34a;font-weight:700;' : '') }}">
                {{ $momMilledDelta !== null ? ($momMilledDelta >= 0 ? '+' : '').$momMilledDelta.'%' : '—' }}
            </td>
            <td class="td-r">{{ number_format($ytdMilled, 0) }}</td>
        </tr>
        <tr>
            <td>Total Costs ($)</td>
            <td class="td-r">{{ $totalAllCosts > 0 ? '$'.number_format($totalAllCosts,0) : '—' }}</td>
            <td class="td-r">{{ $prevCosts > 0 ? '$'.number_format($prevCosts,0) : '—' }}</td>
            <td class="td-r" style="{{ $momCostDelta !== null && $momCostDelta > 0 ? 'color:#b91c1c;font-weight:700;' : ($momCostDelta !== null && $momCostDelta < 0 ? 'color:#16a34a;font-weight:700;' : '') }}">
                {{ $momCostDelta !== null ? ($momCostDelta >= 0 ? '+' : '').$momCostDelta.'%' : '—' }}
            </td>
            <td class="td-r">—</td>
        </tr>
    </tbody>
</table>

{{-- ── 6. Stockpile --}}
@if(count($stockLabels) > 0)
<div class="section-heading">6. Stockpile Balance</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-r">Uncrushed (t)</th>
            <th class="th-r">Unmilled (t)</th>
            <th class="th-r">Total Buffer (t)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stockLabels as $i => $lbl)
        <tr>
            <td>{{ $lbl }}</td>
            <td class="td-r">{{ number_format($stockUncrushed[$i], 1) }}</td>
            <td class="td-r">{{ number_format($stockUnmilled[$i], 1) }}</td>
            <td class="td-r">{{ number_format($stockUncrushed[$i] + $stockUnmilled[$i], 1) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Latest</td>
            <td class="td-r">{{ number_format($latestUncrushed, 1) }} t</td>
            <td class="td-r">{{ number_format($latestUnmilled, 1) }} t</td>
            <td class="td-r">{{ number_format($latestUncrushed + $latestUnmilled, 1) }} t</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── 7. Blasting --}}
@if(count($blastLabels) > 0)
<div class="section-heading">7. Blasting Consumables</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-r">ANFO (kg)</th>
            <th class="th-r">Oil (L)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($blastLabels as $i => $lbl)
        <tr>
            <td>{{ $lbl }}</td>
            <td class="td-r">{{ number_format($blastAnfo[$i], 1) }}</td>
            <td class="td-r">{{ number_format($blastOil[$i], 1) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Totals</td>
            <td class="td-r">{{ number_format($totalAnfo, 1) }} kg</td>
            <td class="td-r">{{ number_format($totalOil, 1) }} L</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── 8. SHE --}}
<div class="section-heading">8. Safety Health Environment (SHE)</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Indicator</th>
            <th class="th-r">Count</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fatalities</td>
            <td class="td-r" style="{{ $totalFatal > 0 ? 'color:#b91c1c;font-weight:700;' : 'color:#16a34a;' }}">{{ $totalFatal }}</td>
            <td style="{{ $totalFatal > 0 ? 'color:#b91c1c;font-weight:700;' : 'color:#16a34a;' }}">{{ $totalFatal > 0 ? 'CRITICAL' : 'Clear' }}</td>
        </tr>
        <tr>
            <td>Lost Time Injuries (LTI)</td>
            <td class="td-r" style="{{ $totalLti > 0 ? 'color:#b91c1c;font-weight:700;' : 'color:#16a34a;' }}">{{ $totalLti }}</td>
            <td style="{{ $totalLti > 0 ? 'color:#b91c1c;font-weight:700;' : 'color:#16a34a;' }}">{{ $totalLti > 0 ? 'Incidents' : 'Clear' }}</td>
        </tr>
        <tr>
            <td>Non-LTI</td>
            <td class="td-r">{{ $totalNlti }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Medical Cases</td>
            <td class="td-r">{{ $totalMedical }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Sick Days</td>
            <td class="td-r">{{ $totalSick }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Leave Days</td>
            <td class="td-r">{{ $totalLeave }}</td>
            <td></td>
        </tr>
        <tr>
            <td>AWOL</td>
            <td class="td-r" style="{{ $totalAwol > 0 ? 'color:#b45309;font-weight:700;' : '' }}">{{ $totalAwol }}</td>
            <td></td>
        </tr>
        <tr style="font-weight:700;">
            <td>Total Absence Days</td>
            <td class="td-r">{{ $totalAbsence }}</td>
            <td></td>
        </tr>
    </tbody>
</table>

{{-- ── 9. Consumables burn rate --}}
@if($burnByCategory->count() > 0)
<div class="section-heading">9. Consumables Burn Rate by Category</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Category</th>
            <th class="th-r">Total Cost ($)</th>
            <th class="th-r">Total Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($burnByCategory as $cat)
        <tr>
            <td>{{ ucfirst($cat->category) }}</td>
            <td class="td-r">${{ number_format($cat->total_cost, 2) }}</td>
            <td class="td-r">{{ number_format($cat->total_qty, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Total</td>
            <td class="td-r">${{ number_format($burnByCategory->sum('total_cost'), 2) }}</td>
            <td class="td-r">—</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── 10. Drill metres --}}
@if(count($drillLabels) > 0)
<div class="section-heading">10. Drill Metres</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-r">Advance (m)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($drillLabels as $i => $lbl)
        <tr>
            <td>{{ $lbl }}</td>
            <td class="td-r">{{ number_format($drillAdvance[$i], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Total / Avg per day</td>
            <td class="td-r">{{ number_format($totalAdvance, 2) }} m &nbsp;(avg {{ $avgAdvPerDay }} m/day)</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ── 11. SPC summary --}}
@if(count($spcLabels) > 1)
<div class="section-heading">11. SPC Control Limits — Implied Gold Grade</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Metric</th>
            <th class="th-r">Value</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>Mean Grade (x̄)</td><td class="td-r">{{ round($spcMean, 4) }} g/t</td></tr>
        <tr><td>Std Deviation (σ)</td><td class="td-r">{{ round($spcStd, 4) }}</td></tr>
        <tr><td>Upper Control Limit (UCL, +2σ)</td><td class="td-r">{{ $spcUcl }} g/t</td></tr>
        <tr><td>Lower Control Limit (LCL, −2σ)</td><td class="td-r">{{ $spcLcl }} g/t</td></tr>
    </tbody>
</table>
@if(count(array_filter($spcValues, fn($v) => $v > $spcUcl || $v < $spcLcl)) > 0)
<table class="data-table" style="margin-top:6px;">
    <thead>
        <tr><th>Date</th><th class="th-r">Grade (g/t)</th><th>Status</th></tr>
    </thead>
    <tbody>
        @foreach($spcLabels as $i => $lbl)
        @if($spcValues[$i] > $spcUcl || $spcValues[$i] < $spcLcl)
        <tr>
            <td>{{ $lbl }}</td>
            <td class="td-r" style="color:#b91c1c;font-weight:700;">{{ $spcValues[$i] }}</td>
            <td style="color:#b91c1c;font-weight:700;">{{ $spcValues[$i] > $spcUcl ? 'Above UCL' : 'Below LCL' }}</td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@endif
@endif

{{-- ── 12. Predictive Maintenance --}}
@if(count($machineScores) > 0)
<div class="section-heading">12. Predictive Maintenance Health</div>
<table class="data-table">
    <thead>
        <tr>
            <th>Machine</th>
            <th>Description</th>
            <th class="th-r">Next Service</th>
            <th class="th-r">Days Left</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($machineScores as $m)
        <tr>
            <td>{{ $m['code'] }}</td>
            <td>{{ $m['description'] }}</td>
            <td class="td-r">{{ $m['next_service'] ?? '—' }}</td>
            <td class="td-r" style="{{ $m['status']==='overdue'?'color:#b91c1c;font-weight:700;':($m['status']==='due_soon'?'color:#b45309;font-weight:700;':'') }}">
                {{ $m['days_to_service'] !== null ? ($m['days_to_service'] < 0 ? abs($m['days_to_service']).' overdue' : $m['days_to_service'].' days') : '—' }}
            </td>
            <td style="{{ $m['status']==='overdue'?'color:#b91c1c;font-weight:700;':($m['status']==='due_soon'?'color:#b45309;font-weight:700;':'color:#16a34a;') }}">
                {{ $m['status']==='overdue'?'OVERDUE':($m['status']==='due_soon'?'Due Soon':'OK') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── 13. Anomalies --}}
<div class="section-heading">13. Anomaly Detection</div>
@if(count($anomalies) > 0)
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Metric</th>
            <th class="th-r">Value</th>
            <th class="th-r">Z-Score</th>
            <th>Direction</th>
        </tr>
    </thead>
    <tbody>
        @foreach($anomalies as $a)
        <tr>
            <td>{{ $a['date'] }}</td>
            <td>{{ $a['metric'] }}</td>
            <td class="td-r">{{ $a['value'] }}</td>
            <td class="td-r" style="{{ abs($a['z']) > 3 ? 'color:#b91c1c;font-weight:700;' : 'color:#16a34a;' }}">{{ $a['z'] }}</td>
            <td style="{{ $a['dir']==='above' ? 'color:#16a34a;' : 'color:#b91c1c;' }}">{{ $a['dir']==='above' ? '▲ Above' : '▼ Below' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p style="font-size:9px;color:#16a34a;font-weight:700;padding:8px 0;">✓ No anomalies detected — all production metrics within normal range.</p>
@endif

@endsection
