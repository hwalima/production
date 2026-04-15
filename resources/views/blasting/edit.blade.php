@extends('layouts.app')
@section('title', 'Edit Blasting')
@section('page-title', 'Blasting Records')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Edit Blasting Record</h1>
        <a href="{{ route('blasting.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('blasting.update', $blasting) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date</label>
                <input type="date" name="date" class="fc-input" value="{{ old('date', $blasting->date->format('Y-m-d')) }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Fractures</label>
                    <input type="number" name="fractures" step="1" class="fc-input" value="{{ old('fractures', $blasting->fractures) }}" required>
                    @error('fractures')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Fuse</label>
                    <input type="number" name="fuse" step="1" class="fc-input" value="{{ old('fuse', $blasting->fuse) }}" required>
                    @error('fuse')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Carmes IEDs</label>
                    <input type="number" name="carmes_ieds" step="1" class="fc-input" value="{{ old('carmes_ieds', $blasting->carmes_ieds) }}" required>
                    @error('carmes_ieds')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Power Cords</label>
                    <input type="number" name="power_cords" step="1" class="fc-input" value="{{ old('power_cords', $blasting->power_cords) }}" required>
                    @error('power_cords')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">ANFO (kg)</label>
                    <input type="number" name="anfo" step="0.01" class="fc-input" value="{{ old('anfo', $blasting->anfo) }}" required>
                    @error('anfo')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Oil (L)</label>
                    <input type="number" name="oil" step="0.01" class="fc-input" value="{{ old('oil', $blasting->oil) }}" required>
                    @error('oil')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Drill Bits</label>
                    <input type="number" name="drill_bits" step="1" class="fc-input" value="{{ old('drill_bits', $blasting->drill_bits) }}" required>
                    @error('drill_bits')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Record</button>
                <a href="{{ route('blasting.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
