@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

<style>
/* ══════════════════════════════════════════════════════════════
   DESIGN TOKENS
══════════════════════════════════════════════════════════════ */
:root {
    --g-gold:   linear-gradient(135deg,#f59e0b,#fcc104);
    --g-purple: linear-gradient(135deg,#7c3aed,#a78bfa);
    --g-sky:    linear-gradient(135deg,#0369a1,#38bdf8);
    --g-orange: linear-gradient(135deg,#ea580c,#fb923c);
    --g-green:  linear-gradient(135deg,#047857,#34d399);
    --g-red:    linear-gradient(135deg,#b91c1c,#f87171);
    --cr: 20px;
    --sh: 0 4px 24px rgba(0,0,0,.15);
    --sh-hover: 0 12px 40px rgba(0,0,0,.26);
}

/* ── Glass Card ── */
.gc {
    background: var(--card);
    border-radius: var(--cr);
    box-shadow: var(--sh);
    border: 1px solid var(--topbar-border);
    position: relative; overflow: hidden;
    transition: transform .2s ease, box-shadow .2s ease;
}
.gc:hover { transform: translateY(-3px); box-shadow: var(--sh-hover); }
html.dark .gc { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }

/* ── KPI card shell ── */
.kpi { padding: 19px 19px 15px; }

/* Glow orb */
.orb {
    position: absolute; width: 110px; height: 110px; border-radius: 50%;
    top: -35px; right: -25px; pointer-events: none; opacity: .18; filter: blur(24px);
}
.ob-gold   { background: #f59e0b; }
.ob-purple { background: #a78bfa; }
.ob-sky    { background: #38bdf8; }
.ob-orange { background: #fb923c; }
.ob-green  { background: #34d399; }
.ob-red    { background: #f87171; }

/* Icon badge */
.ib {
    width: 44px; height: 44px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; margin-bottom: 14px; box-shadow: 0 4px 14px rgba(0,0,0,.25);
}
.ib-gold   { background: var(--g-gold); }
.ib-purple { background: var(--g-purple); }
.ib-sky    { background: var(--g-sky); }
.ib-orange { background: var(--g-orange); }
.ib-green  { background: var(--g-green); }
.ib-red    { background: var(--g-red); }

/* KPI text */
.kt  { font-size: .61rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; }
.kv  { font-size: 1.65rem; font-weight: 900; line-height: 1.1; margin: 4px 0 2px; color: var(--text); }
.ks  { font-size: .7rem; color: #9ca3af; }
.kf  { margin-top: 9px; padding-top: 7px; border-top: 1px solid var(--topbar-border); font-size: .7rem; color: #9ca3af; }
.kf b{ color: var(--text); font-weight: 700; }

/* Gradient text */
.gv-gold   { background: var(--g-gold);   -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.gv-purple { background: var(--g-purple); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.gv-sky    { background: var(--g-sky);    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.gv-orange { background: var(--g-orange); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.gv-green  { background: var(--g-green);  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

/* Progress strip */
.ptrack { margin-top: 10px; height: 5px; border-radius: 99px; background: rgba(0,0,0,.08); overflow: hidden; }
html.dark .ptrack { background: rgba(255,255,255,.08); }
.pfill  { height: 100%; border-radius: 99px; transition: width .8s cubic-bezier(.4,0,.2,1); }
.plabel { margin-top: 4px; font-size: .63rem; font-weight: 600; color: #9ca3af; }

/* Status pill */
.pill {
    display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px;
    border-radius: 99px; font-size: .65rem; font-weight: 700; margin-top: 6px;
}
.p-green  { background: rgba(52,211,153,.1);  color: #34d399; border: 1px solid rgba(52,211,153,.25); }
.p-amber  { background: rgba(251,191,36,.1);  color: #fbbf24; border: 1px solid rgba(251,191,36,.25); }
.p-red    { background: rgba(248,113,113,.1); color: #f87171; border: 1px solid rgba(248,113,113,.25); }

/* ── Panel cards (proj / fleet) ── */
.pcrd { padding: 22px 24px; }
.slbl {
    font-size: .59rem; font-weight: 700; letter-spacing: .12em;
    text-transform: uppercase; color: #9ca3af;
    display: flex; align-items: center; gap: 9px; margin-bottom: 18px;
}
.slbl::before {
    content: ''; width: 3px; height: 14px; border-radius: 99px;
    background: var(--g-gold); flex-shrink: 0;
}

/* Projections */
.pval { font-size: 2.5rem; font-weight: 900; line-height: 1; }
.psub { font-size: .72rem; color: #9ca3af; margin-top: 5px; }
.mgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--topbar-border); }
.mgl  { font-size: .57rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #9ca3af; }
.mgv  { font-size: .94rem; font-weight: 800; color: var(--text); margin-top: 3px; }

/* Fleet */
.fseg { display: flex; height: 7px; border-radius: 99px; overflow: hidden; gap: 3px; margin: 6px 0 16px; }
.fseg span { border-radius: 99px; }
.frow { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--topbar-border); }
.frow:last-of-type { border-bottom: none; padding-bottom: 0; }
.fdot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.flbl { font-size: .8rem; color: var(--text); flex: 1; }
.fnum { font-size: 1.1rem; font-weight: 900; }
.fpct { font-size: .62rem; color: #9ca3af; margin-left: 3px; }
.fbtn {
    margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 10px 16px; border-radius: 12px; font-size: .75rem; font-weight: 700;
    text-decoration: none; transition: opacity .15s, transform .15s;
}
.fbtn:hover { opacity: .82; transform: translateY(-1px); }
.fb-r { background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.2); color: #f87171; }
.fb-a { background: rgba(251,191,36,.1);  border: 1px solid rgba(251,191,36,.2);  color: #fbbf24; }
.fb-g { background: rgba(52,211,153,.1);  border: 1px solid rgba(52,211,153,.2);  color: #34d399; cursor: default; }

/* ── Weather ── */
.wcrd {
    border-radius: var(--cr); box-shadow: var(--sh); padding: 22px 22px;
    position: relative; overflow: hidden;
    background: linear-gradient(145deg,#0f2044,#1a3a6b);
    transition: box-shadow .2s;
}
.wcrd:hover { box-shadow: var(--sh-hover); }
.wcrd::before {
    content:''; position:absolute; width:200px; height:200px; border-radius:50%;
    background:rgba(255,255,255,.05); top:-60px; right:-60px; pointer-events:none;
}
.wcrd.wc-clear   { background: linear-gradient(145deg,#0c3b6e,#0369a1,#0ea5e9); }
.wcrd.wc-cloudy  { background: linear-gradient(145deg,#1a2740,#263957,#334a6b); }
.wcrd.wc-rain    { background: linear-gradient(145deg,#0f1f40,#1a3470,#2048a0); }
.wcrd.wc-storm   { background: linear-gradient(145deg,#120b30,#1e1050,#33186a); }
.wcrd.wc-fog     { background: linear-gradient(145deg,#253345,#3d4f63,#566b80); }
html:not(.dark) .wcrd.wc-clear  { background: linear-gradient(145deg,#0284c7,#0ea5e9,#56c8f0); }
html:not(.dark) .wcrd.wc-cloudy { background: linear-gradient(145deg,#3d5268,#546a80,#6e8698); }
html:not(.dark) .wcrd.wc-rain   { background: linear-gradient(145deg,#1e40af,#2563eb,#4b88ef); }
html:not(.dark) .wcrd.wc-storm  { background: linear-gradient(145deg,#1e1b4b,#3730a3,#5046c8); }
.wey  { font-size:.6rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:rgba(255,255,255,.45); margin-bottom:12px; }
.wicn { font-size:3rem; line-height:1; filter:drop-shadow(0 2px 10px rgba(0,0,0,.4)); }
.wtmp { font-size:3rem; font-weight:900; color:#fff; line-height:1; }
.wdsc { font-size:.8rem; color:rgba(255,255,255,.65); font-weight:500; margin-top:3px; }
.wpls { display:flex; gap:6px; flex-wrap:wrap; margin:12px 0 14px; }
.wpil { padding:4px 11px; border-radius:99px; font-size:.68rem; font-weight:700; color:#fff; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.18); backdrop-filter:blur(8px); }
.wfcst { display:flex; gap:6px; overflow-x:auto; padding-bottom:2px; }
.wfcst::-webkit-scrollbar { height:3px; }
.wfcst::-webkit-scrollbar-thumb { background:rgba(255,255,255,.25); border-radius:99px; }
.wday { text-align:center; min-width:54px; flex-shrink:0; padding:8px 8px 7px; border-radius:13px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.14); backdrop-filter:blur(8px); }
.wday .dn { font-size:.58rem; color:rgba(255,255,255,.55); font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
.wday .di { font-size:1.25rem; margin:4px 0; }
.wday .dh { font-size:.8rem; font-weight:800; color:#fff; }
.wday .dl { font-size:.7rem; color:rgba(255,255,255,.5); }
.wday .dr { font-size:.62rem; color:#7dd3fc; margin-top:2px; }
.walt { margin-top:10px; padding:8px 12px; border-radius:10px; background:rgba(254,243,199,.9); border:1px solid rgba(252,211,77,.7); color:#92400e; font-size:.72rem; display:flex; gap:7px; }
.wldg { text-align:center; padding:32px; color:rgba(255,255,255,.45); font-size:.84rem; }

/* ── Charts ── */
.ccrd { padding: 22px 24px; }
.cht-ttl { font-size: .59rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #9ca3af; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.cht-ttl::before { content:''; width:3px; height:14px; border-radius:99px; background:var(--g-purple); flex-shrink:0; }

/* ── Quick Add ── */
.qabtn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 13px 16px; border-radius: 16px; font-weight: 700; font-size: .8rem;
    text-decoration: none; letter-spacing: .02em;
    transition: transform .18s, box-shadow .18s, filter .18s;
    position: relative; overflow: hidden;
}
.qabtn:hover { transform: translateY(-3px); filter: brightness(1.09); }
.qabtn::after { content:''; position:absolute; inset:0; background:linear-gradient(180deg,rgba(255,255,255,.15) 0%,transparent 60%); pointer-events:none; }
.qa-gold   { background: var(--g-gold);   color: #1a1200; box-shadow: 0 4px 18px rgba(245,158,11,.35); }
.qa-sky    { background: var(--g-sky);    color: #fff;    box-shadow: 0 4px 18px rgba(56,189,248,.3); }
.qa-red    { background: var(--g-red);    color: #fff;    box-shadow: 0 4px 18px rgba(248,113,113,.3); }
.qa-purple { background: var(--g-purple); color: #fff;    box-shadow: 0 4px 18px rgba(167,139,250,.3); }

/* ── Filter bar ── */
.fbar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    background: var(--card); border: 1px solid var(--topbar-border);
    border-radius: 16px; padding: 12px 16px;
}
html.dark .fbar { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.08); }
.fbar-label { font-size: .65rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: #9ca3af; white-space: nowrap; }
.fbar input[type=date] {
    background: var(--input-bg, var(--bg)); border: 1px solid var(--topbar-border);
    border-radius: 10px; padding: 7px 11px; font-size: .78rem; color: var(--text);
    outline: none; transition: border-color .15s;
    color-scheme: dark;
}
.fbar input[type=date]:focus { border-color: #f59e0b; }
html:not(.dark) .fbar input[type=date] { color-scheme: light; }
.fbar-sep { color: #6b7280; font-size: .75rem; }
.fbar-apply {
    padding: 7px 18px; border-radius: 10px; font-size: .75rem; font-weight: 700;
    background: var(--g-gold); color: #1a1200; border: none; cursor: pointer;
    transition: filter .15s, transform .15s;
}
.fbar-apply:hover { filter: brightness(1.1); transform: translateY(-1px); }
.fbar-presets { display: flex; gap: 6px; flex-wrap: wrap; margin-left: auto; }
.fbar-preset {
    padding: 5px 12px; border-radius: 8px; font-size: .68rem; font-weight: 700;
    background: transparent; border: 1px solid var(--topbar-border); color: #9ca3af;
    cursor: pointer; text-decoration: none; transition: background .15s, color .15s;
}
.fbar-preset:hover, .fbar-preset.active { background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.4); color: #f59e0b; }
.fbar-active-range {
    display: inline-flex; align-items: center; gap: 6px; font-size: .68rem; color: #f59e0b;
    font-weight: 600; background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.2);
    border-radius: 8px; padding: 4px 10px;
}

/* ── KPI grid ── */
.kpi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.75rem; }
.two-col  { display:grid; grid-template-columns:repeat(2,1fr); gap:1.75rem; }
@media(max-width:1023px){
    .kpi-grid { grid-template-columns:repeat(2,1fr); gap:1.25rem; }
    .kpi-grid > :last-child:nth-child(odd) { grid-column:span 2; }
    .two-col  { grid-template-columns:1fr; gap:1.25rem; }
}
@media(max-width:599px){
    .kpi-grid { grid-template-columns:1fr; gap:1rem; }
    .two-col  { grid-template-columns:1fr; gap:1rem; }
}

/* ── Filter bar mobile ── */
.fbar-ctrl { display:flex; align-items:center; gap:8px; flex-wrap:nowrap; }
@media(max-width:767px){
    .fbar { flex-direction:column; align-items:stretch; gap:10px; }
    .fbar-label { text-align:center; }
    .fbar-ctrl { flex-wrap:wrap; }
    .fbar-ctrl input[type=date] { flex:1; min-width:120px; }
    .fbar-apply { flex:1; padding:9px; text-align:center; }
    .fbar-presets { margin-left:0 !important; justify-content:flex-start; }
    .fbar-active-range { width:100%; justify-content:center; }
}

/* ── Page heading ── */
.dh-title { font-size: 1.6rem; font-weight: 900; color: var(--text); line-height: 1; }
.dh-sub   { font-size: .78rem; color: #9ca3af; margin-top: 5px; }
.loc-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 13px; border-radius: 99px; font-size: .72rem; font-weight: 600;
    background: var(--card); border: 1px solid var(--topbar-border); color: #9ca3af;
}
</style>

<div class="space-y-6 pb-6">

    {{-- HEADING --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="dh-title">Mine Dashboard</h1>
            <p class="dh-sub">
                {{ \Carbon\Carbon::now()->format('l, d F Y') }}
                &nbsp;·&nbsp; Day <b style="color:var(--text)">{{ $dayOfMonth }}</b> of {{ $daysInMonth }}
            </p>
        </div>
        <span class="loc-badge hidden sm:inline-flex">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $mineLocation }}
        </span>
    </div>

    {{-- DATE RANGE FILTER --}}
    <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
        <div class="fbar">
            <span class="fbar-label">📅 Date Range</span>
            <div class="fbar-ctrl">
                <input type="date" name="from" id="filterFrom" value="{{ $filterFromStr }}" max="{{ date('Y-m-d') }}">
                <span class="fbar-sep">→</span>
                <input type="date" name="to"   id="filterTo"   value="{{ $filterToStr }}"  max="{{ date('Y-m-d') }}">
                <button type="submit" class="fbar-apply">Apply</button>
            </div>
            @if(!$isDefaultRange)
                <span class="fbar-active-range">
                    🔍 {{ \Carbon\Carbon::parse($filterFromStr)->format('d M') }} – {{ \Carbon\Carbon::parse($filterToStr)->format('d M Y') }}
                    &nbsp;<a href="{{ route('dashboard') }}" style="color:inherit;text-decoration:none;opacity:.6;font-size:.9em">✕ clear</a>
                </span>
            @endif
            <div class="fbar-presets">
                @php
                    $now = \Carbon\Carbon::now();
                    $presets = [
                        'This Month'   => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
                        'Last Month'   => [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
                        'Last 7 Days'  => [$now->copy()->subDays(6)->toDateString(), $now->toDateString()],
                        'Last 30 Days' => [$now->copy()->subDays(29)->toDateString(), $now->toDateString()],
                        'This Year'    => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
                    ];
                @endphp
                @foreach($presets as $label => [$pFrom, $pTo])
                    @php $isActive = $filterFromStr === $pFrom && $filterToStr === $pTo; @endphp
                    <a href="{{ route('dashboard', ['from' => $pFrom, 'to' => $pTo]) }}"
                       class="fbar-preset {{ $isActive ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </form>

    {{-- 30-DAY PRODUCTION TREND (before KPI cards) --}}
    <div class="gc ccrd">
        <p class="cht-ttl">Production Trend — {{ \Carbon\Carbon::parse($filterFromStr)->format('d M') }} to {{ \Carbon\Carbon::parse($filterToStr)->format('d M Y') }}</p>
        <canvas id="trendChart" height="80"></canvas>
    </div>

    {{-- ROW 1 — KPI CARDS --}}
    <div class="kpi-grid">

        {{-- Gold Smelted --}}
        <div class="gc kpi">
            <div class="orb ob-gold"></div>
            <div class="ib ib-gold">🥇</div>
            <p class="kt">Gold Smelted</p>
            <p class="kv gv-gold">{{ number_format($goldSmeltedMonth, 3) }}</p>
            <p class="ks">g this month</p>
            <div class="ptrack"><div class="pfill" style="width:{{ $goldTargetPct }}%;background:var(--g-gold);"></div></div>
            <p class="plabel">{{ $goldTargetPct }}% of {{ number_format($goldTarget, 0) }} g target</p>
        </div>

        {{-- Implied Grade --}}
        <div class="gc kpi">
            <div class="orb ob-purple"></div>
            <div class="ib ib-purple">🔬</div>
            <p class="kt">Implied Grade</p>
            <p class="kv gv-purple">{{ number_format($impliedGrade, 2) }}</p>
            <p class="ks">g/t head grade</p>
            <p class="kf">Purity <b>{{ number_format($avgPurity, 1) }}%</b></p>
        </div>

        {{-- Ore Hoisted --}}
        <div class="gc kpi">
            <div class="orb ob-sky"></div>
            <div class="ib ib-sky">⛏️</div>
            <p class="kt">Ore Hoisted</p>
            <p class="kv gv-sky">{{ number_format($oreHoistedMonth, 0) }}</p>
            <p class="ks">tonnes</p>
            <p class="kf">Waste <b>{{ number_format($wasteHoistedMonth, 0) }} t</b></p>
        </div>

        {{-- Ore Milled --}}
        <div class="gc kpi">
            <div class="orb ob-orange"></div>
            <div class="ib ib-orange">⚙️</div>
            <p class="kt">Ore Milled</p>
            <p class="kv gv-orange">{{ number_format($oreMilledMonth, 0) }}</p>
            <p class="ks">tonnes</p>
            <div class="ptrack"><div class="pfill" style="width:{{ min(100,$millingEfficiency) }}%;background:var(--g-orange);"></div></div>
            <p class="plabel">{{ $millingEfficiency }}% efficiency</p>
        </div>

        {{-- Action Items --}}
        @php $aiOverdue = \App\Models\ActionItem::overdueCount(); @endphp
        <a href="{{ route('action-items.index') }}" style="text-decoration:none;">
        <div class="gc kpi" style="cursor:pointer;">
            <div class="orb {{ $aiOverdue > 0 ? 'ob-red' : 'ob-green' }}"></div>
            <div class="ib {{ $aiOverdue > 0 ? 'ib-red' : 'ib-green' }}">&#128204;</div>
            <p class="kt">Action Items</p>
            <p class="kv" style="{{ $aiOverdue > 0 ? 'color:#f87171' : 'color:#34d399' }}">
                {{ $aiOverdue }}
            </p>
            <p class="ks">{{ $aiOverdue === 1 ? 'item' : 'items' }} overdue</p>
            @if($aiOverdue > 0)
            <span class="pill p-red">&#9888; Needs Attention</span>
            @else
            <span class="pill p-green">&#10003; All Clear</span>
            @endif
        </div>
        </a>

        {{-- Stripping Ratio --}}
        @php
            [$srGv, $srOb, $srIb, $srPillCls, $srLabel] = $strippingRatio <= 0.5
                ? ['gv-green',  'ob-green',  'ib-green',  'p-green', 'Excellent']
                : ($strippingRatio <= 1.0
                    ? ['gv-gold', 'ob-gold', 'ib-gold', 'p-amber', 'Acceptable']
                    : ['',         'ob-red',  'ib-red',  'p-red',   'High']);
        @endphp
        <div class="gc kpi">
            <div class="orb {{ $srOb }}"></div>
            <div class="ib {{ $srIb }}">🏗️</div>
            <p class="kt">Stripping Ratio</p>
            <p class="kv {{ $srGv }}" @if(!$srGv) style="color:#f87171" @endif>{{ number_format($strippingRatio, 2) }}</p>
            <p class="ks">waste : ore (t/t)</p>
            <span class="pill {{ $srPillCls }}">{{ $srLabel }}</span>
        </div>

    </div>

    {{-- ROW 2 — PROJECTIONS | FLEET --}}
    <div class="two-col">

        {{-- Projections --}}
        @php $onTrack = $goldProjected >= $goldTarget; @endphp
        <div class="gc pcrd">
            <p class="slbl">Month-End Projections</p>
            <p class="kt" style="margin-bottom:6px;">Projected Gold Output</p>
            <p class="pval {{ $onTrack ? 'gv-green' : 'gv-orange' }}">
                {{ number_format($goldProjected, 1) }}<span style="font-size:1rem;font-weight:500;-webkit-text-fill-color:#9ca3af"> g</span>
            </p>
            <p class="psub">Target: <b style="color:var(--text)">{{ number_format($goldTarget, 0) }} g</b></p>
            @if($onTrack)
                <span class="pill p-green" style="margin-top:8px">✓ On Track</span>
            @else
                <span class="pill p-red" style="margin-top:8px">↓ {{ number_format($goldTarget - $goldProjected, 1) }} g short</span>
            @endif
            <div class="mgrid">
                <div><p class="mgl">Avg Daily Gold</p><p class="mgv">{{ number_format($avgDailyGold, 1) }} <span style="font-size:.68rem;color:#9ca3af">g</span></p></div>
                <div><p class="mgl">Days Recorded</p><p class="mgv">{{ $daysRecorded }}<span style="font-size:.68rem;color:#9ca3af"> / {{ $daysInMonth }}</span></p></div>
                <div><p class="mgl">Days Remaining</p><p class="mgv">{{ $daysInMonth - $dayOfMonth }}</p></div>
            </div>
        </div>

        {{-- Fleet Status --}}
        @php
            $mOk   = max(0, $machinesTotal - $machinesOverdue - $machinesDueSoon);
            $okP   = $machinesTotal > 0 ? round($mOk            / $machinesTotal * 100) : 0;
            $snP   = $machinesTotal > 0 ? round($machinesDueSoon / $machinesTotal * 100) : 0;
            $ovP   = $machinesTotal > 0 ? round($machinesOverdue  / $machinesTotal * 100) : 0;
        @endphp
        <div class="gc pcrd">
            <p class="slbl" style="--g-gold:var(--g-sky)">Fleet Status</p>
            <div class="fseg">
                @if($machinesTotal > 0)
                    @if($mOk > 0)            <span style="flex:{{ $mOk }};background:#34d399;"></span> @endif
                    @if($machinesDueSoon > 0)<span style="flex:{{ $machinesDueSoon }};background:#fbbf24;"></span> @endif
                    @if($machinesOverdue > 0)<span style="flex:{{ $machinesOverdue }};background:#f87171;"></span> @endif
                @else
                    <span style="flex:1;background:var(--topbar-border)"></span>
                @endif
            </div>
            <div class="frow"><div class="fdot" style="background:#6b7280"></div><span class="flbl">Total Machines</span><span class="fnum" style="color:var(--text)">{{ $machinesTotal }}</span></div>
            <div class="frow"><div class="fdot" style="background:#34d399"></div><span class="flbl">Operational</span><span class="fnum" style="color:#34d399">{{ $mOk }}</span><span class="fpct">{{ $okP }}%</span></div>
            <div class="frow"><div class="fdot" style="background:#fbbf24"></div><span class="flbl">Service Due Soon</span><span class="fnum" style="color:#fbbf24">{{ $machinesDueSoon }}</span><span class="fpct">{{ $snP }}%</span></div>
            <div class="frow"><div class="fdot" style="background:#f87171"></div><span class="flbl">Overdue Service</span><span class="fnum" style="color:#f87171">{{ $machinesOverdue }}</span><span class="fpct">{{ $ovP }}%</span></div>
            @if($machinesOverdue > 0)
                <a href="{{ route('machines.index') }}" class="fbtn fb-r">⚠️ {{ $machinesOverdue }} machine(s) need service now</a>
            @elseif($machinesDueSoon > 0)
                <a href="{{ route('machines.index') }}" class="fbtn fb-a">🔧 {{ $machinesDueSoon }} machine(s) due for service</a>
            @else
                <div class="fbtn fb-g">✓ All machines up to date</div>
            @endif
        </div>

    </div>

    {{-- ROW 3 — WEATHER | SHIFT COMPARISON --}}
    <div class="two-col">

        {{-- Weather --}}
        <div class="wcrd" id="weatherCard">
            <p class="wey">⛅ Weather — <span id="weatherLocName">Loading…</span>
                <button id="myLocBtn" title="Show my current location"
                    style="margin-left:8px;background:none;border:1px solid rgba(255,255,255,.25);border-radius:20px;padding:1px 8px;font-size:.65rem;color:rgba(255,255,255,.6);cursor:pointer;vertical-align:middle;">📍 My location</button>
            </p>
            <div id="weatherBody" class="wldg">Locating &amp; loading weather…</div>
        </div>

        {{-- Shift Comparison Chart --}}
        <div class="gc" style="padding:20px 22px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
                <p class="cht-ttl" style="margin-bottom:0;">Shift Performance Comparison</p>
                <div id="shiftMetricBtns" style="display:flex;gap:5px;flex-wrap:wrap;">
                    <button class="smet-btn active" data-metric="gold"   style="padding:4px 11px;font-size:.7rem;font-weight:700;border-radius:20px;border:1px solid #fcb913;background:rgba(252,185,19,.15);color:#fcb913;cursor:pointer;">Gold (g)</button>
                    <button class="smet-btn"        data-metric="hoisted" style="padding:4px 11px;font-size:.7rem;font-weight:700;border-radius:20px;border:1px solid var(--topbar-border);background:transparent;color:#9ca3af;cursor:pointer;">Ore Hoisted</button>
                    <button class="smet-btn"        data-metric="milled"  style="padding:4px 11px;font-size:.7rem;font-weight:700;border-radius:20px;border:1px solid var(--topbar-border);background:transparent;color:#9ca3af;cursor:pointer;">Ore Milled</button>
                    <button class="smet-btn"        data-metric="purity"  style="padding:4px 11px;font-size:.7rem;font-weight:700;border-radius:20px;border:1px solid var(--topbar-border);background:transparent;color:#9ca3af;cursor:pointer;">Purity %</button>
                </div>
            </div>

            @if(count($shiftLabels) > 0)
            <canvas id="shiftChart" height="120"></canvas>

            {{-- Per-shift summary cards — updated by JS on metric toggle --}}
            <div id="shiftSummaryCards" style="margin-top:14px;display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;">
                @foreach($shiftLabels as $i => $sLabel)
                @php
                    $colors = ['#fcb913','#38bdf8','#a78bfa','#34d399','#f87171'];
                    $col    = $colors[$i % count($colors)];
                @endphp
                <div style="background:var(--input-bg);border-radius:10px;padding:10px 12px;border-left:3px solid {{ $col }};">
                    <div style="font-size:.65rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:{{ $col }};margin-bottom:4px;">{{ $sLabel }}</div>
                    <div class="shift-card-val" data-idx="{{ $i }}" style="font-size:1rem;font-weight:800;color:var(--text);">{{ number_format($shiftGold[$i], 1) }} <span class="shift-card-unit" style="font-size:.6rem;font-weight:500;color:#9ca3af;">g</span></div>
                    <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">{{ $shiftCounts[$i] }} record{{ $shiftCounts[$i] != 1 ? 's' : '' }}</div>
                </div>
                @endforeach
            </div>

            @else
            <div style="text-align:center;padding:40px 0;color:#9ca3af;font-size:.85rem;">
                No shift data for this period.
            </div>
            @endif
        </div>

    </div>

    {{-- ROW 4 — QUICK ADD --}}
    <div>
        <p class="mgl" style="margin-bottom:10px;">Quick Add</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('production.create') }}" class="qabtn qa-gold">⛏️ Production</a>
            <a href="{{ route('drilling.create') }}"   class="qabtn qa-sky">🔩 Drilling</a>
            <a href="{{ route('blasting.create') }}"   class="qabtn qa-red">💥 Blasting</a>
            <a href="{{ route('chemicals.create') }}"  class="qabtn qa-purple">🧪 Chemicals</a>
            <a href="{{ route('action-items.create') }}" class="qabtn qa-red">📌 Action Item</a>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = () => document.documentElement.classList.contains('dark');

    function cc() {
        return {
            grid:    isDark() ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)',
            text:    isDark() ? '#94a3b8'               : '#6b7280',
            ttBg:    isDark() ? '#1e2535'               : '#fff',
            ttTitle: isDark() ? '#f1f5f9'               : '#0f172a',
            ttBody:  isDark() ? '#94a3b8'               : '#6b7280',
            ttBord:  isDark() ? '#334155'               : '#e2e8f0',
        };
    }

    /* ── 30-day 5-line trend ── */
    function buildChart() {
        const c = cc();
        return new Chart(document.getElementById('trendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [
                    { label:'Ore Hoisted (t)',   data:@json($trendOreHoisted),   yAxisID:'yT', borderColor:'#fbbf24', backgroundColor:'transparent', borderWidth:2, pointRadius:0, pointHoverRadius:4, tension:.4 },
                    { label:'Waste Hoisted (t)', data:@json($trendWasteHoisted), yAxisID:'yT', borderColor:'#f87171', backgroundColor:'transparent', borderWidth:2, borderDash:[5,4], pointRadius:0, pointHoverRadius:4, tension:.4 },
                    { label:'Ore Crushed (t)',   data:@json($trendOreCrushed),   yAxisID:'yT', borderColor:'#38bdf8', backgroundColor:'transparent', borderWidth:2, pointRadius:0, pointHoverRadius:4, tension:.4 },
                    { label:'Ore Milled (t)',    data:@json($trendOreMilled),    yAxisID:'yT', borderColor:'#a78bfa', backgroundColor:'transparent', borderWidth:2, pointRadius:0, pointHoverRadius:4, tension:.4 },
                    { label:'Gold Smelted (g)', data:@json($trendGoldSmelted),  yAxisID:'yG', borderColor:'#34d399', backgroundColor:'rgba(52,211,153,.07)', fill:true, borderWidth:2.5, pointRadius:0, pointHoverRadius:5, tension:.4 },
                ]
            },
            options: {
                responsive: true,
                interaction: { mode:'index', intersect:false },
                plugins: {
                    legend: { position:'top', labels:{ color:c.text, boxWidth:12, boxHeight:2, padding:16, font:{size:11} } },
                    tooltip: {
                        backgroundColor:c.ttBg, titleColor:c.ttTitle, bodyColor:c.ttBody,
                        borderColor:c.ttBord, borderWidth:1, padding:12, cornerRadius:12,
                        callbacks: { label: x => '  '+x.dataset.label+': '+x.parsed.y.toFixed(x.dataset.yAxisID==='yG'?1:1)+(x.dataset.yAxisID==='yG'?' g':' t') }
                    }
                },
                scales: {
                    x:  { ticks:{color:c.text,font:{size:10},maxTicksLimit:10}, grid:{color:c.grid}, border:{display:false} },
                    yT: { type:'linear', position:'left', beginAtZero:true,
                          title:{display:true,text:'Tonnes',color:c.text,font:{size:10}},
                          ticks:{color:c.text,font:{size:10}}, grid:{color:c.grid}, border:{display:false} },
                    yG: { type:'linear', position:'right', beginAtZero:true,
                          title:{display:true,text:'kg Gold',color:'#34d399',font:{size:10}},
                          ticks:{color:'#34d399',font:{size:10},callback:v=>v.toFixed(2)},
                          grid:{drawOnChartArea:false}, border:{display:false} }
                }
            }
        });
    }

    let chart = buildChart();
    document.getElementById('darkToggle').addEventListener('click', () => {
        setTimeout(() => { chart.destroy(); chart=buildChart(); }, 55);
    });

    /* ── Shift Comparison Chart ── */
    @if(count($shiftLabels) > 0)
    const shiftLabels  = @json($shiftLabels);
    const shiftData    = {
        gold:    @json($shiftGold),
        hoisted: @json($shiftOreHoisted),
        milled:  @json($shiftOreMilled),
        purity:  @json($shiftPurity),
    };
    const shiftUnits   = { gold:'g', hoisted:'t', milled:'t', purity:'%' };
    const shiftColors  = ['#fcb913','#38bdf8','#a78bfa','#34d399','#f87171'];
    const shiftAlphas  = ['rgba(252,185,19,.75)','rgba(56,189,248,.75)','rgba(167,139,250,.75)','rgba(52,211,153,.75)','rgba(248,113,113,.75)'];

    let shiftMetric = 'gold';
    let shiftChart  = null;

    function buildShiftChart() {
        const c = cc();
        if (shiftChart) shiftChart.destroy();
        shiftChart = new Chart(document.getElementById('shiftChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: shiftLabels,
                datasets: shiftLabels.map((lbl, i) => ({
                    label: lbl,
                    data: [shiftData[shiftMetric][i]],
                    backgroundColor: shiftAlphas[i % shiftAlphas.length],
                    borderColor:     shiftColors[i % shiftColors.length],
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }))
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: shiftLabels.length > 1, position:'top', labels:{ color:c.text, boxWidth:12, boxHeight:2, padding:14, font:{size:11} } },
                    tooltip: {
                        backgroundColor:c.ttBg, titleColor:c.ttTitle, bodyColor:c.ttBody,
                        borderColor:c.ttBord, borderWidth:1, padding:10, cornerRadius:10,
                        callbacks: { label: x => '  ' + x.dataset.label + ': ' + x.parsed.x.toFixed(shiftMetric==='gold'||shiftMetric==='purity'?2:1) + ' ' + shiftUnits[shiftMetric] }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks:{ color:c.text, font:{size:10} },
                        grid:{ color:c.grid }, border:{display:false},
                        title:{ display:true, text: shiftMetric==='gold'?'Gold (g)': shiftMetric==='purity'?'Avg Purity (%)':'Tonnes', color:c.text, font:{size:10} }
                    },
                    y: { ticks:{ color:c.text, font:{size:11,weight:'bold'} }, grid:{display:false}, border:{display:false} }
                }
            }
        });
    }

    buildShiftChart();

    document.querySelectorAll('.smet-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            shiftMetric = this.dataset.metric;
            document.querySelectorAll('.smet-btn').forEach(b => {
                b.style.background   = 'transparent';
                b.style.borderColor  = 'var(--topbar-border)';
                b.style.color        = '#9ca3af';
                b.classList.remove('active');
            });
            this.style.background  = 'rgba(252,185,19,.15)';
            this.style.borderColor = '#fcb913';
            this.style.color       = '#fcb913';
            this.classList.add('active');
            buildShiftChart();
            // Update summary cards
            const decimals = (shiftMetric === 'purity') ? 1 : (shiftMetric === 'gold' ? 1 : 1);
            const unit     = shiftUnits[shiftMetric];
            document.querySelectorAll('.shift-card-val').forEach(el => {
                const idx = parseInt(el.dataset.idx);
                const val = shiftData[shiftMetric][idx];
                el.innerHTML = val.toFixed(decimals) + ' <span class="shift-card-unit" style="font-size:.6rem;font-weight:500;color:#9ca3af;">' + unit + '</span>';
            });
        });
    });

    document.getElementById('darkToggle').addEventListener('click', () => {
        setTimeout(buildShiftChart, 60);
    });
    @endif

    /* ── Weather init ── */
    loadWeather(MINE_LAT, MINE_LON, MINE_NAME);
    document.getElementById('myLocBtn').addEventListener('click', switchToMyLocation);
});

/* ══════════════ WEATHER GLOBALS (outside DOMContentLoaded) ══════════════ */
const WI = {0:'☀️',1:'🌤️',2:'⛅',3:'☁️',45:'🌫️',48:'🌫️',51:'🌦️',53:'🌦️',55:'🌧️',61:'🌧️',63:'🌧️',65:'🌧️',71:'🌨️',73:'🌨️',75:'🌨️',77:'❄️',80:'🌦️',81:'🌧️',82:'⛈️',85:'🌨️',86:'🌨️',95:'⛈️',96:'⛈️',99:'⛈️'};
const WD = {0:'Clear sky',1:'Mainly clear',2:'Partly cloudy',3:'Overcast',45:'Foggy',48:'Icy fog',51:'Light drizzle',53:'Drizzle',55:'Heavy drizzle',61:'Light rain',63:'Moderate rain',65:'Heavy rain',71:'Light snow',73:'Snow',75:'Heavy snow',77:'Snow grains',80:'Rain showers',81:'Heavy showers',82:'Violent showers',85:'Snow showers',86:'Heavy snow showers',95:'Thunderstorm',96:'Thunderstorm + hail',99:'Severe thunderstorm'};
const DAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const wCls = c => [95,96,99].includes(c)?'wc-storm':[45,48].includes(c)?'wc-fog':c>=51?'wc-rain':c>=2?'wc-cloudy':'wc-clear';
const MINE_LAT  = {{ $mineLat }};
const MINE_LON  = {{ $mineLon }};
const MINE_NAME = '{{ addslashes($mineLocation) }}';

function renderWeather(data, loc) {
    const cur=data.current, dly=data.daily, code=cur.weather_code, r0=dly.precipitation_sum[0]??0;
    const el=document.getElementById('weatherCard');
    el.classList.remove('wc-clear','wc-cloudy','wc-rain','wc-storm','wc-fog');
    el.classList.add(wCls(code));
    document.getElementById('weatherLocName').textContent = loc;

    const alerts=[];
    if([95,96,99].includes(code)) alerts.push('⚡ Thunderstorm now — <strong>suspend blasting immediately</strong>');
    else if(dly.weather_code.slice(0,3).some(c=>[95,96,99].includes(c))) alerts.push('⚡ Thunderstorm within 3 days — confirm blasting schedule');
    if(r0>=25) alerts.push(`🌊 Heavy rain (${r0.toFixed(0)} mm) — monitor shaft drainage & haul roads`);
    else if(r0>=10) alerts.push(`🌧️ Moderate rain (${r0.toFixed(0)} mm) — check haul road conditions`);

    let fc='';
    for(let i=0;i<Math.min(5,dly.time.length);i++){
        const d=new Date(dly.time[i]+'T00:00:00');
        const rn=dly.precipitation_sum[i]>0?`<div class="dr">${dly.precipitation_sum[i].toFixed(0)}mm</div>`:'';
        fc+=`<div class="wday"><div class="dn">${i===0?'Today':DAYS[d.getDay()]}</div><div class="di">${WI[dly.weather_code[i]]??'🌡️'}</div><div class="dh">${Math.round(dly.temperature_2m_max[i])}°</div><div class="dl">${Math.round(dly.temperature_2m_min[i])}°</div>${rn}</div>`;
    }
    document.getElementById('weatherBody').innerHTML=`
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
            <div class="wicn">${WI[code]??'🌡️'}</div>
            <div><div class="wtmp">${Math.round(cur.temperature_2m)}°C</div><div class="wdsc">${WD[code]??'Unknown'}</div></div>
        </div>
        <div class="wpls"><span class="wpil">💧 ${cur.relative_humidity_2m}%</span><span class="wpil">💨 ${cur.wind_speed_10m} km/h</span><span class="wpil">🌧️ ${cur.precipitation} mm</span></div>
        <div class="wfcst">${fc}</div>
        ${alerts.map(a=>`<div class="walt"><span>${a}</span></div>`).join('')}`;
}

function loadWeather(lat, lon, loc) {
    const tz=Intl.DateTimeFormat().resolvedOptions().timeZone||'Africa/Harare';
    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code,precipitation&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=${encodeURIComponent(tz)}&forecast_days=5`)
        .then(r=>r.ok?r.json():Promise.reject())
        .then(d=>renderWeather(d,loc))
        .catch(()=>{document.getElementById('weatherBody').innerHTML='<div class="wldg" style="color:rgba(255,255,255,.5)">Weather data unavailable</div>';});
}

function switchToMyLocation() {
    const btn = document.getElementById('myLocBtn');
    if (!navigator.geolocation) { btn.textContent = 'Not supported'; return; }
    btn.textContent = '⏳ Locating…';
    btn.disabled = true;
    navigator.geolocation.getCurrentPosition(
        pos => {
            const {latitude:lat, longitude:lon} = pos.coords;
            fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`, {headers:{'Accept-Language':'en'}})
                .then(r=>r.json())
                .then(g=>{
                    const a=g.address||{};
                    const city=a.city||a.town||a.village||a.municipality||a.county||'Your Location';
                    const cc=(a.country_code||'').toUpperCase();
                    loadWeather(lat, lon, city+(cc?', '+cc:''));
                    btn.textContent = '⛏ Mine site';
                    btn.disabled = false;
                    btn.onclick = () => {
                        loadWeather(MINE_LAT, MINE_LON, MINE_NAME);
                        btn.textContent = '📍 My location';
                        btn.onclick = switchToMyLocation;
                    };
                })
                .catch(()=>{ loadWeather(lat, lon, 'Your Location'); btn.textContent='⛏ Mine site'; btn.disabled=false; });
        },
        () => {
            btn.textContent = '📍 My location';
            btn.disabled = false;
            showToast('warning', 'Could not get your location. Check browser permissions.');
        },
        {timeout:10000, maximumAge:0, enableHighAccuracy:true}
    );
}
</script>
@endpush
@endsection
