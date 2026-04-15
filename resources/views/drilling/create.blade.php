@extends('layouts.app')
@section('title', 'Add Drilling')
@section('page-title', 'Drilling Records')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Add Drilling Record</h1>
        <a href="{{ route('drilling.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('drilling.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date</label>
                <input type="date" name="date" class="fc-input" value="{{ old('date') }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label class="fc-label">End Name</label>
                <input type="text" name="end_name" class="fc-input" value="{{ old('end_name') }}" required>
                @error('end_name')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Hole Count</label>
                    <input type="number" name="hole_count" step="1" class="fc-input" value="{{ old('hole_count') }}" required>
                    @error('hole_count')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Drill Steel Length (m)</label>
                    <input type="number" name="drill_steel_length" step="0.01" class="fc-input" value="{{ old('drill_steel_length') }}" required>
                    @error('drill_steel_length')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Advance (m)</label>
                    <input type="number" name="advance" step="0.01" class="fc-input" value="{{ old('advance') }}" required>
                    @error('advance')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Record</button>
                <a href="{{ route('drilling.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
