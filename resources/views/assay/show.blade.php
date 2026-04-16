@extends('layouts.app')
@section('title', 'Assay Result')
@section('page-title', 'Assay Results')
@section('content')
@php
$typeLabels = ['fire_assay' => 'Fire Assay', 'gold_on_carbon' => 'Gold on Carbon', 'bottle_roll' => 'Bottle Roll'];
$typeColors = ['fire_assay' => '#ef4444', 'gold_on_carbon' => '#fcb913', 'bottle_roll' => '#3b82f6'];
$tabKeys    = ['fire_assay' => 'fire', 'gold_on_carbon' => 'goc', 'bottle_roll' => 'bottle'];
$color      = $typeColors[$assay->type] ?? '#9ca3af';
$v          = (float) $assay->assay_value;
$assayFmt   = $v == floor($v) ? number_format($v, 0) : number_format($v, 2);
$dlFmt      = number_format((float)($assay->detection_limit ?? 0.01), 2);
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

    {{-- Certificate metadata --}}
    <div style="padding:10px 14px;background:rgba(255,255,255,.04);border-radius:8px;border-left:3px solid {{ $color }};
                margin-bottom:16px;display:flex;gap:20px;flex-wrap:wrap;font-size:.78rem;color:#9ca3af;">
        <span>
            <span style="text-transform:uppercase;letter-spacing:.06em;font-size:.68rem;">Analysis</span>
            <span style="color:#e5e7eb;font-weight:600;margin-left:6px;">{{ $typeLabels[$assay->type] ?? $assay->type }}</span>
        </span>
        <span>
            <span style="text-transform:uppercase;letter-spacing:.06em;font-size:.68rem;">Element</span>
            <span style="color:#e5e7eb;font-weight:600;margin-left:6px;">Gold (Au)</span>
        </span>
        <span>
            <span style="text-transform:uppercase;letter-spacing:.06em;font-size:.68rem;">Detection Limit</span>
            <span style="color:#e5e7eb;font-weight:600;margin-left:6px;">{{ $dlFmt }} g/t</span>
        </span>
    </div>

    <div class="detail-card">
        <div class="detail-row">
            <span class="dr-label">Date</span>
            <span class="dr-value" style="font-weight:600;">{{ $assay->date->format('d/m/y') }}</span>
        </div>
        <div class="detail-row" style="background:rgba(255,255,255,.04);">
            <span class="dr-label">Sample Description</span>
            <span class="dr-value" style="font-weight:700;">{{ $assay->description ?: '—' }}</span>
        </div>
        <div class="detail-row" style="background:rgba(255,255,255,.04);">
            <span class="dr-label" style="color:{{ $color }};">Assay Value Au g/t</span>
            <span class="dr-value" style="color:{{ $color }};font-size:1.2rem;font-weight:700;">{{ $assayFmt }}</span>
        </div>
        <div class="detail-row"><span class="dr-label">Recorded</span><span class="dr-value">{{ $assay->created_at->format('d M Y, H:i') }}</span></div>
    </div>
</div>
@endsection
