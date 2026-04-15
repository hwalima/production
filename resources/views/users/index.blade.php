@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('content')

<div class="page-header">
    <h1 class="page-title">Users</h1>
    <a href="{{ route('users.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New User
    </a>
</div>

<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Job Title</th>
                <th>Phone</th>
                <th class="th-c">Role</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <td style="font-weight:600;">
                    {{ $u->name }}
                    @if($u->id === auth()->id())
                    <span style="font-size:.68rem;background:rgba(252,185,19,.2);color:#fcb913;border-radius:10px;padding:1px 7px;margin-left:4px;">You</span>
                    @endif
                </td>
                <td style="font-size:.82rem;color:#9ca3af;">{{ $u->email }}</td>
                <td style="font-size:.82rem;">{{ $u->job_title ?: '—' }}</td>
                <td style="font-size:.82rem;">{{ $u->phone ?: '—' }}</td>
                <td class="td-c">
                    @php
                        $roleColour = match($u->role) {
                            'super_admin' => ['bg' => 'rgba(124,58,237,.15)',  'border' => '#7c3aed', 'text' => '#a78bfa'],
                            'admin'       => ['bg' => 'rgba(239,68,68,.15)',   'border' => '#ef4444', 'text' => '#ef4444'],
                            'manager'     => ['bg' => 'rgba(252,185,19,.15)', 'border' => '#fcb913', 'text' => '#fcb913'],
                            default       => ['bg' => 'rgba(156,163,175,.12)','border' => '#6b7280', 'text' => '#9ca3af'],
                        };
                    @endphp
                    <span style="background:{{ $roleColour['bg'] }};color:{{ $roleColour['text'] }};border:1px solid {{ $roleColour['border'] }};border-radius:20px;padding:3px 12px;font-size:.72rem;font-weight:700;text-transform:uppercase;white-space:nowrap;">
                        {{ str_replace('_', ' ', $u->role) }}
                    </span>
                </td>
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('users.edit', $u) }}" class="act-btn act-edit" title="Edit user">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $u) }}" style="display:contents" onsubmit="return confirm('Delete user {{ $u->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete user">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="6">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
