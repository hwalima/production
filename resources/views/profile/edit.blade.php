@extends('layouts.app')
@section('page-title', 'My Profile')

@push('styles')
<style>
    /* ── Profile Page ── */
    .profile-wrap { display:grid; grid-template-columns:280px 1fr; gap:24px; align-items:start; }
    @media(max-width:900px){ .profile-wrap { grid-template-columns:1fr; } }

    .profile-card { background:var(--card); border-radius:16px; padding:28px 24px; box-shadow:0 2px 12px rgba(0,0,0,.07); }

    /* Avatar panel */
    .avatar-panel { display:flex; flex-direction:column; align-items:center; gap:16px; }
    .avatar-upload-wrap { position:relative; cursor:pointer; }
    .avatar-lg {
        width:120px; height:120px; border-radius:50%;
        object-fit:cover; display:block;
        border:4px solid #fcb913;
        box-shadow:0 4px 20px rgba(252,185,19,.25);
    }
    .avatar-lg-placeholder {
        width:120px; height:120px; border-radius:50%;
        background:linear-gradient(135deg,#fcb913,#db9f01);
        display:flex; align-items:center; justify-content:center;
        font-size:2.6rem; font-weight:700; color:#001a4d;
        border:4px solid #fcb913;
        box-shadow:0 4px 20px rgba(252,185,19,.25);
    }
    .avatar-overlay {
        position:absolute; bottom:0; right:0;
        width:36px; height:36px; border-radius:50%;
        background:#fcb913; border:3px solid var(--card);
        display:flex; align-items:center; justify-content:center;
        box-shadow:0 2px 6px rgba(0,0,0,.2);
        transition:transform .15s;
    }
    .avatar-upload-wrap:hover .avatar-overlay { transform:scale(1.1); }
    .avatar-overlay svg { width:16px; height:16px; color:#001a4d; }
    #avatarInput { display:none; }

    /* Avatar preview strip */
    .avatar-change-tip { font-size:.75rem; color:#9ca3af; text-align:center; }

    /* Role badge */
    .role-badge {
        display:inline-block; padding:3px 12px; border-radius:999px;
        font-size:.72rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase;
    }
    .role-admin   { background:#fef3c7; color:#92400e; }
    .role-manager { background:#dbeafe; color:#1e40af; }
    .role-viewer  { background:#f3f4f6; color:#6b7280; }

    /* Section headings inside cards */
    .section-title { font-size:1rem; font-weight:700; color:var(--text); margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid var(--topbar-border); }

    /* Form row */
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    @media(max-width:600px){ .form-row { grid-template-columns:1fr; } }

    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group label { font-size:.8rem; font-weight:600; color:var(--text); }
    .form-group input {
        background:var(--input-bg); border:1.5px solid var(--topbar-border);
        color:var(--text); border-radius:10px; padding:9px 13px;
        font-size:.875rem; transition:border-color .2s, box-shadow .2s; outline:none;
    }
    .form-group input:focus { border-color:#fcb913; box-shadow:0 0 0 3px rgba(252,185,19,.15); }
    .form-group .field-error { font-size:.75rem; color:#ef4444; margin-top:2px; }

    /* Save button */
    .btn-save {
        display:inline-flex; align-items:center; gap:8px;
        background:#fcb913; color:#001a4d; font-weight:700;
        border:none; border-radius:10px; padding:10px 22px;
        font-size:.875rem; cursor:pointer; transition:background .15s, transform .1s;
    }
    .btn-save:hover { background:#db9f01; transform:translateY(-1px); }
    .btn-save:active { transform:translateY(0); }

    .btn-danger {
        display:inline-flex; align-items:center; gap:8px;
        background:#fee2e2; color:#dc2626; font-weight:600;
        border:none; border-radius:10px; padding:10px 22px;
        font-size:.875rem; cursor:pointer; transition:background .15s;
    }
    .btn-danger:hover { background:#fecaca; }

    /* Success toast */
    .profile-toast {
        display:inline-flex; align-items:center; gap:6px;
        background:#dcfce7; color:#166534; border-radius:8px;
        padding:6px 14px; font-size:.8rem; font-weight:600;
        animation:fadeInOut 3s forwards;
    }
    @keyframes fadeInOut { 0%{opacity:0;transform:translateY(4px)} 10%{opacity:1;transform:translateY(0)} 80%{opacity:1} 100%{opacity:0} }

    /* Delete modal */
    .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:50; display:none; align-items:center; justify-content:center; }
    .modal-backdrop.open { display:flex; }
    .modal-box { background:var(--card); border-radius:16px; padding:28px 28px 24px; max-width:420px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,.3); }
    .modal-title { font-size:1.05rem; font-weight:700; color:var(--text); margin-bottom:8px; }
    .modal-desc  { font-size:.85rem; color:#9ca3af; margin-bottom:20px; }
</style>
@endpush

@section('content')
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:800;color:var(--text);">My Profile</h1>
        <p style="color:#9ca3af;font-size:.85rem;margin-top:2px;">Manage your personal details and security settings</p>
    </div>
</div>

<div class="profile-wrap">

    {{-- ══ LEFT: Avatar / Identity Card ══ --}}
    <div class="profile-card avatar-panel">

        {{-- Avatar Upload --}}
        <form id="avatarForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            {{-- Required by ProfileUpdateRequest even when only uploading avatar --}}
            <input type="hidden" name="name"  value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">
            <div class="avatar-upload-wrap" onclick="document.getElementById('avatarInput').click()" title="Click to change photo">
                @if($user->avatar_path)
                    <img class="avatar-lg" id="avatarPreview" src="{{ asset('storage/'.$user->avatar_path) }}" alt="Avatar">
                @else
                    <div class="avatar-lg-placeholder" id="avatarPlaceholder">{{ strtoupper(substr($user->name,0,1)) }}</div>
                    <img class="avatar-lg" id="avatarPreview" src="" alt="Avatar" style="display:none;">
                @endif
                <span class="avatar-overlay">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 10.07 4h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 18.07 7H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                </span>
            </div>
            <input type="file" name="avatar" id="avatarInput" accept="image/*">
            @error('avatar')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </form>

        <p class="avatar-change-tip">Click the photo to change your avatar<br><small>JPG, PNG, WEBP — max 2 MB</small></p>

        {{-- Identity summary --}}
        <div style="text-align:center;">
            <div style="font-size:1.1rem;font-weight:700;color:var(--text);">{{ $user->name }}</div>
            <div style="font-size:.8rem;color:#9ca3af;margin-top:2px;">{{ $user->email }}</div>
            @if($user->job_title)
                <div style="font-size:.8rem;color:#9ca3af;margin-top:2px;">{{ $user->job_title }}</div>
            @endif
            <span class="role-badge role-{{ $user->role ?? 'viewer' }}" style="margin-top:10px;">{{ ucfirst($user->role ?? 'viewer') }}</span>
        </div>

        <hr style="width:100%;border:none;border-top:1px solid var(--topbar-border);">

        {{-- Quick stats --}}
        <div style="width:100%;font-size:.8rem;color:#9ca3af;">
            <div style="display:flex;justify-content:space-between;padding:4px 0;">
                <span>Member since</span>
                <span style="color:var(--text);font-weight:600;">{{ $user->created_at->format('M Y') }}</span>
            </div>
            @if($user->phone)
            <div style="display:flex;justify-content:space-between;padding:4px 0;">
                <span>Phone</span>
                <span style="color:var(--text);font-weight:600;">{{ $user->phone }}</span>
            </div>
            @endif
        </div>

        {{-- Danger zone link --}}
        <button onclick="document.getElementById('deleteModal').classList.add('open')"
                style="font-size:.75rem;color:#ef4444;background:none;border:none;cursor:pointer;padding:4px;">
            Delete account
        </button>
    </div>

    {{-- ══ RIGHT ══ --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Profile Information --}}
        <div class="profile-card">
            <div class="section-title">Profile Information</div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PATCH')

                <div class="form-row" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @error('name') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="job_title">Job Title</label>
                        <input id="job_title" name="job_title" type="text" value="{{ old('job_title', $user->job_title) }}" placeholder="e.g. Mine Supervisor">
                        @error('job_title') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row" style="margin-bottom:24px;">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                        @error('email') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" placeholder="+263 ...">
                        @error('phone') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <button type="submit" class="btn-save">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Changes
                    </button>
                    @if (session('status') === 'profile-updated')
                        <span class="profile-toast">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Profile saved!
                        </span>
                    @endif
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="profile-card">
            <div class="section-title">Change Password</div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf @method('PUT')

                <div class="form-group" style="margin-bottom:14px;">
                    <label for="current_password">Current Password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password">
                    @error('current_password', 'updatePassword') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-row" style="margin-bottom:24px;">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password">
                        @error('password', 'updatePassword') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <button type="submit" class="btn-save">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
                        Update Password
                    </button>
                    @if (session('status') === 'password-updated')
                        <span class="profile-toast">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Password updated!
                        </span>
                    @endif
                </div>
            </form>
        </div>

    </div>
</div>

{{-- ══ Delete Account Modal ══ --}}
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-box">
        <div class="modal-title">Delete Account</div>
        <p class="modal-desc">This action is permanent and cannot be undone. All your data will be erased. Please enter your password to confirm.</p>

        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <div class="form-group" style="margin-bottom:20px;">
                <label for="del_password">Your Password</label>
                <input type="password" id="del_password" name="password" placeholder="Enter your password">
                @error('password', 'userDeletion') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('deleteModal').classList.remove('open')"
                        style="background:var(--input-bg);border:none;border-radius:10px;padding:9px 18px;font-size:.875rem;cursor:pointer;color:var(--text);">
                    Cancel
                </button>
                <button type="submit" class="btn-danger">Delete Account</button>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    // Avatar instant preview
    const input = document.getElementById('avatarInput');
    if(input){
        input.addEventListener('change', function(){
            if(!this.files || !this.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.getElementById('avatarPreview');
                const placeholder = document.getElementById('avatarPlaceholder');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if(placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(this.files[0]);
            // Auto-submit the avatar form
            document.getElementById('avatarForm').submit();
        });
    }

    // Close delete modal on backdrop click
    const modal = document.getElementById('deleteModal');
    if(modal){
        modal.addEventListener('click', function(e){ if(e.target === this) this.classList.remove('open'); });
    }
})();
</script>
@endsection
