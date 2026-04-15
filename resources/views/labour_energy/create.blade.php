@extends('layouts.app')
@section('title', 'Add Labour & Energy')
@section('page-title', 'Labour & Energy')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Add Labour &amp; Energy Record</h1>
        <a href="{{ route('labour-energy.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('labour-energy.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date</label>
                <input type="date" name="date" class="fc-input" value="{{ old('date') }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">ZESA Cost ({{ $currencySymbol }})</label>
                    <input type="number" name="zesa_cost" step="0.01" class="fc-input" value="{{ old('zesa_cost', $defaults['zesa'] ?? '') }}" required>
                    @error('zesa_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Diesel Cost ({{ $currencySymbol }})</label>
                    <input type="number" name="diesel_cost" step="0.01" class="fc-input" value="{{ old('diesel_cost', $defaults['diesel'] ?? '') }}" required>
                    @error('diesel_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Labour Cost ({{ $currencySymbol }})</label>
                    <input type="number" name="labour_cost" step="0.01" class="fc-input" value="{{ old('labour_cost', $defaults['labour'] ?? '') }}" required>
                    @error('labour_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Record</button>
                <a href="{{ route('labour-energy.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
