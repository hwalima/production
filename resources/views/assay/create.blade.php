@extends('layouts.app')
@section('title', 'Add Assay Result')
@section('page-title', 'Assay Results')
@section('content')
<div style="max-width:560px;">
    <div class="page-header">
        <h1 class="page-title">Add Assay Result</h1>
        <a href="{{ route('assay.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('assay.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <label class="fc-label">Assay Type <span style="color:#ef4444;">*</span></label>
                <select name="type" class="fc-input" required>
                    @foreach(['fire_assay' => 'Fire Assay', 'gold_on_carbon' => 'Gold on Carbon', 'bottle_roll' => 'Bottle Roll'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('type', request('type')) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('type')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date <span style="color:#ef4444;">*</span></label>
                <input type="date" name="date" class="fc-input" value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Assay Value (g/t) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="assay_value" step="0.0001" min="0" class="fc-input" value="{{ old('assay_value') }}" required placeholder="e.g. 12.5000">
                @error('assay_value')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Description</label>
                <input type="text" name="description" class="fc-input" value="{{ old('description') }}" placeholder="Optional notes">
                @error('description')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Result</button>
                <a href="{{ route('assay.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
