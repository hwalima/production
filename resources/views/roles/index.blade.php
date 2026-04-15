@extends('layouts.app')
@section('title', 'Roles Management')
@section('page-title', 'Roles Management')
@section('content')

<div class="page-header">
    <h1 class="page-title">Roles &amp; Permissions</h1>
</div>

{{-- ── Role cards ──────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(285px,1fr));gap:18px;margin-bottom:28px;">
@foreach($roles as $key => $role)
<div class="detail-card" style="padding:0;overflow:hidden;">

    {{-- Header band --}}
    <div style="background:{{ $role['badge_color'] }};padding:16px 20px 14px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:1rem;font-weight:700;color:#fff;letter-spacing:.02em;">{{ $role['label'] }}</div>
            <div style="font-size:.72rem;color:rgba(255,255,255,.75);margin-top:2px;font-family:monospace;">{{ $key }}</div>
        </div>
        <div style="background:rgba(255,255,255,.18);border-radius:30px;padding:4px 14px;font-size:.8rem;font-weight:600;color:#fff;">
            {{ $role['users_count'] }} {{ Str::plural('user', $role['users_count']) }}
        </div>
    </div>

    {{-- Body --}}
    <div style="padding:16px 20px;">
        <p style="font-size:.82rem;color:#9ca3af;margin-bottom:14px;line-height:1.5;">{{ $role['description'] }}</p>

        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
            @foreach($role['permissions'] as $perm => $allowed)
            <tr>
                <td style="padding:4px 0;color:#d1d5db;">{{ $perm }}</td>
                <td style="text-align:right;padding:4px 0;">
                    @if($allowed)
                        <span style="color:#22c55e;font-weight:700;">&#10003;</span>
                    @else
                        <span style="color:#ef4444;opacity:.7;">&#10007;</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    {{-- Footer: users with this role --}}
    @if(isset($usersByRole[$key]) && $usersByRole[$key]->isNotEmpty())
    <div style="padding:0 20px 16px;">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:8px;">Users</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach($usersByRole[$key] as $u)
            <span style="font-size:.75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:3px 10px;color:#d1d5db;white-space:nowrap;">
                {{ $u->name }}
                @if($u->id === auth()->id())
                    <span style="color:#fcb913;font-size:.65rem;"> (you)</span>
                @endif
            </span>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endforeach
</div>

{{-- ── Permission Matrix ────────────────────────────────────────────── --}}
<div class="detail-card" style="padding:0;overflow:hidden;margin-bottom:28px;">
    <div style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.07);">
        <span style="font-size:.82rem;font-weight:600;color:#fcb913;letter-spacing:.04em;text-transform:uppercase;">Permission Matrix</span>
    </div>
    <div class="tbl-scroll">
    <table class="data-table" style="white-space:nowrap;">
        <thead>
            <tr>
                <th>Permission</th>
                @foreach($roles as $key => $role)
                <th class="th-c" style="background:rgba(0,0,0,.22);">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $role['badge_color'] }};margin-right:5px;vertical-align:middle;"></span>
                    {{ $role['label'] }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $permissions = array_keys(array_values($roles)[0]['permissions']); @endphp
            @foreach($permissions as $perm)
            <tr>
                <td style="font-size:.82rem;color:#d1d5db;">{{ $perm }}</td>
                @foreach($roles as $role)
                <td class="td-c">
                    @if($role['permissions'][$perm])
                        <span style="color:#22c55e;font-weight:700;font-size:1rem;">&#10003;</span>
                    @else
                        <span style="color:#ef4444;opacity:.6;font-size:1rem;">&#10007;</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

{{-- ── Assign Roles (super_admin only) ─────────────────────────────── --}}
<div class="detail-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px;">
        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#7c3aed;flex-shrink:0;"></span>
        <span style="font-size:.82rem;font-weight:600;color:#fcb913;letter-spacing:.04em;text-transform:uppercase;">Assign Roles</span>
        <span style="font-size:.75rem;color:#6b7280;margin-left:4px;">— Super Administrator only</span>
    </div>
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Job Title</th>
                <th class="th-c">Current Role</th>
                <th class="th-c">Change Role</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allUsers as $u)
            <tr>
                <td style="font-weight:600;">
                    {{ $u->name }}
                    @if($u->id === auth()->id())
                    <span style="font-size:.68rem;background:rgba(252,185,19,.2);color:#fcb913;border-radius:10px;padding:1px 7px;margin-left:4px;">You</span>
                    @endif
                </td>
                <td style="font-size:.82rem;color:#9ca3af;">{{ $u->email }}</td>
                <td style="font-size:.82rem;">{{ $u->job_title ?: '—' }}</td>
                <td class="td-c">
                    @php $rc = $roles[$u->role] ?? null; @endphp
                    <span style="font-size:.72rem;font-weight:700;color:#fff;background:{{ $rc['badge_color'] ?? '#6b7280' }};border-radius:20px;padding:2px 10px;">
                        {{ $rc['label'] ?? $u->role }}
                    </span>
                </td>
                <td class="td-c">
                    @if($u->id === auth()->id())
                        <span style="font-size:.75rem;color:#6b7280;">—</span>
                    @else
                    <form method="POST" action="{{ route('roles.assign', $u) }}" style="display:inline-flex;align-items:center;gap:6px;">
                        @csrf
                        @method('PATCH')
                        <select name="role" style="background:#1e293b;color:#d1d5db;border:1px solid rgba(255,255,255,.12);border-radius:6px;padding:4px 8px;font-size:.78rem;cursor:pointer;" onchange="this.form.submit()">
                            @foreach($roles as $rKey => $rDef)
                            <option value="{{ $rKey }}" @selected($u->role === $rKey)
                                style="background:#1e293b;">
                                {{ $rDef['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

@endsection
