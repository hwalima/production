@extends('layouts.app')
@section('title', 'Edit Assay Result')
@section('page-title', 'Assay Results')
@section('content')
<div style="max-width:560px;">
    <div class="page-header">
        <h1 class="page-title">Edit Assay Result</h1>
        <a href="{{ route('assay.index', ['tab' => str_replace('_', '', $assay->type === 'fire_assay' ? 'fire' : ($assay->type === 'gold_on_carbon' ? 'goc' : 'bottle'))]) }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('assay.update', $assay) }}" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:14px;">
                <label class="fc-label">Assay Type <span style="color:#ef4444;">*</span></label>
                <select name="type" class="fc-input" required>
                    @foreach(['fire_assay' => 'Fire Assay', 'gold_on_carbon' => 'Gold on Carbon', 'bottle_roll' => 'Bottle Roll'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('type', $assay->type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('type')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date <span style="color:#ef4444;">*</span></label>
                <input type="date" name="date" class="fc-input" value="{{ old('date', $assay->date->format('Y-m-d')) }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Assay Value (g/t) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="assay_value" step="0.0001" min="0" class="fc-input" value="{{ old('assay_value', $assay->assay_value) }}" required>
                @error('assay_value')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">Description</label>
                <input type="text" name="description" class="fc-input" value="{{ old('description', $assay->description) }}" placeholder="Optional notes">
                @error('description')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Result</button>
                <a href="{{ route('assay.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
