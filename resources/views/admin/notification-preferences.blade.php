@extends('layouts.app')
@section('page-title', 'Notification Opt-Outs')

@push('styles')
<style>
.no-wrap { max-width: 960px; }

/* Alert type card */
.no-card {
    background: var(--card);
    border: 1px solid var(--topbar-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,.07);
    margin-bottom: 22px;
}
.no-card-head {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--topbar-border);
    background: rgba(0,0,0,.02);
}
html.dark .no-card-head { background: rgba(255,255,255,.02); }

.no-type-name { font-size: .9rem; font-weight: 800; color: var(--text); }
.no-type-desc { font-size: .73rem; color: #9ca3af; margin-top: 2px; }

.no-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 99px; font-size: .68rem; font-weight: 700;
}
.no-badge-green  { background: rgba(34,197,94,.1);  color: #16a34a; border: 1px solid rgba(34,197,94,.25);  }
.no-badge-red    { background: rgba(239,68,68,.1);  color: #b91c1c; border: 1px solid rgba(239,68,68,.25);  }
.no-badge-gray   { background: rgba(156,163,175,.1); color: #6b7280; border: 1px solid rgba(156,163,175,.2);}
html.dark .no-badge-green { color: #4ade80; }
html.dark .no-badge-red   { color: #f87171; }

.no-table { width: 100%; border-collapse: collapse; }
.no-table th {
    padding: 8px 16px;
    font-size: .68rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase;
    color: #9ca3af; border-bottom: 1px solid var(--topbar-border); text-align: left;
}
.no-table td { padding: 9px 16px; font-size: .82rem; color: var(--text); border-bottom: 1px solid var(--topbar-border); }
.no-table tbody tr:last-child td { border-bottom: none; }
.no-table tbody tr:hover { background: rgba(252,185,19,.04); }

.role-chip {
    display: inline-block;
    padding: 1px 8px; border-radius: 99px; font-size: .63rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
}
.chip-sa   { background: rgba(252,185,19,.15); color: #b45309; border: 1px solid rgba(252,185,19,.3); }
.chip-adm  { background: rgba(99,102,241,.12);  color: #4338ca; border: 1px solid rgba(99,102,241,.25); }
.chip-mgr  { background: rgba(16,185,129,.1);   color: #065f46; border: 1px solid rgba(16,185,129,.25); }
.chip-vwr  { background: rgba(156,163,175,.1);  color: #374151; border: 1px solid rgba(156,163,175,.2); }
html.dark .chip-sa  { color: #fcd34d; }
html.dark .chip-adm { color: #a5b4fc; }
html.dark .chip-mgr { color: #6ee7b7; }
html.dark .chip-vwr { color: #d1d5db; }

/* Summary stats */
.no-stats { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 22px; }
.no-stat {
    flex: 1; min-width: 130px;
    background: var(--card);
    border: 1px solid var(--topbar-border);
    border-radius: 12px; padding: 14px 16px;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
}
.no-stat .st-label { font-size: .62rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; }
.no-stat .st-val   { font-size: 1.5rem; font-weight: 900; color: var(--text); margin-top: 4px; line-height: 1.1; }
.no-stat .st-sub   { font-size: .68rem; color: #9ca3af; margin-top: 3px; }
</style>
@endpush

@section('content')

<div class="no-wrap">
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title">Notification Opt-Out Overview</h1>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:2px;">
                See which users have opted out of each email alert type.
            </p>
        </div>
    </div>

    {{-- ── Summary stat cards ─────────────────────────────── --}}
    @php
        $totalOptOuts = collect($overview)->sum('opted_out');
        $totalUsers   = collect($overview)->flatMap(fn($o) => $o['rows']->pluck('user.id'))->unique()->count();
        $totalTypes   = count($overview);
    @endphp
    <div class="no-stats">
        <div class="no-stat" style="border-top:3px solid #f59e0b;">
            <div class="st-label">Alert Types</div>
            <div class="st-val">{{ $totalTypes }}</div>
            <div class="st-sub">configured</div>
        </div>
        <div class="no-stat" style="border-top:3px solid #6366f1;">
            <div class="st-label">Eligible Users</div>
            <div class="st-val">{{ $totalUsers }}</div>
            <div class="st-sub">across all types</div>
        </div>
        <div class="no-stat" style="border-top:3px solid {{ $totalOptOuts > 0 ? '#ef4444' : '#22c55e' }};">
            <div class="st-label">Total Opt-Outs</div>
            <div class="st-val" style="color:{{ $totalOptOuts > 0 ? '#ef4444' : '#22c55e' }};">{{ $totalOptOuts }}</div>
            <div class="st-sub">across all alert types</div>
        </div>
    </div>

    {{-- ── Per alert type tables ───────────────────────────── --}}
    @foreach($overview as $alertKey => $data)
    @php
        $meta     = $data['meta'];
        $rows     = $data['rows'];
        $optedOut = $data['opted_out'];
        $optedIn  = $rows->count() - $optedOut;
    @endphp
    <div class="no-card">
        <div class="no-card-head">
            <div>
                <div class="no-type-name">{{ $meta['label'] }}</div>
                <div class="no-type-desc">{{ $meta['description'] }}</div>
                <div style="margin-top:5px;display:flex;gap:6px;flex-wrap:wrap;">
                    @if($meta['roles'] === null)
                        <span class="no-badge no-badge-gray">All users</span>
                    @else
                        @foreach($meta['roles'] as $r)
                            @php
                                $rChip = ['super_admin'=>'chip-sa','admin'=>'chip-adm','manager'=>'chip-mgr','viewer'=>'chip-vwr'][$r] ?? 'chip-vwr';
                                $rLabel = str_replace('_', ' ', $r);
                            @endphp
                            <span class="role-chip {{ $rChip }}">{{ $rLabel }}</span>
                        @endforeach
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;">
                <span class="no-badge no-badge-green">✓ {{ $optedIn }} opted in</span>
                @if($optedOut > 0)
                    <span class="no-badge no-badge-red">✗ {{ $optedOut }} opted out</span>
                @endif
            </div>
        </div>

        @if($rows->isEmpty())
        <div style="padding:20px;text-align:center;color:#9ca3af;font-size:.82rem;">No eligible users.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="no-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    @php
                        $u    = $row['user'];
                        $on   = $row['enabled'];
                        $rChip = ['super_admin'=>'chip-sa','admin'=>'chip-adm','manager'=>'chip-mgr','viewer'=>'chip-vwr'][$u->role] ?? 'chip-vwr';
                    @endphp
                    <tr style="{{ !$on ? 'background:rgba(239,68,68,.03)' : '' }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:9px;">
                                @if($u->avatar_path)
                                    <img src="{{ asset('storage/'.$u->avatar_path) }}" alt="{{ $u->name }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:2px solid var(--topbar-border);">
                                @else
                                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#fcb913,#db9f01);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:#001a4d;flex-shrink:0;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                                @endif
                                <span style="font-weight:600;">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td><span class="role-chip {{ $rChip }}">{{ str_replace('_',' ',$u->role) }}</span></td>
                        <td style="color:#9ca3af;font-size:.78rem;">{{ $u->email }}</td>
                        <td style="text-align:center;">
                            @if($on)
                                <span class="no-badge no-badge-green">✓ Opted In</span>
                            @else
                                <span class="no-badge no-badge-red">✗ Opted Out</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endforeach

</div>
@endsection
