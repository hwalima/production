@extends('layouts.app')
@section('title', 'Blasting Details')
@section('page-title', 'Blasting Records')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Blasting Record Details</h1>
        <a href="{{ route('blasting.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="detail-card">
        <div class="detail-row"><span class="dr-label">Date</span><span class="dr-value">{{ $blasting->date->format('d M Y') }}</span></div>
        <div class="detail-row"><span class="dr-label">Fractures</span><span class="dr-value">{{ $blasting->fractures }}</span></div>
        <div class="detail-row"><span class="dr-label">Fuse</span><span class="dr-value">{{ $blasting->fuse }}</span></div>
        <div class="detail-row"><span class="dr-label">Carmes IEDs</span><span class="dr-value">{{ $blasting->carmes_ieds }}</span></div>
        <div class="detail-row"><span class="dr-label">Power Cords</span><span class="dr-value">{{ $blasting->power_cords }}</span></div>
        <div class="detail-row"><span class="dr-label">ANFO</span><span class="dr-value">{{ $blasting->anfo }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Oil</span><span class="dr-value">{{ $blasting->oil }} L</span></div>
        <div class="detail-row"><span class="dr-label">Drill Bits</span><span class="dr-value">{{ $blasting->drill_bits }}</span></div>
    </div>
</div>
@endsection
