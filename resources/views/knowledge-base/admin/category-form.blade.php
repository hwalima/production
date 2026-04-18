@extends('layouts.app')
@section('page-title', isset($category) ? 'Edit Category' : 'New Category')

@section('content')
<div class="page-header" style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">
    <a href="{{ route('kb.admin.index') }}" class="btn-cancel">← Back</a>
    <h2 class="page-title">{{ isset($category) ? 'Edit Category' : 'New Category' }}</h2>
</div>

<div class="form-card" style="max-width:520px;">
    <form method="POST"
          action="{{ isset($category) ? route('kb.categories.update', $category) : route('kb.categories.store') }}">
        @csrf
        @if(isset($category)) @method('PUT') @endif

        <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">Category Title <span style="color:#ef4444">*</span></label>
            <input type="text" name="title" class="form-control"
                   value="{{ old('title', $category?->title) }}"
                   placeholder="e.g. Getting Started"
                   maxlength="120" required>
            @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
            <div class="form-group">
                <label class="form-label">Icon (emoji)</label>
                <input type="text" name="icon" class="form-control"
                       value="{{ old('icon', $category?->icon ?? '📄') }}"
                       placeholder="📄" maxlength="20">
                <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">Single emoji character</div>
            </div>
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control"
                       value="{{ old('sort_order', $category?->sort_order ?? 0) }}"
                       min="0" max="999">
                <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">Lower = shown first</div>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn-add">
                {{ isset($category) ? 'Save Changes' : 'Create Category' }}
            </button>
            <a href="{{ route('kb.admin.index') }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
@endsection
