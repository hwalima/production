@extends('layouts.app')
@section('title', 'New User')
@section('page-title', 'User Management')
@section('content')

<div style="max-width:560px;">
    <div class="page-header">
        <h1 class="page-title">New User</h1>
        <a href="{{ route('users.index') }}" class="btn-cancel">&larr; Back</a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="fc-grid">
                <div>
                    <label class="fc-label">Full Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="fc-input" required autofocus>
                    @error('name')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Email <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="fc-input" required>
                    @error('email')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Role <span style="color:#ef4444;">*</span></label>
                    <select name="role" class="fc-input" required>
                        <option value="">— Select role —</option>
                        @foreach(auth()->user()->isSuperAdmin() ? ['super_admin', 'admin', 'manager', 'viewer'] : ['admin', 'manager', 'viewer'] as $r)
                        <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="fc-label">Job Title</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}" class="fc-input" placeholder="e.g. Mine Manager">
                </div>
                <div>
                    <label class="fc-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="fc-input" placeholder="+263 77 ...">
                </div>
                <div>
                    <label class="fc-label">Password <span style="color:#ef4444;">*</span></label>
                    <input type="password" name="password" class="fc-input" required autocomplete="new-password">
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Min 8 chars, must include letters and numbers.</p>
                    @error('password')<p class="fc-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Create User</button>
                <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>

    <div class="detail-card" style="margin-top:16px;">
        <p style="font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#9ca3af;margin-bottom:8px;">Role Permissions</p>
        <div class="detail-row"><span class="dr-label" style="color:#ef4444;font-weight:700;">Admin</span><span class="dr-value" style="font-size:.8rem;">Full access — user management, settings, all modules</span></div>
        <div class="detail-row"><span class="dr-label" style="color:#fcb913;font-weight:700;">Manager</span><span class="dr-value" style="font-size:.8rem;">Create, edit &amp; delete operational records; view everything</span></div>
        <div class="detail-row"><span class="dr-label" style="color:#9ca3af;font-weight:700;">Viewer</span><span class="dr-value" style="font-size:.8rem;">Read-only — can view all records but cannot modify data</span></div>
    </div>
</div>
@endsection
