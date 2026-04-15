@extends('layouts.app')
@section('title', 'Chemical Details')
@section('page-title', 'Chemical Inventory')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Chemical Inventory Details</h1>
        <a href="{{ route('chemicals.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="detail-card">
        <div class="detail-row"><span class="dr-label">Date</span><span class="dr-value">{{ $chemical->date->format('d M Y') }}</span></div>
        <div class="detail-row"><span class="dr-label">Sodium Cyanide</span><span class="dr-value">{{ $chemical->sodium_cyanide }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Lime</span><span class="dr-value">{{ $chemical->lime }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Caustic Soda</span><span class="dr-value">{{ $chemical->caustic_soda }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Iodised Salt</span><span class="dr-value">{{ $chemical->iodised_salt }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Mercury</span><span class="dr-value">{{ $chemical->mercury }} g</span></div>
        <div class="detail-row"><span class="dr-label">Steel Balls</span><span class="dr-value">{{ $chemical->steel_balls }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Hydrogen Peroxide</span><span class="dr-value">{{ $chemical->hydrogen_peroxide }} L</span></div>
        <div class="detail-row"><span class="dr-label">Borax</span><span class="dr-value">{{ $chemical->borax }} kg</span></div>
        <div class="detail-row"><span class="dr-label">Nitric Acid</span><span class="dr-value">{{ $chemical->nitric_acid }} L</span></div>
        <div class="detail-row"><span class="dr-label">Sulphuric Acid</span><span class="dr-value">{{ $chemical->sulphuric_acid }} L</span></div>
    </div>
</div>
@endsection
