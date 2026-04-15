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
        <div class="detail-row"><span class="dr-label">Shift</span><span class="dr-value">{{ $production->shift }}</span></div>
        @endif
        @if($production->mining_site)
        <div class="detail-row"><span class="dr-label">Mining Site</span><span class="dr-value">{{ $production->mining_site }}</span></div>
        @endif
        <div class="detail-row"><span class="dr-label">Ore Hoisted</span><span class="dr-value">{{ number_format($production->ore_hoisted, 2) }} t</span></div>
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
        <div class="detail-row"><span class="dr-label">Gold Smelted</span><span class="dr-value">{{ number_format($production->gold_smelted, 3) }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Purity</span><span class="dr-value">{{ $production->purity_percentage }}%</span></div>
        <div class="detail-row"><span class="dr-label">Fidelity Price</span><span class="dr-value">${{ number_format($production->fidelity_price, 2) }}/kg</span></div>
    </div>
</div>
@endsection
