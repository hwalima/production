@extends('layouts.app')
@section('title', 'Labour & Energy Details')
@section('page-title', 'Labour & Energy')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Labour &amp; Energy Details</h1>
        <a href="{{ route('labour-energy.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="detail-card">
        <div class="detail-row"><span class="dr-label">Date</span><span class="dr-value">{{ $labour_energy->date->format('d M Y') }}</span></div>
        <div class="detail-row"><span class="dr-label">ZESA Cost</span><span class="dr-value">${{ number_format($labour_energy->zesa_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Diesel Cost</span><span class="dr-value">${{ number_format($labour_energy->diesel_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Labour Cost</span><span class="dr-value">${{ number_format($labour_energy->labour_cost, 2) }}</span></div>
    </div>
</div>
@endsection
