@extends('layouts.app')
@section('title', 'Edit SHE Indicator')
@section('page-title', 'SHE')
@section('content')
<div style="max-width:720px;">
    <div class="page-header">
        <h1 class="page-title">Edit SHE Indicator Record</h1>
        <a href="{{ route('she.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('she.indicators.update', $indicator) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="date" class="fc-input"
                           value="{{ old('date', $indicator->date->format('Y-m-d')) }}" required>
                    @error('date')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Department <span style="color:#ef4444;">*</span></label>
                    <select name="mining_department_id" class="fc-input" required>
                        <option value="">— Select Department —</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}"
                            {{ old('mining_department_id', $indicator->mining_department_id) == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('mining_department_id')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
                        color:#9ca3af;border-bottom:1px solid rgba(255,255,255,.07);
                        padding-bottom:8px;margin-bottom:12px;">
                Indicators
            </div>
            <div class="fc-grid" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-bottom:18px;">
                @foreach($indicatorLabels as $field => $label)
                <div>
                    <label class="fc-label" style="font-size:.75rem;">{{ $label }}</label>
                    <input type="number" name="{{ $field }}" step="1" min="0"
                           class="fc-input"
                           value="{{ old($field, $indicator->{$field} > 0 ? $indicator->{$field} : '') }}"
                           placeholder="0">
                    @error($field)<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Record</button>
                <a href="{{ route('she.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection