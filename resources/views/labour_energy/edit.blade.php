@extends('layouts.app')
@section('title', 'Edit Labour & Energy')
@section('page-title', 'Labour & Energy')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Edit Labour &amp; Energy Record</h1>
        <a href="{{ route('labour-energy.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('labour-energy.update', $labour_energy) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date</label>
                <input type="date" name="date" class="fc-input" value="{{ old('date', $labour_energy->date->format('Y-m-d')) }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">ZESA Cost ({{ $currencySymbol }})</label>
                    <input type="number" name="zesa_cost" step="0.01" class="fc-input" value="{{ old('zesa_cost', $labour_energy->zesa_cost) }}" required>
                    @error('zesa_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Diesel Cost ({{ $currencySymbol }})</label>
                    <input type="number" name="diesel_cost" step="0.01" class="fc-input" value="{{ old('diesel_cost', $labour_energy->diesel_cost) }}" required>
                    @error('diesel_cost')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            @if($departments->isNotEmpty())
            <div style="margin-bottom:14px;">
                <label class="fc-label" style="margin-bottom:8px;display:block;">Labour Cost by Department ({{ $currencySymbol }})</label>
                <div style="border:1px solid rgba(255,255,255,0.1);border-radius:8px;overflow:hidden;">
                    @foreach($departments as $dept)
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,0.07);' : '' }}">
                        <span style="flex:1;font-size:0.875rem;color:var(--text);">{{ $dept->name }}</span>
                        <input type="number"
                               name="dept_costs[{{ $dept->id }}]"
                               step="0.01" min="0"
                               class="fc-input"
                               style="width:160px;margin-bottom:0;"
                               placeholder="0.00"
                               value="{{ old('dept_costs.'.$dept->id, $existingCosts[$dept->id] ?? '') }}">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Record</button>
                <a href="{{ route('labour-energy.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
