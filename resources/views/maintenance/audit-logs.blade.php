@extends('layouts.app')
@section('title', 'Audit Logs')
@section('page-title', 'Admin')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Audit Logs</h1>
        <nav style="font-size:.75rem;color:#6b7280;margin-top:2px;">
            <a href="{{ route('dashboard') }}" style="color:#6b7280;text-decoration:none;">Home</a>
            &rsaquo; <a href="{{ route('maintenance.index') }}" style="color:#6b7280;text-decoration:none;">Maintenance</a>
            &rsaquo; <span style="color:#fcb913;">Audit Logs</span>
        </nav>
    </div>
    <a href="{{ route('maintenance.index') }}" class="btn-add" style="background:#6b7280;border-color:#6b7280;">
        &larr; Back to Maintenance
    </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('maintenance.audit-logs') }}" class="filter-form" style="margin-bottom:16px;">
    <div class="filter-group">
        <label class="filter-label">Search</label>
        <input type="text" name="q" class="fc-input" value="{{ request('q') }}" placeholder="User, action, description…" style="min-width:240px;">
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
    @if(request()->hasAny(['q','from','to']))
        <a href="{{ route('maintenance.audit-logs') }}" class="btn-add" style="background:#6b7280;border-color:#6b7280;">Clear</a>
    @endif
</form>

<div class="data-card" style="overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--topbar-border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.82rem;color:#6b7280;">{{ $logs->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="auditLogsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Model</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="color:#6b7280;font-size:.78rem;">{{ $log->id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:.82rem;">{{ $log->user_name ?? '—' }}</div>
                        @if($log->user)
                            <div style="font-size:.72rem;color:#6b7280;">{{ $log->user->email }}</div>
                        @endif
                    </td>
                    <td>
                        @php
                            $color = match(strtolower($log->action)) {
                                'create','created' => '#22c55e',
                                'update','updated' => '#3b82f6',
                                'delete','deleted' => '#ef4444',
                                'login'            => '#a78bfa',
                                'logout'           => '#f97316',
                                default            => '#9ca3af',
                            };
                        @endphp
                        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:{{ $color }};">{{ $log->action }}</span>
                    </td>
                    <td style="font-size:.8rem;color:#9ca3af;">
                        @if($log->model)
                            {{ class_basename($log->model) }}
                            @if($log->model_id)
                                <span style="color:#6b7280;">#{{ $log->model_id }}</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-size:.82rem;max-width:320px;word-break:break-word;">{{ $log->description ?? '—' }}</td>
                    <td style="font-size:.78rem;color:#9ca3af;font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
                    <td style="font-size:.78rem;color:#9ca3af;white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#6b7280;">No audit log records found.</td></tr>
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
