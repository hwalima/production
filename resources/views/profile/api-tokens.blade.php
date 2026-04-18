@extends('layouts.app')
@section('page-title', 'API Tokens')

@push('styles')
<style>
.at-wrap { max-width: 720px; }

.at-card {
    background: var(--card);
    border: 1px solid var(--topbar-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    margin-bottom: 24px;
}
.at-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid var(--topbar-border);
    background: rgba(0,0,0,.02);
    display: flex; align-items: center; justify-content: space-between;
}
html.dark .at-card-head { background: rgba(255,255,255,.02); }
.at-card-title { font-size: .85rem; font-weight: 800; color: var(--text); }
.at-card-body  { padding: 20px; }

/* Token reveal banner */
.at-new-token {
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 20px;
}
html.dark .at-new-token {
    background: rgba(34,197,94,.08);
    border-color: rgba(34,197,94,.25);
}
.at-new-token-label { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #16a34a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
html.dark .at-new-token-label { color: #4ade80; }
.at-token-code {
    font-family: 'Courier New', monospace;
    font-size: .78rem; font-weight: 700;
    word-break: break-all;
    background: rgba(0,0,0,.05);
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 8px;
    padding: 10px 14px;
    color: var(--text);
    margin-bottom: 10px;
}
html.dark .at-token-code { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.08); }
.at-copy-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 8px; font-size: .78rem; font-weight: 700;
    background: #22c55e; color: #fff; border: none; cursor: pointer;
    transition: background .15s;
}
.at-copy-btn:hover { background: #16a34a; }
.at-warn { font-size: .72rem; color: #ca8a04; margin-top: 8px; }
html.dark .at-warn { color: #fbbf24; }

/* Token list */
.at-token-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--topbar-border);
    gap: 12px;
}
.at-token-row:last-child { border-bottom: none; }
.at-token-name { font-weight: 700; font-size: .84rem; color: var(--text); }
.at-token-meta { font-size: .72rem; color: #9ca3af; margin-top: 2px; }
.at-revoke-btn {
    padding: 5px 12px; border-radius: 8px; font-size: .75rem; font-weight: 700;
    background: rgba(239,68,68,.1); color: #b91c1c; border: 1px solid rgba(239,68,68,.2);
    cursor: pointer; transition: all .15s; flex-shrink: 0;
}
.at-revoke-btn:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
html.dark .at-revoke-btn { color: #f87171; }

/* Create form */
.at-form { display: flex; gap: 10px; flex-wrap: wrap; }
.at-form input[type=text] {
    flex: 1; min-width: 200px;
    padding: 9px 14px; border-radius: 10px;
    border: 1.5px solid var(--topbar-border);
    background: var(--input-bg, var(--card));
    color: var(--text); font-size: .84rem;
    outline: none;
}
.at-form input[type=text]:focus { border-color: #fcb913; }
.at-form button {
    padding: 9px 18px; border-radius: 10px; font-size: .84rem; font-weight: 700;
    background: #fcb913; color: #001a4d; border: none; cursor: pointer;
    transition: background .15s;
}
.at-form button:hover { background: #db9f01; }

/* Docs section */
.at-endpoint {
    background: rgba(0,0,0,.04);
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 8px;
    display: flex; align-items: flex-start; gap: 10px;
}
html.dark .at-endpoint { background: rgba(255,255,255,.05); }
.at-method {
    font-size: .65rem; font-weight: 900; padding: 2px 7px; border-radius: 4px;
    flex-shrink: 0; margin-top: 1px; letter-spacing: .04em;
}
.at-method-get  { background: rgba(34,197,94,.12);  color: #16a34a; }
.at-method-post { background: rgba(99,102,241,.12);  color: #4338ca; }
.at-method-del  { background: rgba(239,68,68,.12);   color: #b91c1c; }
html.dark .at-method-get  { color: #4ade80; }
html.dark .at-method-post { color: #a5b4fc; }
html.dark .at-method-del  { color: #f87171; }
.at-ep-path { font-family: 'Courier New', monospace; font-size: .78rem; font-weight: 700; color: var(--text); }
.at-ep-desc { font-size: .72rem; color: #9ca3af; margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="at-wrap">

    <div class="page-header" style="margin-bottom:20px;">
        <div>
            <h1 class="page-title">API Tokens</h1>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:2px;">
                Generate Bearer tokens for accessing the read-only JSON API from dashboards or mobile apps.
            </p>
        </div>
        <a href="{{ route('profile.edit') }}"
           style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;font-size:.8rem;font-weight:700;border-radius:10px;border:1px solid var(--topbar-border);background:var(--card);color:var(--text);text-decoration:none;">
            ← Profile
        </a>
    </div>

    {{-- ── New token banner (shown once after creation) ──────────────── --}}
    @if(session('new_token'))
    <div class="at-new-token">
        <div class="at-new-token-label">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Token created — copy it now, it will not be shown again
        </div>
        <div class="at-token-code" id="newTokenValue">{{ session('new_token') }}</div>
        <button class="at-copy-btn" onclick="copyToken()">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span id="copyLabel">Copy Token</span>
        </button>
        <div class="at-warn">⚠ Store this token securely. It won't be visible after you leave this page.</div>
    </div>
    @endif

    @if(session('success'))
    <div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.82rem;color:#16a34a;font-weight:600;">
        ✓ {{ session('success') }}
    </div>
    @endif

    {{-- ── Existing tokens ──────────────────────────────────────────── --}}
    <div class="at-card">
        <div class="at-card-head">
            <div class="at-card-title">Active Tokens</div>
            <span style="font-size:.72rem;color:#9ca3af;">{{ $tokens->count() }} token{{ $tokens->count() !== 1 ? 's' : '' }}</span>
        </div>
        <div class="at-card-body" style="padding:4px 20px;">
            @forelse($tokens as $token)
            <div class="at-token-row">
                <div>
                    <div class="at-token-name">{{ $token->name }}</div>
                    <div class="at-token-meta">
                        Created {{ $token->created_at->diffForHumans() }}
                        @if($token->last_used_at)
                            &bull; Last used {{ $token->last_used_at->diffForHumans() }}
                        @else
                            &bull; Never used
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('api-tokens.destroy', $token->id) }}"
                      onsubmit="return confirm('Revoke token \'{{ addslashes($token->name) }}\'? Any app using it will lose access immediately.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="at-revoke-btn">Revoke</button>
                </form>
            </div>
            @empty
            <div style="padding:20px 0;text-align:center;color:#9ca3af;font-size:.82rem;">
                No tokens yet. Create one below.
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── Create new token ─────────────────────────────────────────── --}}
    <div class="at-card">
        <div class="at-card-head">
            <div class="at-card-title">Create New Token</div>
        </div>
        <div class="at-card-body">
            <form method="POST" action="{{ route('api-tokens.store') }}" class="at-form">
                @csrf
                <input type="text" name="token_name" placeholder="e.g. Mobile App, Dashboard" maxlength="100"
                       value="{{ old('token_name') }}" autocomplete="off" required>
                @error('token_name')
                    <span style="font-size:.72rem;color:#ef4444;width:100%;">{{ $message }}</span>
                @enderror
                <button type="submit">Generate Token</button>
            </form>
            <p style="font-size:.72rem;color:#9ca3af;margin-top:10px;">
                All tokens have read-only scope. Use the token as a Bearer header:<br>
                <code style="font-size:.72rem;">Authorization: Bearer {your-token}</code>
            </p>
        </div>
    </div>

    {{-- ── API reference ─────────────────────────────────────────────── --}}
    <div class="at-card">
        <div class="at-card-head">
            <div class="at-card-title">API Reference</div>
            <span style="font-size:.72rem;color:#9ca3af;">Base URL: <code style="font-size:.72rem;">{{ config('app.url') }}/api</code></span>
        </div>
        <div class="at-card-body" style="padding-bottom:8px;">

            <p style="font-size:.75rem;color:#9ca3af;margin-bottom:14px;">
                All endpoints below require <code>Authorization: Bearer {token}</code> header. Responses are JSON (paginated where noted).
                Accepted date params: <code>from=YYYY-MM-DD</code>, <code>to=YYYY-MM-DD</code> (default: current month).
            </p>

            @php
            $endpoints = [
                ['POST',   '/auth/token',              'Issue a token. Body: { email, password, token_name? }'],
                ['DELETE', '/auth/token',               'Revoke the current token.'],
                ['GET',    '/v1/dashboard',             'KPI summary — gold today, MTD, targets, alerts.'],
                ['GET',    '/v1/production',            'Paginated production records. Params: from, to, shift, per_page.'],
                ['GET',    '/v1/production/summary',    'Aggregated totals + shift breakdown. Params: from, to, shift.'],
                ['GET',    '/v1/consumables',           'Paginated stores list with current stock. Params: category, per_page.'],
                ['GET',    '/v1/consumables/low-stock', 'Items at or below reorder level.'],
                ['GET',    '/v1/action-items',          'Paginated action items. Params: from, to, status, priority, per_page.'],
                ['GET',    '/v1/machines',              'Machine runtimes. Params: from, to, filter=overdue, per_page.'],
                ['GET',    '/v1/drilling',              'Drilling records. Params: from, to, per_page.'],
                ['GET',    '/v1/blasting',              'Blasting records. Params: from, to, per_page.'],
                ['GET',    '/v1/labour-energy',         'Labour & energy cost records. Params: from, to, per_page.'],
            ];
            $methodClass = ['GET' => 'at-method-get', 'POST' => 'at-method-post', 'DELETE' => 'at-method-del'];
            @endphp

            @foreach($endpoints as $ep)
            <div class="at-endpoint">
                <span class="at-method {{ $methodClass[$ep[0]] }}">{{ $ep[0] }}</span>
                <div>
                    <div class="at-ep-path">/api{{ $ep[1] }}</div>
                    <div class="at-ep-desc">{{ $ep[2] }}</div>
                </div>
            </div>
            @endforeach

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function copyToken() {
    const val = document.getElementById('newTokenValue').textContent.trim();
    navigator.clipboard.writeText(val).then(() => {
        const lbl = document.getElementById('copyLabel');
        lbl.textContent = 'Copied!';
        setTimeout(() => { lbl.textContent = 'Copy Token'; }, 2000);
    });
}
</script>
@endpush
