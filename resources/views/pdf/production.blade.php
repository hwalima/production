@extends('pdf.layout')

@section('report-title', 'Monthly Production Report')
@section('report-subtitle', \Carbon\Carbon::parse($month . '-01')->format('F Y'))

@section('content')

{{-- ── Summary tiles ──────────────────────────────────────── --}}
<table class="summary-grid" style="width:75%;">
    <tr>
        <td style="width:33.33%;">
            <div class="tile-label">Total Ore Milled (t)</div>
            <div class="tile-value">{{ number_format($totalOre, 2) }}</div>
        </td>
        <td style="width:33.33%;">
            <div class="tile-label">Total Gold Smelted (kg)</div>
            <div class="tile-value gold">{{ number_format($totalGold, 3) }}</div>
        </td>
        <td style="width:33.33%;">
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
            <th class="th-r">Waste Hoisted (t)</th>
            <th class="th-r">Uncrushed Stk (t)</th>
            <th class="th-r">Ore Crushed (t)</th>
            <th class="th-r">Unmilled Stk (t)</th>
            <th class="th-r">Ore Milled (t)</th>
            <th class="th-r">Gold (kg)</th>
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
            <td class="td-r">{{ number_format($p->waste_hoisted, 2) }}</td>
            <td class="td-r gold">{{ number_format($p->uncrushed_stockpile, 2) }}</td>
            <td class="td-r">{{ number_format($p->ore_crushed, 2) }}</td>
            <td class="td-r gold">{{ number_format($p->unmilled_stockpile, 2) }}</td>
            <td class="td-r">{{ number_format($p->ore_milled, 2) }}</td>
            <td class="td-r">{{ number_format($p->gold_smelted, 3) }}</td>
            <td class="td-r">{{ $p->purity_percentage }}%</td>
        </tr>
        @empty
        <tr><td colspan="11" style="text-align:center;padding:16px;color:#9ca3af;">No records found for this period.</td></tr>
        @endforelse
    </tbody>
    @if($productions->count())
    <tfoot>
        <tr>
            <td colspan="3">Totals</td>
            <td class="td-r">{{ number_format($productions->sum('ore_hoisted'), 2) }}</td>
            <td class="td-r">{{ number_format($productions->sum('waste_hoisted'), 2) }}</td>
            <td class="td-r">—</td>
            <td class="td-r">{{ number_format($productions->sum('ore_crushed'), 2) }}</td>
            <td class="td-r">—</td>
            <td class="td-r">{{ number_format($totalOre, 2) }}</td>
            <td class="td-r">{{ number_format($totalGold, 3) }}</td>
            <td class="td-r">{{ number_format($avgPurity, 2) }}%</td>
        </tr>
    </tfoot>
    @endif
</table>

@endsection
