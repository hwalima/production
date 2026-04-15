@extends('layouts.app')
@section('title', 'Production Report')
@section('page-title', 'Reports')
@section('content')
<div class="page-header">
    <h1 class="page-title">Monthly Production Report</h1>
    <div class="fbar-ctrl" style="display:flex;gap:8px;align-items:center;">
        <form method="GET" style="display:contents;">
            <input type="month" name="month" value="{{ $month }}" class="fc-input" style="width:auto;">
            <button class="btn-add">Filter</button>
        </form>
        <a href="{{ route('reports.production.pdf', ['month' => $month]) }}"
           class="btn-add"
           style="background:#b45309;border-color:#b45309;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            &#8675; Export PDF
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px;">
    <div class="data-card" style="padding:16px;text-align:center;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Total Ore Milled</div>
        <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ number_format($totalOre, 2) }}</div>
    </div>
    <div class="data-card" style="padding:16px;text-align:center;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Total Gold Smelted</div>
        <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ number_format($totalGold, 2) }}</div>
    </div>
    <div class="data-card" style="padding:16px;text-align:center;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Avg Purity %</div>
        <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ number_format($avgPurity, 2) }}</div>
    </div>
    <div class="data-card" style="padding:16px;text-align:center;">
        <div style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">Total Profit</div>
        <div style="font-size:1.4rem;font-weight:700;color:#22c55e;">${{ number_format($totalProfit, 2) }}</div>
    </div>
</div>

<div class="data-card">
    <div class="tbl-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Mining Site</th>
                    <th>Ore Hoisted</th>
                    <th>Waste Hoisted</th>
                    <th style="background:rgba(0,0,0,.18);">&#x3A3; Uncrushed Stk</th>
                    <th>Ore Crushed</th>
                    <th style="background:rgba(0,0,0,.18);">&#x3A3; Unmilled Stk</th>
                    <th>Ore Milled</th>
                    <th>Gold Smelted</th>
                    <th>Purity %</th>
                    <th>Fidelity Price</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productions as $p)
                <tr>
                    <td>{{ $p->date->format('d M Y') }}</td>
                    <td class="text-center">{{ $p->shift ?? '—' }}</td>
                    <td class="text-center">{{ $p->mining_site ?? '—' }}</td>
                    <td class="text-center">{{ number_format($p->ore_hoisted, 2) }}</td>
                    <td class="text-center">{{ number_format($p->waste_hoisted, 2) }}</td>
                    <td class="text-center" style="color:#fcb913;font-weight:600;">{{ number_format($p->uncrushed_stockpile, 2) }}</td>
                    <td class="text-center">{{ number_format($p->ore_crushed, 2) }}</td>
                    <td class="text-center" style="color:#fcb913;font-weight:600;">{{ number_format($p->unmilled_stockpile, 2) }}</td>
                    <td class="text-center">{{ number_format($p->ore_milled, 2) }}</td>
                    <td class="text-center">{{ number_format($p->gold_smelted, 3) }}</td>
                    <td class="text-center">{{ $p->purity_percentage }}%</td>
                    <td class="text-center">${{ number_format($p->fidelity_price, 2) }}</td>
                    <td class="text-center" style="font-weight:600;color:#22c55e;">${{ number_format($p->profit_calculated, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="13" style="text-align:center;padding:32px;color:#9ca3af;">No records found for this month.</td></tr>
                @endforelse
            </tbody>
            @if($productions->count())
            <tfoot>
                <tr style="font-weight:700;">
                    <td>Totals</td>
                    <td class="text-center">—</td>
                    <td class="text-center">—</td>
                    <td class="text-center">{{ number_format($productions->sum('ore_hoisted'), 2) }}</td>
                    <td class="text-center">{{ number_format($productions->sum('waste_hoisted'), 2) }}</td>
                    <td class="text-center">—</td>
                    <td class="text-center">{{ number_format($productions->sum('ore_crushed'), 2) }}</td>
                    <td class="text-center">—</td>
                    <td class="text-center">{{ number_format($totalOre, 2) }}</td>
                    <td class="text-center">{{ number_format($totalGold, 2) }}</td>
                    <td class="text-center">{{ number_format($avgPurity, 2) }}%</td>
                    <td class="text-center">—</td>
                    <td class="text-center" style="color:#22c55e;">${{ number_format($totalProfit, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
