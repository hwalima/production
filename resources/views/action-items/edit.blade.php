@extends('layouts.app')
@section('title', 'Edit Action Item')
@section('page-title', 'Action Items')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Edit Action Item</h1>
        <a href="{{ route('action-items.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="form-card">
        <form action="{{ route('action-items.update', $item) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom:14px;">
                <label class="fc-label">Department <span style="color:#ef4444;">*</span></label>
                <select name="mining_department_id" class="fc-input" required>
                    <option value="">— Select Department —</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}"
                        {{ old('mining_department_id', $item->mining_department_id) == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
                @error('mining_department_id')<p class="fc-error">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:14px;">
                <label class="fc-label">Comment / Issue <span style="color:#ef4444;">*</span></label>
                <textarea name="comment" class="fc-input" rows="3" required
                          style="resize:vertical;">{{ old('comment', $item->comment) }}</textarea>
                @error('comment')<p class="fc-error">{{ $message }}</p>@enderror
            </div>

            <div class="fc-grid" style="margin-bottom:14px;">
                <div>
                    <label class="fc-label">Priority <span style="color:#ef4444;">*</span></label>
                    <select name="priority" class="fc-input" required>
                        <option value="high"   {{ old('priority', $item->priority) === 'high'   ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ old('priority', $item->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low"    {{ old('priority', $item->priority) === 'low'    ? 'selected' : '' }}>Low</option>
                    </select>
                    @error('priority')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Status <span style="color:#ef4444;">*</span></label>
                    <select name="status" class="fc-input" required>
                        <option value="not_started" {{ old('status', $item->status) === 'not_started' ? 'selected' : '' }}>Not Started</option>
                        <option value="in_progress" {{ old('status', $item->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="pending"     {{ old('status', $item->status) === 'pending'     ? 'selected' : '' }}>Pending</option>
                        <option value="completed"   {{ old('status', $item->status) === 'completed'   ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="fc-grid" style="margin-bottom:18px;">
                <div>
                    <label class="fc-label">Reported Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="reported_date" class="fc-input"
                           value="{{ old('reported_date', $item->reported_date->format('Y-m-d')) }}" required>
                    @error('reported_date')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Due Date <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
                    <input type="date" name="due_date" class="fc-input"
                           value="{{ old('due_date', $item->due_date?->format('Y-m-d')) }}">
                    @error('due_date')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Item</button>
                <a href="{{ route('action-items.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
