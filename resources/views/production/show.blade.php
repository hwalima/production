@extends('layouts.app')
@section('title', 'Production Details')
@section('page-title', 'Daily Production')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Production Details</h1>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('production.edit', $production) }}" class="btn-add" style="padding:7px 14px;font-size:.8rem;">Edit</a>
            <a href="{{ route('production.index') }}" class="btn-cancel">&larr; Back</a>
        </div>
    </div>
    <div class="detail-card">
        <div class="detail-row"><span class="dr-label">Date</span><span class="dr-value">{{ $production->date->format('d M Y') }}</span></div>
        @if($production->shift)
        @php
            $sc = ['Day'=>'#f59e0b','Night'=>'#6366f1','Afternoon'=>'#10b981','Morning'=>'#38bdf8'];
            $bc = $sc[$production->shift] ?? '#9ca3af';
        @endphp
        <div class="detail-row">
            <span class="dr-label">Shift</span>
            <span class="dr-value">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:.75rem;font-weight:700;background:{{ $bc }}22;color:{{ $bc }};border:1px solid {{ $bc }}44;">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $bc }};"></span>{{ $production->shift }}
                </span>
            </span>
        </div>
        @endif
        @if($production->mining_site)
        <div class="detail-row"><span class="dr-label">Mining Site</span><span class="dr-value">{{ $production->mining_site }}</span></div>
        @endif
        <div class="detail-row"><span class="dr-label">Ore Hoisted</span><span class="dr-value">{{ number_format($production->ore_hoisted, 2) }} t</span></div>
        @if($production->ore_hoisted_target !== null)
        @php $hv = (float)$production->ore_hoisted_target - (float)$production->ore_hoisted; @endphp
        <div class="detail-row"><span class="dr-label">Hoist Target</span><span class="dr-value" style="color:#9ca3af;">{{ number_format($production->ore_hoisted_target, 2) }} t</span></div>
        <div class="detail-row"><span class="dr-label">Hoist Variance</span>
            <span class="dr-value" style="color:{{ $hv > 0 ? '#ef4444' : '#22c55e' }};font-weight:700;">{{ ($hv > 0 ? '+' : '').number_format($hv, 2) }} t</span>
        </div>
        @endif
        <div class="detail-row"><span class="dr-label">Waste Hoisted</span><span class="dr-value">{{ number_format($production->waste_hoisted, 2) }} t</span></div>
        <div class="detail-row" style="background:rgba(252,185,19,.06);">
            <span class="dr-label" style="color:#fcb913;">Uncrushed Stockpile</span>
            <span class="dr-value" style="color:#fcb913;">{{ number_format($production->uncrushed_stockpile, 2) }} t</span>
        </div>
        <div class="detail-row"><span class="dr-label">Ore Crushed</span><span class="dr-value">{{ number_format($production->ore_crushed, 2) }} t</span></div>
        <div class="detail-row" style="background:rgba(252,185,19,.06);">
            <span class="dr-label" style="color:#fcb913;">Unmilled Stockpile</span>
            <span class="dr-value" style="color:#fcb913;">{{ number_format($production->unmilled_stockpile, 2) }} t</span>
        </div>
        <div class="detail-row"><span class="dr-label">Ore Milled</span><span class="dr-value">{{ number_format($production->ore_milled, 2) }} t</span></div>
        @if($production->ore_milled_target !== null)
        @php $mv = (float)$production->ore_milled_target - (float)$production->ore_milled; @endphp
        <div class="detail-row"><span class="dr-label">Mill Target</span><span class="dr-value" style="color:#9ca3af;">{{ number_format($production->ore_milled_target, 2) }} t</span></div>
        <div class="detail-row"><span class="dr-label">Mill Variance</span>
            <span class="dr-value" style="color:{{ $mv > 0 ? '#ef4444' : '#22c55e' }};font-weight:700;">{{ ($mv > 0 ? '+' : '').number_format($mv, 2) }} t</span>
        </div>
        @endif
        <div class="detail-row"><span class="dr-label">Gold Smelted</span><span class="dr-value">{{ number_format($production->gold_smelted, 2) }} g</span></div>
        <div class="detail-row"><span class="dr-label">Purity</span><span class="dr-value">{{ $production->purity_percentage }}%</span></div>
        <div class="detail-row"><span class="dr-label">Fidelity Price</span><span class="dr-value">{{ $currencySymbol }}{{ number_format($production->fidelity_price, 2) }}/g</span></div>
    </div>
</div>
@endsection
