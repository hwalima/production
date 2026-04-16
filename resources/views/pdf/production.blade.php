@extends('pdf.layout')

@section('report-title', 'Monthly Production Report')
@section('report-subtitle', $filterFrom . ' – ' . $filterTo)

@section('content')

{{-- ── Summary tiles ──────────────────────────────────────── --}}
<table class="summary-grid">
    <tr>
        <td style="width:20%;">
            <div class="tile-label">Total Ore Hoisted (t)</div>
            <div class="tile-value">{{ number_format($productions->sum('ore_hoisted'), 2) }}</div>
        </td>
        <td style="width:20%;">
            <div class="tile-label">Total Waste Hoisted (t)</div>
            <div class="tile-value">{{ number_format($productions->sum('waste_hoisted'), 2) }}</div>
        </td>
        <td style="width:20%;">
            <div class="tile-label">Total Ore Crushed (t)</div>
            <div class="tile-value">{{ number_format($productions->sum('ore_crushed'), 2) }}</div>
        </td>
        <td style="width:20%;">
            <div class="tile-label">Total Ore Milled (t)</div>
            <div class="tile-value">{{ number_format($totalOre, 2) }}</div>
        </td>
        <td style="width:20%;">
            <div class="tile-label">Total Gold (g)</div>
            <div class="tile-value gold">{{ number_format($totalGold, 2) }}</div>
        </td>
    </tr>
    <tr>
        <td colspan="4">
            <div class="tile-label">Records in Period</div>
            <div class="tile-value">{{ $productions->count() }}</div>
        </td>
        <td>
            <div class="tile-label">Avg Purity (%)</div>
            <div class="tile-value">{{ number_format($avgPurity, 2) }}</div>
        </td>
    </tr>
</table>

{{-- ── Detail table ─────────────────────────────────────── --}}
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-c">Shift</th>
            <th class="th-c">Site</th>
            <th class="th-r">Ore Hoisted (t)</th>
            <th class="th-r">Hoist Tgt</th>
            <th class="th-r">Hoist Var</th>
            <th class="th-r">Waste Hoisted (t)</th>
            <th class="th-r">Uncrushed Stk (t)</th>
            <th class="th-r">Ore Crushed (t)</th>
            <th class="th-r">Unmilled Stk (t)</th>
            <th class="th-r">Ore Milled (t)</th>
            <th class="th-r">Mill Tgt</th>
            <th class="th-r">Mill Var</th>
            <th class="th-r">Gold (g)</th>
            <th class="th-r">Purity %</th>
        </tr>
    </thead>
    <tbody>
        @forelse($productions as $p)
        <tr>
            <td>{{ $p->date->format('d M Y') }}</td>
            <td class="td-c muted">{{ $p->shift ?? '—' }}</td>
            <td class="td-c muted">{{ $p->mining_site ?? '—' }}</td>
            <td class="td-r">{{ number_format($p->ore_hoisted, 2) }}</td>
            @php $hv = $p->ore_hoisted_target !== null ? (float)$p->ore_hoisted_target - (float)$p->ore_hoisted : null; @endphp
            <td class="td-r muted">{{ $p->ore_hoisted_target !== null ? number_format($p->ore_hoisted_target, 2) : '—' }}</td>
            <td class="td-r" style="color:{{ $hv === null ? '#9ca3af' : ($hv > 0 ? '#b91c1c' : '#15803d') }};font-weight:600;">
                {{ $hv === null ? '—' : (($hv > 0 ? '+' : '').number_format($hv, 2)) }}
            </td>
            <td class="td-r">{{ number_format($p->waste_hoisted, 2) }}</td>
            <td class="td-r gold">{{ number_format($p->uncrushed_stockpile, 2) }}</td>
            <td class="td-r">{{ number_format($p->ore_crushed, 2) }}</td>
            <td class="td-r gold">{{ number_format($p->unmilled_stockpile, 2) }}</td>
            <td class="td-r">{{ number_format($p->ore_milled, 2) }}</td>
            @php $mv = $p->ore_milled_target !== null ? (float)$p->ore_milled_target - (float)$p->ore_milled : null; @endphp
            <td class="td-r muted">{{ $p->ore_milled_target !== null ? number_format($p->ore_milled_target, 2) : '—' }}</td>
            <td class="td-r" style="color:{{ $mv === null ? '#9ca3af' : ($mv > 0 ? '#b91c1c' : '#15803d') }};font-weight:600;">
                {{ $mv === null ? '—' : (($mv > 0 ? '+' : '').number_format($mv, 2)) }}
            </td>
            <td class="td-r">{{ number_format($p->gold_smelted, 3) }}</td>
            <td class="td-r">{{ $p->purity_percentage }}%</td>
        </tr>
        @empty
        <tr><td colspan="11" style="text-align:center;padding:16px;color:#9ca3af;">No records found for this period.</td></tr>
        @endforelse
    </tbody>
    @if($productions->count())
    <tfoot>
        @php
            $totHoisted     = $productions->sum('ore_hoisted');
            $totHoistTgt    = $productions->whereNotNull('ore_hoisted_target')->sum('ore_hoisted_target');
            $totHoistVar    = $productions->whereNotNull('ore_hoisted_target')->count() ? $totHoistTgt - $totHoisted : null;
            $totWaste       = $productions->sum('waste_hoisted');
            $totCrushed     = $productions->sum('ore_crushed');
            $totMilled      = $productions->sum('ore_milled');
            $totMillTgt     = $productions->whereNotNull('ore_milled_target')->sum('ore_milled_target');
            $totMillVar     = $productions->whereNotNull('ore_milled_target')->count() ? $totMillTgt - $totMilled : null;
            $totGold        = $productions->sum('gold_smelted');
        @endphp
        <tr>
            <td colspan="3" style="font-weight:700;">TOTALS</td>
            <td class="td-r" style="font-weight:700;">{{ number_format($totHoisted, 2) }}</td>
            <td class="td-r">{{ $totHoistTgt ? number_format($totHoistTgt, 2) : '—' }}</td>
            <td class="td-r" style="font-weight:700;color:{{ $totHoistVar === null ? '#94a3b8' : ($totHoistVar > 0 ? '#fca5a5' : '#86efac') }}">
                {{ $totHoistVar === null ? '—' : (($totHoistVar > 0 ? '+' : '').number_format($totHoistVar, 2)) }}
            </td>
            <td class="td-r" style="font-weight:700;">{{ number_format($totWaste, 2) }}</td>
            <td class="td-r">—</td>
            <td class="td-r" style="font-weight:700;">{{ number_format($totCrushed, 2) }}</td>
            <td class="td-r">—</td>
            <td class="td-r" style="font-weight:700;">{{ number_format($totMilled, 2) }}</td>
            <td class="td-r">{{ $totMillTgt ? number_format($totMillTgt, 2) : '—' }}</td>
            <td class="td-r" style="font-weight:700;color:{{ $totMillVar === null ? '#94a3b8' : ($totMillVar > 0 ? '#fca5a5' : '#86efac') }}">
                {{ $totMillVar === null ? '—' : (($totMillVar > 0 ? '+' : '').number_format($totMillVar, 2)) }}
            </td>
            <td class="td-r" style="font-weight:700;color:#fcd34d;">{{ number_format($totGold, 2) }} g</td>
            <td class="td-r">{{ number_format($avgPurity, 2) }}%</td>
        </tr>
    </tfoot>
    @endif
</table>

@endsection
