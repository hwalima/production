@extends('pdf.layout')

@section('report-title', 'Accounts Report')
@section('report-subtitle', $filterFrom . ' – ' . $filterTo)

@section('content')

{{-- ── Summary tiles ──────────────────────────────────────────── --}}
<table class="summary-grid">
    <tr>
        <td style="width:20%;border-top:3px solid #b45309;">
            <div class="tile-label">Gold Revenue</div>
            <div class="tile-value gold">${{ number_format($totalGoldRevenue, 2) }}</div>
            <div style="font-size:7px;color:#9ca3af;margin-top:2px;">{{ number_format($totalGoldGrams, 2) }} g smelted</div>
        </td>
        <td style="width:20%;border-top:3px solid #6b7280;">
            <div class="tile-label">Consumables Cost</div>
            <div class="tile-value">${{ number_format($totalConsumablesCost, 2) }}</div>
        </td>
        <td style="width:20%;border-top:3px solid #6b7280;">
            <div class="tile-label">Labour &amp; Energy</div>
            <div class="tile-value">${{ number_format($totalLabourCost, 2) }}</div>
        </td>
        <td style="width:20%;border-top:3px solid #b91c1c;">
            <div class="tile-label">Total Costs</div>
            <div class="tile-value" style="color:#b91c1c;">${{ number_format($totalCosts, 2) }}</div>
        </td>
        <td style="width:20%;border-top:3px solid {{ $isProfitable ? '#16a34a' : '#b91c1c' }};">
            <div class="tile-label">{{ $isProfitable ? 'Net Profit' : 'Net Loss' }}</div>
            <div class="tile-value {{ $isProfitable ? 'green' : '' }}" style="{{ $isProfitable ? '' : 'color:#b91c1c;' }}">
                {{ $isProfitable ? '' : '-' }}${{ number_format(abs($profitLoss), 2) }}
            </div>
        </td>
    </tr>
</table>

{{-- ── Cost Breakdown ──────────────────────────────────────────── --}}
<div class="section-heading">Cost Breakdown</div>
<table class="data-table" style="margin-bottom:18px;">
    <thead>
        <tr>
            <th>Category</th>
            <th class="th-r">Amount (USD)</th>
        </tr>
    </thead>
    <tbody>
        {{-- Labour & Energy section --}}
        <tr>
            <td colspan="2" style="background:#f1f5f9;font-size:7.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#475569;padding:5px 6px;">Labour &amp; Energy</td>
        </tr>
        <tr>
            <td style="padding-left:16px;">ZESA (power)</td>
            <td class="td-r">${{ number_format($labourBreakdown->zesa ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="padding-left:16px;">Diesel</td>
            <td class="td-r">${{ number_format($labourBreakdown->diesel ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="padding-left:16px;">Labour (wages)</td>
            <td class="td-r">${{ number_format($labourBreakdown->labour ?? 0, 2) }}</td>
        </tr>
        <tr style="font-weight:700;">
            <td>Labour &amp; Energy Subtotal</td>
            <td class="td-r">${{ number_format($totalLabourCost, 2) }}</td>
        </tr>

        {{-- Consumables section --}}
        <tr>
            <td colspan="2" style="background:#f1f5f9;font-size:7.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#475569;padding:5px 6px;">Consumables</td>
        </tr>
        @forelse ($consumablesBreakdown as $category => $row)
        <tr>
            <td style="padding-left:16px;">{{ ucfirst($category ?? 'Uncategorised') }}</td>
            <td class="td-r">${{ number_format($row->total, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" class="td-c" style="color:#9ca3af;font-style:italic;">No consumable usage recorded</td>
        </tr>
        @endforelse
        <tr style="font-weight:700;">
            <td>Consumables Subtotal</td>
            <td class="td-r">${{ number_format($totalConsumablesCost, 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL COSTS</td>
            <td class="td-r">${{ number_format($totalCosts, 2) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ── Gold Revenue Detail ─────────────────────────────────────── --}}
<div class="section-heading">Gold Revenue Detail</div>
<table class="data-table" style="margin-bottom:18px;">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-r">Grams</th>
            <th class="th-r">Purity %</th>
            <th class="th-r">$/g</th>
            <th class="th-r">Revenue (USD)</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($productions as $p)
        <tr>
            <td>{{ \Carbon\Carbon::parse($p->date)->format('d M Y') }}</td>
            <td class="td-r">{{ number_format($p->gold_smelted, 2) }}</td>
            <td class="td-r">{{ number_format($p->purity_percentage, 1) }}%</td>
            <td class="td-r">${{ number_format($p->fidelity_price, 2) }}</td>
            <td class="td-r gold">${{ number_format($p->gold_revenue, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="td-c" style="color:#9ca3af;font-style:italic;">No production records in this period</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="td-r">{{ number_format($totalGoldGrams, 2) }} g</td>
            <td></td>
            <td></td>
            <td class="td-r">${{ number_format($totalGoldRevenue, 2) }}</td>
        </tr>
    </tfoot>
</table>

{{-- ── Profit / Loss Banner ────────────────────────────────────── --}}
<table style="width:100%;border-collapse:collapse;border:2px solid {{ $isProfitable ? '#16a34a' : '#b91c1c' }};border-radius:6px;margin-top:6px;">
    <tr>
        <td style="padding:14px;text-align:center;">
            <div style="font-size:7.5px;color:#9ca3af;letter-spacing:.1em;text-transform:uppercase;margin-bottom:4px;">
                {{ $filterFrom }} — {{ $filterTo }}
            </div>
            <div style="font-size:8px;color:#9ca3af;margin-bottom:4px;">
                Revenue ${{ number_format($totalGoldRevenue, 2) }} &minus; Costs ${{ number_format($totalCosts, 2) }}
            </div>
            <div style="font-size:20px;font-weight:900;color:{{ $isProfitable ? '#16a34a' : '#b91c1c' }};">
                {{ $isProfitable ? 'PROFIT' : 'LOSS' }}&nbsp;{{ $isProfitable ? '' : '-' }}${{ number_format(abs($profitLoss), 2) }}
            </div>
        </td>
    </tr>
</table>

@endsection
