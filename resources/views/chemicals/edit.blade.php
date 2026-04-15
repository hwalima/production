@extends('layouts.app')
@section('title', 'Edit Chemical Inventory')
@section('page-title', 'Chemical Inventory')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Edit Chemical Inventory</h1>
        <a href="{{ route('chemicals.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('chemicals.update', $chemical) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:14px;">
                <label class="fc-label">Date</label>
                <input type="date" name="date" class="fc-input" value="{{ old('date', $chemical->date->format('Y-m-d')) }}" required>
                @error('date')<p class="fc-error">{{ $message }}</p>@enderror
            </div>
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Sodium Cyanide (kg)</label>
                    <input type="number" name="sodium_cyanide" step="0.01" class="fc-input" value="{{ old('sodium_cyanide', $chemical->sodium_cyanide) }}" required>
                    @error('sodium_cyanide')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Lime (kg)</label>
                    <input type="number" name="lime" step="0.01" class="fc-input" value="{{ old('lime', $chemical->lime) }}" required>
                    @error('lime')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Caustic Soda (kg)</label>
                    <input type="number" name="caustic_soda" step="0.01" class="fc-input" value="{{ old('caustic_soda', $chemical->caustic_soda) }}" required>
                    @error('caustic_soda')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Iodised Salt (kg)</label>
                    <input type="number" name="iodised_salt" step="0.01" class="fc-input" value="{{ old('iodised_salt', $chemical->iodised_salt) }}" required>
                    @error('iodised_salt')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Mercury (g)</label>
                    <input type="number" name="mercury" step="0.01" class="fc-input" value="{{ old('mercury', $chemical->mercury) }}" required>
                    @error('mercury')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Steel Balls (kg)</label>
                    <input type="number" name="steel_balls" step="0.01" class="fc-input" value="{{ old('steel_balls', $chemical->steel_balls) }}" required>
                    @error('steel_balls')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Hydrogen Peroxide (L)</label>
                    <input type="number" name="hydrogen_peroxide" step="0.01" class="fc-input" value="{{ old('hydrogen_peroxide', $chemical->hydrogen_peroxide) }}" required>
                    @error('hydrogen_peroxide')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Borax (kg)</label>
                    <input type="number" name="borax" step="0.01" class="fc-input" value="{{ old('borax', $chemical->borax) }}" required>
                    @error('borax')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Nitric Acid (L)</label>
                    <input type="number" name="nitric_acid" step="0.01" class="fc-input" value="{{ old('nitric_acid', $chemical->nitric_acid) }}" required>
                    @error('nitric_acid')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Sulphuric Acid (L)</label>
                    <input type="number" name="sulphuric_acid" step="0.01" class="fc-input" value="{{ old('sulphuric_acid', $chemical->sulphuric_acid) }}" required>
                    @error('sulphuric_acid')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Record</button>
                <a href="{{ route('chemicals.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
