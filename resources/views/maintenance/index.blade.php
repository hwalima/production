@extends('layouts.app')
@section('title', 'System Maintenance')
@section('page-title', 'Admin')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">System Maintenance</h1>
        <nav style="font-size:.75rem;color:#6b7280;margin-top:2px;">
            <a href="{{ route('dashboard') }}" style="color:#6b7280;text-decoration:none;">Home</a>
            &rsaquo; <a href="{{ route('settings.index') }}" style="color:#6b7280;text-decoration:none;">Settings</a>
            &rsaquo; <span style="color:#fcb913;">Maintenance</span>
        </nav>
    </div>
</div>

@if(session('success'))
<div class="alert-success" style="margin-bottom:16px;">&#10003; {{ session('success') }}</div>
@endif

{{-- ── System Cache ── --}}
<div class="data-card" style="margin-bottom:20px;padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;gap:14px;padding:18px 22px;border-bottom:1px solid var(--topbar-border);">
        <div style="width:42px;height:42px;border-radius:10px;background:rgba(96,165,250,.15);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">&#128190;</div>
        <div>
            <div style="font-weight:700;font-size:.95rem;">System Cache</div>
            <div style="font-size:.78rem;color:#6b7280;margin-top:1px;">Clears the application cache, config cache, and compiled view cache for all users.</div>
        </div>
    </div>
    <div style="padding:18px 22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="font-size:.82rem;color:#9ca3af;">
                Last cleared:
                @if($lastCacheClear)
                    <strong style="color:var(--text);">{{ \Carbon\Carbon::parse($lastCacheClear)->diffForHumans() }}</strong>
                    <span style="color:#6b7280;">({{ \Carbon\Carbon::parse($lastCacheClear)->format('d M Y H:i') }})</span>
                @else
                    <span style="color:#6b7280;">Never recorded</span>
                @endif
            </div>
            <form method="POST" action="{{ route('maintenance.cache.clear') }}"
                  onsubmit="return confirm('Clear all application caches?')">
                @csrf
                <button type="submit" class="btn-add" style="background:#3b82f6;border-color:#3b82f6;display:inline-flex;align-items:center;gap:6px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Clear All Caches
                </button>
            </form>
        </div>
        <div style="display:flex;gap:24px;margin-top:14px;flex-wrap:wrap;">
            <span style="font-size:.78rem;color:#22c55e;display:flex;align-items:center;gap:5px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                Application cache
            </span>
            <span style="font-size:.78rem;color:#22c55e;display:flex;align-items:center;gap:5px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                Config cache
            </span>
            <span style="font-size:.78rem;color:#22c55e;display:flex;align-items:center;gap:5px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                Compiled views
            </span>
            <span style="font-size:.78rem;color:#22c55e;display:flex;align-items:center;gap:5px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                Route cache
            </span>
        </div>
    </div>
</div>

{{-- ── Audit Logs ── --}}
<div class="data-card" style="margin-bottom:20px;padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--topbar-border);flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:42px;height:42px;border-radius:10px;background:rgba(251,146,60,.15);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">&#128221;</div>
            <div>
                <div style="font-weight:700;font-size:.95rem;">Audit Logs</div>
                <div style="font-size:.78rem;color:#6b7280;margin-top:1px;">Permanently delete old audit log entries to free up database space.</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:2rem;font-weight:800;color:var(--text);line-height:1;">{{ number_format($auditCount) }}</div>
            <div style="font-size:.72rem;color:#6b7280;">total records</div>
        </div>
    </div>
    <div style="padding:18px 22px;">
        <div style="font-size:.82rem;color:#9ca3af;margin-bottom:14px;">
            Oldest entry:
            @if($auditOldest)
                <strong style="color:var(--text);">{{ \Carbon\Carbon::parse($auditOldest)->format('d M Y') }}</strong>
            @else
                <span style="color:#6b7280;">No records</span>
            @endif
        </div>
        <form method="POST" action="{{ route('maintenance.audit-logs.purge') }}"
              id="purgeAuditForm"
              onsubmit="var s=this.querySelector('select');var txt=s.options[s.selectedIndex].text;return confirm('Permanently delete audit logs ('+txt+')? This cannot be undone.')">
            @csrf
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div>
                    <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;display:block;margin-bottom:5px;">Delete records older than</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <select name="days" class="fc-input" style="width:220px;">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90" selected>90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                            <option value="0">All records</option>
                        </select>
                        <button type="submit" class="btn-add" style="background:#ef4444;border-color:#ef4444;display:inline-flex;align-items:center;gap:6px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            Purge Audit Logs
                        </button>
                        <a href="{{ route('maintenance.audit-logs') }}" style="font-size:.82rem;color:#6b7280;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            View Logs
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Login Logs ── --}}
<div class="data-card" style="margin-bottom:20px;padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--topbar-border);flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:42px;height:42px;border-radius:10px;background:rgba(167,139,250,.15);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">&#128100;</div>
            <div>
                <div style="font-weight:700;font-size:.95rem;">Login Logs</div>
                <div style="font-size:.78rem;color:#6b7280;margin-top:1px;">Track user login, logout, and failed login attempts. Useful for security auditing.</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:2rem;font-weight:800;color:var(--text);line-height:1;">{{ number_format($loginCount) }}</div>
            <div style="font-size:.72rem;color:#6b7280;">total records</div>
        </div>
    </div>
    <div style="padding:18px 22px;">
        <div style="font-size:.82rem;color:#9ca3af;margin-bottom:14px;">
            Oldest entry:
            @if($loginOldest)
                <strong style="color:var(--text);">{{ \Carbon\Carbon::parse($loginOldest)->format('d M Y') }}</strong>
            @else
                <span style="color:#6b7280;">No records yet — login events will be recorded automatically.</span>
            @endif
        </div>
        <form method="POST" action="{{ route('maintenance.login-logs.purge') }}"
              id="purgeLoginForm"
              onsubmit="var s=this.querySelector('select');var txt=s.options[s.selectedIndex].text;return confirm('Permanently delete login logs ('+txt+')? This cannot be undone.')">
            @csrf
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div>
                    <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;display:block;margin-bottom:5px;">Delete records older than</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <select name="days" class="fc-input" style="width:220px;">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90" selected>90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                            <option value="0">All records</option>
                        </select>
                        <button type="submit" class="btn-add" style="background:#ef4444;border-color:#ef4444;display:inline-flex;align-items:center;gap:6px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            Purge Login Logs
                        </button>
                        <a href="{{ route('maintenance.login-logs') }}" style="font-size:.82rem;color:#6b7280;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            View Logs
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
