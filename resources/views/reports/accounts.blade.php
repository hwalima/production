@extends('layouts.app')
@section('title', 'Accounts Report')
@section('page-title', 'Reports')
@section('content')

<div class="page-header">
    <h1 class="page-title">Accounts Report</h1>
    <div class="fbar-ctrl" style="display:flex;gap:8px;align-items:center;">
        <form method="GET" style="display:contents;">
            <label style="font-size:.75rem;color:#9ca3af;">From</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="fc-input" style="width:auto;">
            <label style="font-size:.75rem;color:#9ca3af;">To</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="fc-input" style="width:auto;">
            <button class="btn-add">Filter</button>
        </form>
    </div>
</div>

{{-- ── Summary Tiles ─────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px;">

    <div class="data-card" style="padding:16px;text-align:center;border-top:3px solid #d97706;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Gold Revenue</div>
        <div style="font-size:1.4rem;font-weight:700;color:#f59e0b;">
            ${{ number_format($totalGoldRevenue, 2) }}
        </div>
        <div style="font-size:.7rem;color:#6b7280;margin-top:4px;">{{ number_format($totalGoldGrams, 2) }} g smelted</div>
    </div>

    <div class="data-card" style="padding:16px;text-align:center;border-top:3px solid #6b7280;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Consumables Cost</div>
        <div style="font-size:1.4rem;font-weight:700;color:var(--text);">
            ${{ number_format($totalConsumablesCost, 2) }}
        </div>
        <div style="font-size:.7rem;color:#6b7280;margin-top:4px;">usage issues in period</div>
    </div>

    <div class="data-card" style="padding:16px;text-align:center;border-top:3px solid #6b7280;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Labour &amp; Energy</div>
        <div style="font-size:1.4rem;font-weight:700;color:var(--text);">
            ${{ number_format($totalLabourCost, 2) }}
        </div>
        <div style="font-size:.7rem;color:#6b7280;margin-top:4px;">ZESA + diesel + wages</div>
    </div>

    <div class="data-card" style="padding:16px;text-align:center;border-top:3px solid #dc2626;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Total Costs</div>
        <div style="font-size:1.4rem;font-weight:700;color:#f87171;">
            ${{ number_format($totalCosts, 2) }}
        </div>
        <div style="font-size:.7rem;color:#6b7280;margin-top:4px;">consumables + labour</div>
    </div>

    @php $isProfitable = $profitLoss >= 0; @endphp
    <div class="data-card" style="padding:16px;text-align:center;border-top:3px solid {{ $isProfitable ? '#16a34a' : '#dc2626' }};">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">{{ $isProfitable ? 'Net Profit' : 'Net Loss' }}</div>
        <div style="font-size:1.5rem;font-weight:800;color:{{ $isProfitable ? '#4ade80' : '#f87171' }};">
            {{ $isProfitable ? '' : '-' }}${{ number_format(abs($profitLoss), 2) }}
        </div>
        <div style="font-size:.7rem;color:#6b7280;margin-top:4px;">
            {{ $from->format('d M') }} – {{ $to->format('d M Y') }}
        </div>
    </div>

</div>

{{-- ── Two-column breakdown ──────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">

    {{-- Cost Breakdown --}}
    <div>
        <div class="data-card">
            <div style="padding:12px 16px 8px;font-weight:600;font-size:.85rem;color:#9ca3af;letter-spacing:.05em;text-transform:uppercase;">
                Cost Breakdown
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th style="text-align:right;">Amount (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Labour & Energy rows --}}
                    <tr>
                        <td colspan="2" style="padding:6px 12px;font-size:.7rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;background:rgba(0,0,0,.1);">
                            Labour &amp; Energy
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left:24px;">ZESA (power)</td>
                        <td style="text-align:right;">${{ number_format($labourBreakdown->zesa ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left:24px;">Diesel</td>
                        <td style="text-align:right;">${{ number_format($labourBreakdown->diesel ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left:24px;">Labour (wages)</td>
                        <td style="text-align:right;">${{ number_format($labourBreakdown->labour ?? 0, 2) }}</td>
                    </tr>
                    <tr style="font-weight:600;">
                        <td>Labour &amp; Energy Subtotal</td>
                        <td style="text-align:right;">${{ number_format($totalLabourCost, 2) }}</td>
                    </tr>

                    {{-- Consumables rows --}}
                    <tr>
                        <td colspan="2" style="padding:6px 12px;font-size:.7rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;background:rgba(0,0,0,.1);">
                            Consumables
                        </td>
                    </tr>
                    @forelse ($consumablesBreakdown as $category => $row)
                        <tr>
                            <td style="padding-left:24px;">{{ ucfirst($category ?? 'Uncategorised') }}</td>
                            <td style="text-align:right;">${{ number_format($row->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;color:#6b7280;font-style:italic;">No consumable usage recorded</td>
                        </tr>
                    @endforelse
                    <tr style="font-weight:600;">
                        <td>Consumables Subtotal</td>
                        <td style="text-align:right;">${{ number_format($totalConsumablesCost, 2) }}</td>
                    </tr>

                    {{-- Grand total --}}
                    <tr style="font-weight:700;font-size:1rem;background:rgba(220,38,38,.1);color:#f87171;">
                        <td>TOTAL COSTS</td>
                        <td style="text-align:right;">${{ number_format($totalCosts, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Gold Revenue Detail --}}
    <div>
        <div class="data-card">
            <div style="padding:12px 16px 8px;font-weight:600;font-size:.85rem;color:#9ca3af;letter-spacing:.05em;text-transform:uppercase;">
                Gold Revenue Detail
            </div>
            <div class="tbl-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th style="text-align:right;">Grams</th>
                            <th style="text-align:right;">Purity %</th>
                            <th style="text-align:right;">$/g</th>
                            <th style="text-align:right;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productions as $p)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($p->date)->format('d M Y') }}</td>
                                <td style="text-align:right;">{{ number_format($p->gold_smelted, 2) }}</td>
                                <td style="text-align:right;">{{ number_format($p->purity_percentage, 1) }}%</td>
                                <td style="text-align:right;">${{ number_format($p->fidelity_price, 2) }}</td>
                                <td style="text-align:right;color:#f59e0b;">${{ number_format($p->gold_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;color:#6b7280;font-style:italic;">No production records in this period</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:700;color:#f59e0b;">
                            <td>TOTAL</td>
                            <td style="text-align:right;">{{ number_format($totalGoldGrams, 2) }} g</td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;">${{ number_format($totalGoldRevenue, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── Profit / Loss Banner ──────────────────────────────────────── --}}
<div class="data-card" style="padding:24px;text-align:center;border:2px solid {{ $isProfitable ? '#16a34a' : '#dc2626' }};">
    <div style="font-size:.8rem;color:#9ca3af;letter-spacing:.1em;text-transform:uppercase;margin-bottom:8px;">
        {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
    </div>
    <div style="font-size:.9rem;color:#9ca3af;margin-bottom:4px;">
        Revenue ${{ number_format($totalGoldRevenue, 2) }} &minus; Costs ${{ number_format($totalCosts, 2) }}
    </div>
    <div style="font-size:2.5rem;font-weight:800;color:{{ $isProfitable ? '#4ade80' : '#f87171' }};">
        {{ $isProfitable ? 'PROFIT' : 'LOSS' }}&nbsp;
        {{ $isProfitable ? '' : '-' }}${{ number_format(abs($profitLoss), 2) }}
    </div>
</div>

@endsection
