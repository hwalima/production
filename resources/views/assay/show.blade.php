@extends('layouts.app')
@section('title', 'Assay Result')
@section('page-title', 'Assay Results')
@section('content')
@php
$typeLabels = ['fire_assay' => 'Fire Assay', 'gold_on_carbon' => 'Gold on Carbon', 'bottle_roll' => 'Bottle Roll'];
$typeColors = ['fire_assay' => '#ef4444', 'gold_on_carbon' => '#fcb913', 'bottle_roll' => '#3b82f6'];
$tabKeys    = ['fire_assay' => 'fire', 'gold_on_carbon' => 'goc', 'bottle_roll' => 'bottle'];
$color      = $typeColors[$assay->type] ?? '#9ca3af';
@endphp
<div style="max-width:560px;">
    <div class="page-header">
        <h1 class="page-title">Assay Result</h1>
        <div style="display:flex;gap:8px;align-items:center;">
            @if(auth()->user()->canWrite())
            <a href="{{ route('assay.edit', $assay) }}" class="btn-add" style="padding:7px 14px;font-size:.8rem;">Edit</a>
            @endif
            <a href="{{ route('assay.index', ['tab' => $tabKeys[$assay->type] ?? 'fire']) }}" class="btn-cancel">&larr; Back</a>
        </div>
    </div>
    <div class="detail-card">
        <div class="detail-row">
            <span class="dr-label">Type</span>
            <span class="dr-value">
                <span style="background:{{ $color }};color:#fff;border-radius:20px;padding:2px 12px;font-size:.78rem;font-weight:700;">
                    {{ $typeLabels[$assay->type] ?? $assay->type }}
                </span>
            </span>
        </div>
        <div class="detail-row"><span class="dr-label">Date</span><span class="dr-value">{{ $assay->date->format('d M Y') }}</span></div>
        <div class="detail-row" style="background:rgba(255,255,255,.04);">
            <span class="dr-label" style="color:{{ $color }};">Assay Value</span>
            <span class="dr-value" style="color:{{ $color }};font-size:1.1rem;font-weight:700;">{{ number_format($assay->assay_value, 4) }} g/t</span>
        </div>
        <div class="detail-row"><span class="dr-label">Description</span><span class="dr-value">{{ $assay->description ?: '—' }}</span></div>
        <div class="detail-row"><span class="dr-label">Recorded</span><span class="dr-value">{{ $assay->created_at->format('d M Y, H:i') }}</span></div>
    </div>
</div>
@endsection
