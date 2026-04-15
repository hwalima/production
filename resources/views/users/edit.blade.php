@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'User Management')
@section('content')

<div style="max-width:560px;">
    <div class="page-header">
        <h1 class="page-title">Edit User</h1>
        <a href="{{ route('users.index') }}" class="btn-cancel">&larr; Back</a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')

            <div class="fc-grid">
                <div>
                    <label class="fc-label">Full Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="fc-input" required autofocus>
                    @error('name')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Email <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="fc-input" required>
                    @error('email')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Role <span style="color:#ef4444;">*</span></label>
                    <select name="role" class="fc-input" required>
                        @foreach(auth()->user()->isSuperAdmin() ? ['super_admin', 'admin', 'manager', 'viewer'] : ['admin', 'manager', 'viewer'] as $r)
                        <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Job Title</label>
                    <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="fc-input" placeholder="e.g. Mine Manager">
                </div>
                <div>
                    <label class="fc-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="fc-input" placeholder="+263 77 ...">
                </div>
                <div>
                    <label class="fc-label">New Password <span style="color:#9ca3af;">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="fc-input" autocomplete="new-password">
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Min 8 chars, must include letters and numbers.</p>
                    @error('password')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
