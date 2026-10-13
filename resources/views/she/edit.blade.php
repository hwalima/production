@extends('layouts.app')
@section('title', 'Edit SHE Indicator')
@section('page-title', 'SHE')
@section('content')
<div class="she-form-wrap">
    <div class="page-header">
        <h1 class="page-title">Edit SHE Indicator Record</h1>
        <a href="{{ route('she.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('she.indicators.update', $indicator) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="fc-grid she-meta-grid">
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

            @include('she._indicator-fields', ['indicator' => $indicator])

            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Record</button>
                <a href="{{ route('she.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection