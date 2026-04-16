@extends('layouts.app')
@section('title', 'Login Logs')
@section('page-title', 'Admin')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Login Logs</h1>
        <nav style="font-size:.75rem;color:#6b7280;margin-top:2px;">
            <a href="{{ route('dashboard') }}" style="color:#6b7280;text-decoration:none;">Home</a>
            &rsaquo; <a href="{{ route('maintenance.index') }}" style="color:#6b7280;text-decoration:none;">Maintenance</a>
            &rsaquo; <span style="color:#fcb913;">Login Logs</span>
        </nav>
    </div>
    <a href="{{ route('maintenance.index') }}" class="btn-add" style="background:#6b7280;border-color:#6b7280;">
        &larr; Back to Maintenance
    </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('maintenance.login-logs') }}" class="filter-form" style="margin-bottom:16px;">
    <div class="filter-group">
        <label class="filter-label">Search</label>
        <input type="text" name="q" class="fc-input" value="{{ request('q') }}" placeholder="Name or email…" style="min-width:200px;">
    </div>
    <div class="filter-group">
        <label class="filter-label">Event</label>
        <select name="event" class="fc-input">
            <option value="">All events</option>
            <option value="login"   {{ request('event') === 'login'   ? 'selected' : '' }}>Login</option>
            <option value="logout"  {{ request('event') === 'logout'  ? 'selected' : '' }}>Logout</option>
            <option value="failed"  {{ request('event') === 'failed'  ? 'selected' : '' }}>Failed</option>
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">From</label>
        <input type="date" name="from" class="fc-input" value="{{ request('from') }}">
    </div>
    <div class="filter-group">
        <label class="filter-label">To</label>
        <input type="date" name="to" class="fc-input" value="{{ request('to') }}">
    </div>
    <button type="submit" class="btn-add">Filter</button>
    @if(request()->hasAny(['q','event','from','to']))
        <a href="{{ route('maintenance.login-logs') }}" class="btn-add" style="background:#6b7280;border-color:#6b7280;">Clear</a>
    @endif
</form>

<div class="data-card" style="overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--topbar-border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.82rem;color:#6b7280;">{{ $logs->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="loginLogsTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Event</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-weight:600;font-size:.85rem;">{{ $log->user_name ?? '—' }}</td>
                    <td style="font-size:.82rem;color:#9ca3af;">{{ $log->user_email ?? '—' }}</td>
                    <td>
                        @php
                            $badgeStyle = match($log->event) {
                                'login'  => 'background:rgba(34,197,94,.15);color:#22c55e;',
                                'logout' => 'background:rgba(249,115,22,.15);color:#f97316;',
                                'failed' => 'background:rgba(239,68,68,.15);color:#ef4444;',
                                default  => 'background:rgba(156,163,175,.15);color:#9ca3af;',
                            };
                        @endphp
                        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:2px 8px;border-radius:20px;{{ $badgeStyle }}">
                            {{ $log->event }}
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:#9ca3af;font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
                    <td style="font-size:.72rem;color:#6b7280;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->user_agent }}">
                        {{ $log->user_agent ? \Illuminate\Support\Str::limit($log->user_agent, 60) : '—' }}
                    </td>
                    <td style="font-size:.78rem;color:#9ca3af;white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:28px;color:#6b7280;">No login log records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div style="padding:14px 18px;border-top:1px solid var(--topbar-border);">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
