@extends('layouts.app')
@section('page-title', 'Notification Preferences')

@push('styles')
<style>
.np-wrap { max-width: 640px; }
.np-card {
    background: var(--card);
    border: 1px solid var(--topbar-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.np-header {
    padding: 18px 22px;
    border-bottom: 1px solid var(--topbar-border);
    display: flex;
    align-items: center;
    gap: 12px;
}
.np-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: rgba(252,185,19,.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.np-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 22px;
    border-bottom: 1px solid var(--topbar-border);
}
.np-row:last-child { border-bottom: none; }
.np-row-info { flex: 1; }
.np-row-label { font-size: .88rem; font-weight: 700; color: var(--text); }
.np-row-desc  { font-size: .74rem; color: #9ca3af; margin-top: 3px; line-height: 1.4; }

/* Toggle switch */
.np-toggle { position: relative; flex-shrink: 0; margin-top: 2px; }
.np-toggle input[type=checkbox] { display: none; }
.np-toggle-track {
    display: block;
    width: 46px; height: 26px;
    border-radius: 99px;
    background: #d1d5db;
    cursor: pointer;
    transition: background .2s;
    position: relative;
}
.np-toggle input:checked + .np-toggle-track { background: #22c55e; }
.np-toggle-track::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
    transition: left .2s;
}
.np-toggle input:checked + .np-toggle-track::after { left: 23px; }
html.dark .np-toggle-track { background: #4b5563; }
html.dark .np-toggle input:checked + .np-toggle-track { background: #22c55e; }

.np-status-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .65rem; font-weight: 700; padding: 2px 8px;
    border-radius: 99px; margin-top: 5px;
}
.badge-in  { background: rgba(34,197,94,.1);  color: #16a34a; border: 1px solid rgba(34,197,94,.25);  }
.badge-out { background: rgba(239,68,68,.1);  color: #b91c1c; border: 1px solid rgba(239,68,68,.25);  }
html.dark .badge-in  { color: #4ade80; }
html.dark .badge-out { color: #f87171; }
</style>
@endpush

@section('content')

<div class="np-wrap">
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title">Notification Preferences</h1>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:2px;">
                <a href="{{ route('profile.edit') }}" style="color:#fcb913;">My Profile</a>
                &rsaquo; Notification Preferences
            </p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn-cancel">&larr; Profile</a>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#16a34a;border-radius:10px;padding:10px 16px;margin-bottom:16px;font-size:.82rem;font-weight:600;display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="np-card">
        <div class="np-header">
            <div class="np-icon">🔔</div>
            <div>
                <p style="font-size:.88rem;font-weight:800;color:var(--text);margin:0;">Email Alert Subscriptions</p>
                <p style="font-size:.72rem;color:#9ca3af;margin:2px 0 0;">Toggle off to stop receiving specific email alerts. In-app notifications are unaffected.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('notification-preferences.update') }}">
            @csrf
            @method('PATCH')

            @foreach($prefs as $key => $enabled)
            @php $meta = $types[$key]; @endphp
            <div class="np-row">
                <div class="np-row-info">
                    <div class="np-row-label">{{ $meta['label'] }}</div>
                    <div class="np-row-desc">{{ $meta['description'] }}</div>
                    <div>
                        <span class="np-status-badge {{ $enabled ? 'badge-in' : 'badge-out' }}" id="badge-{{ $key }}">
                            {{ $enabled ? '✓ Opted In' : '✗ Opted Out' }}
                        </span>
                    </div>
                </div>
                <label class="np-toggle">
                    <input type="checkbox" name="prefs[{{ $key }}]" value="1"
                           {{ $enabled ? 'checked' : '' }}
                           onchange="updateBadge('{{ $key }}', this.checked)">
                    <span class="np-toggle-track"></span>
                </label>
            </div>
            @endforeach

            <div style="padding: 16px 22px; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--topbar-border); background:rgba(0,0,0,.02);">
                <a href="{{ route('profile.edit') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit" style="padding:9px 22px;font-size:.85rem;">Save Preferences</button>
            </div>
        </form>
    </div>

    <p style="font-size:.72rem;color:#6b7280;margin-top:12px;text-align:center;">
        Only email delivery is controlled here. You will still see in-app bell notifications regardless of these settings.
    </p>
</div>

@push('scripts')
<script>
function updateBadge(key, checked) {
    const badge = document.getElementById('badge-' + key);
    if (!badge) return;
    badge.className = 'np-status-badge ' + (checked ? 'badge-in' : 'badge-out');
    badge.textContent = checked ? '✓ Opted In' : '✗ Opted Out';
}
</script>
@endpush
@endsection
