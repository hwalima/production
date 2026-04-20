@extends('layouts.app')
@section('title', 'Analytics')
@section('page-title', 'Analytics')

@push('styles')
<style>
/* ── Analytics page layout ─────────────────────────────────────── */
.an-wrap    { display:flex; flex-direction:column; gap:20px; padding:20px; max-width:1400px; }
.an-filter  { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.an-filter input[type=date] { background:var(--card); border:1px solid var(--topbar-border); color:var(--text); border-radius:8px; padding:6px 10px; font-size:.82rem; }
.an-filter .preset-btn { background:transparent; border:1px solid var(--topbar-border); color:#9ca3af; border-radius:7px; padding:5px 11px; font-size:.75rem; cursor:pointer; transition:all .15s; }
.an-filter .preset-btn:hover,.an-filter .preset-btn.active { background:rgba(252,185,19,.15); border-color:#fcb913; color:#fcb913; }
.an-filter .apply-btn { background:var(--g-gold); color:#000; font-weight:700; border:none; border-radius:8px; padding:7px 18px; font-size:.82rem; cursor:pointer; }
.an-filter .export-btn { background:transparent; border:1px solid var(--topbar-border); color:#9ca3af; border-radius:8px; padding:6px 13px; font-size:.78rem; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all .15s; }
.an-filter .export-btn:hover { border-color:#34d399; color:#34d399; }
.an-filter .print-btn { background:transparent; border:1px solid var(--topbar-border); color:#9ca3af; border-radius:8px; padding:6px 13px; font-size:.78rem; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all .15s; }
.an-filter .print-btn:hover { border-color:#38bdf8; color:#38bdf8; }

/* ── Section headings ─────────────────────────────────────────── */
.an-sec-hd  { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.an-sec-icon{ width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.an-sec-hd h3 { font-size:.95rem; font-weight:700; color:var(--text); margin:0; }
.an-sec-hd p  { font-size:.72rem; color:#6b7280; margin:0; }

/* ── KPI strip ────────────────────────────────────────────────── */
.an-kpi-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:16px; }
.an-kpi     { background:var(--card); border:1px solid var(--topbar-border); border-radius:12px; padding:14px 16px; position:relative; overflow:hidden; }
.an-kpi-val { font-size:1.6rem; font-weight:800; background:var(--g-gold); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; line-height:1.1; }
.an-kpi-val.gv-green  { background:var(--g-green); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
.an-kpi-val.gv-red    { background:none; -webkit-text-fill-color:#f87171; }
.an-kpi-val.gv-sky    { background:var(--g-sky); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
.an-kpi-val.gv-purple { background:var(--g-purple); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
.an-kpi-lbl { font-size:.7rem; color:#6b7280; margin-top:3px; }
.an-kpi-sub { font-size:.68rem; color:#9ca3af; margin-top:5px; }
.an-delta   { display:inline-flex; align-items:center; gap:3px; font-size:.7rem; font-weight:700; padding:2px 7px; border-radius:99px; margin-top:6px; }
.an-delta.up   { background:rgba(52,211,153,.15); color:#34d399; }
.an-delta.down { background:rgba(248,113,113,.15); color:#f87171; }
.an-delta.flat { background:rgba(156,163,175,.12); color:#9ca3af; }

/* ── Cards ────────────────────────────────────────────────────── */
.an-card    { background:var(--card); border:1px solid var(--topbar-border); border-radius:14px; padding:18px 20px; }
.an-2col    { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.an-3col    { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
@media(max-width:860px){ .an-2col,.an-3col { grid-template-columns:1fr; } }

/* ── Maintenance health bars ──────────────────────────────────── */
.mach-row   { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid var(--topbar-border); }
.mach-row:last-child { border-bottom:none; }
.mach-info  { flex:1; min-width:0; }
.mach-code  { font-size:.8rem; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mach-desc  { font-size:.68rem; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mach-bar-wrap { width:110px; flex-shrink:0; }
.mach-bar   { height:6px; border-radius:3px; background:rgba(255,255,255,.08); overflow:hidden; }
.mach-fill  { height:100%; border-radius:3px; transition:width .4s ease; }
.mach-score { font-size:.72rem; font-weight:700; width:36px; text-align:right; flex-shrink:0; }
.mach-badge { font-size:.64rem; font-weight:700; padding:2px 7px; border-radius:99px; flex-shrink:0; }
.mb-ok      { background:rgba(52,211,153,.15); color:#34d399; }
.mb-soon    { background:rgba(252,185,19,.15); color:#fcb913; }
.mb-over    { background:rgba(248,113,113,.15); color:#f87171; }
.mb-unk     { background:rgba(156,163,175,.12); color:#9ca3af; }

/* ── Anomaly table ────────────────────────────────────────────── */
.an-tbl     { width:100%; border-collapse:collapse; font-size:.8rem; }
.an-tbl th  { text-align:left; padding:8px 10px; color:#6b7280; font-weight:600; border-bottom:1px solid var(--topbar-border); font-size:.72rem; }
.an-tbl td  { padding:8px 10px; border-bottom:1px solid rgba(255,255,255,.04); color:var(--text); }
.an-tbl tr:last-child td { border-bottom:none; }
.an-tbl .z-high { color:#f87171; font-weight:700; }
.an-tbl .z-low  { color:#34d399; font-weight:700; }

/* ── Consumable category bar ──────────────────────────────────── */
.cat-row    { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.cat-label  { width:80px; font-size:.72rem; color:#9ca3af; text-align:right; flex-shrink:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cat-bar-bg { flex:1; height:8px; border-radius:4px; background:rgba(255,255,255,.07); overflow:hidden; }
.cat-bar-fill { height:100%; border-radius:4px; }
.cat-val    { width:80px; font-size:.72rem; font-weight:700; color:var(--text); flex-shrink:0; text-align:right; }

/* ── SHE badges ──────────────────────────────────────────────── */
.she-grid   { display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:10px; }
.she-kpi    { background:rgba(255,255,255,.04); border-radius:10px; padding:12px 14px; text-align:center; }
.she-val    { font-size:1.5rem; font-weight:800; line-height:1; }
.she-lbl    { font-size:.65rem; color:#6b7280; margin-top:4px; }

/* ── Print ────────────────────────────────────────────────────── */
@media print {
    .sidebar, .topbar, .an-filter, #pwaInstallBtn { display:none !important; }
    .main-content { margin:0 !important; padding:0 !important; }
    .an-wrap { padding:10px; }
    .an-card, .an-kpi { break-inside:avoid; }
}
</style>
@endpush

@section('content')
<div class="an-wrap">

    {{-- ═══════════════ FILTER BAR ═══════════════ --}}
    <div class="an-card" style="padding:14px 18px;">
        <form method="GET" action="{{ route('analytics.index') }}" id="anFilterForm" style="display:contents;">
            <div class="an-filter">
                <span style="font-size:.78rem;font-weight:700;color:#9ca3af;white-space:nowrap;">&#128197; Date Range:</span>
                <input type="date" name="from" id="anFrom" value="{{ $from }}" max="{{ $to }}">
                <span style="color:#6b7280;font-size:.8rem;">to</span>
                <input type="date" name="to" id="anTo" value="{{ $to }}" min="{{ $from }}">

                <button type="button" class="preset-btn" data-months="1">1M</button>
                <button type="button" class="preset-btn" data-months="3">3M</button>
                <button type="button" class="preset-btn" data-months="6">6M</button>
                <button type="button" class="preset-btn" data-ytd="1">YTD</button>
                <button type="button" class="preset-btn" data-months="12">12M</button>

                <button type="submit" class="apply-btn">Apply</button>

                <div style="margin-left:auto;display:flex;gap:8px;">
                    <a href="{{ route('analytics.export', ['from' => $from, 'to' => $to]) }}" class="export-btn">
                        &#8659; Export CSV
                    </a>
                    <button type="button" class="print-btn" onclick="window.print()">&#128424; Print</button>
                </div>
            </div>
        </form>
        <div style="margin-top:10px; display:flex; gap:20px; flex-wrap:wrap;">
            <span style="font-size:.72rem;color:#6b7280;">Period: <b style="color:var(--text);">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</b> ({{ $daysRange }} days)</span>
            <span style="font-size:.72rem;color:#6b7280;">Gold: <b style="color:#fcb913;">{{ number_format($totalGoldSmelted, 2) }} g</b></span>
            <span style="font-size:.72rem;color:#6b7280;">Ore Milled: <b style="color:#38bdf8;">{{ number_format($totalOreMilled, 0) }} t</b></span>
            <span style="font-size:.72rem;color:#6b7280;">Total Costs: <b style="color:#a78bfa;">{{ $totalAllCosts > 0 ? '$'.number_format($totalAllCosts, 0) : 'N/A' }}</b></span>
        </div>
    </div>

    {{-- ═══ 1. MILL RECOVERY % ════════════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(52,211,153,.12);">♻️</div>
            <div>
                <h3>1. Mill Recovery %</h3>
                <p>Gold recovered as % of theoretical gold in feed ore (fire assay × ore milled)</p>
            </div>
        </div>
        <div class="an-kpi-row">
            <div class="an-kpi">
                <div class="an-kpi-val {{ $avgMillRecovery === null ? '' : ($avgMillRecovery >= 85 ? 'gv-green' : ($avgMillRecovery >= 75 ? '' : 'gv-red')) }}">
                    {{ $avgMillRecovery !== null ? $avgMillRecovery.'%' : 'N/A' }}
                </div>
                <div class="an-kpi-lbl">Avg Recovery</div>
                <div class="an-kpi-sub">Target: ≥85%</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">{{ number_format($totalGoldSmelted, 1) }} g</div>
                <div class="an-kpi-lbl">Gold Smelted</div>
                <div class="an-kpi-sub">Period total</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val">{{ number_format($totalOreMilled, 0) }} t</div>
                <div class="an-kpi-lbl">Ore Milled</div>
            </div>
            <div class="an-kpi">
                @php $recDays = count(array_filter($recoveryTrendData)); @endphp
                <div class="an-kpi-val gv-purple">{{ $recDays }}</div>
                <div class="an-kpi-lbl">Days with assay</div>
                <div class="an-kpi-sub">of {{ count($recoveryTrendData) }} total</div>
            </div>
        </div>
        @if($hasAssayData)
        <canvas id="an_recoveryChart" height="60"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">No fire assay data recorded for this period. Add Fire Assay results to compute Mill Recovery %.</div>
        @endif
    </div>

    {{-- ═══ 2. AISC PER GRAM ══════════════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(252,185,19,.12);">💰</div>
            <div>
                <h3>2. All-In Sustaining Cost (AISC) per Gram</h3>
                <p>Total operating costs ÷ gold smelted. Lower is better.</p>
            </div>
        </div>
        <div class="an-kpi-row">
            <div class="an-kpi">
                <div class="an-kpi-val">{{ $avgAisc !== null ? '$'.number_format($avgAisc, 2) : 'N/A' }}</div>
                <div class="an-kpi-lbl">AISC per gram</div>
                <div class="an-kpi-sub">USD/g period avg</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-red">{{ $totalAllCosts > 0 ? '$'.number_format($totalAllCosts, 0) : 'N/A' }}</div>
                <div class="an-kpi-lbl">Total Costs</div>
                <div class="an-kpi-sub">Labour + Energy + Stores</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">{{ number_format($totalGoldSmelted, 1) }} g</div>
                <div class="an-kpi-lbl">Gold Smelted</div>
            </div>
        </div>
        @if(count($aiscLabels) > 0 && count(array_filter($aiscData)) > 0)
        <canvas id="an_aiscChart" height="60"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">Cost data not available for this period. Ensure Labour & Energy and Consumables records are entered.</div>
        @endif
    </div>

    {{-- ═══ 3. GRADE RECONCILIATION ═══════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(167,139,250,.12);">🔬</div>
            <div>
                <h3>3. Grade Reconciliation</h3>
                <p>Fire Assay grade (sampled) vs Implied Head Grade (gold smelted ÷ ore milled)</p>
            </div>
        </div>
        <div class="an-kpi-row">
            @php
                $avgFire    = count(array_filter($gradeRecFire)) > 0 ? round(array_sum(array_filter($gradeRecFire)) / count(array_filter($gradeRecFire)), 4) : null;
                $avgImplied = count(array_filter($gradeRecImplied)) > 0 ? round(array_sum(array_filter($gradeRecImplied)) / count(array_filter($gradeRecImplied)), 4) : null;
                $reconcDiff = ($avgFire && $avgImplied) ? round($avgImplied - $avgFire, 4) : null;
            @endphp
            <div class="an-kpi">
                <div class="an-kpi-val gv-purple">{{ $avgFire !== null ? $avgFire.' g/t' : 'N/A' }}</div>
                <div class="an-kpi-lbl">Avg Fire Assay</div>
                <div class="an-kpi-sub">Sampled head grade</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val">{{ $avgImplied !== null ? $avgImplied.' g/t' : 'N/A' }}</div>
                <div class="an-kpi-lbl">Avg Implied Grade</div>
                <div class="an-kpi-sub">Gold ÷ Ore Milled</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val {{ $reconcDiff === null ? '' : ($reconcDiff >= 0 ? 'gv-green' : 'gv-red') }}">
                    {{ $reconcDiff !== null ? ($reconcDiff >= 0 ? '+' : '').$reconcDiff.' g/t' : 'N/A' }}
                </div>
                <div class="an-kpi-lbl">Reconciliation Gap</div>
                <div class="an-kpi-sub">Implied − Assay</div>
            </div>
        </div>
        @if(count($gradeRecLabels) > 0 && (count(array_filter($gradeRecFire)) > 0 || count(array_filter($gradeRecImplied)) > 0))
        <canvas id="an_gradeRecChart" height="65"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">No grade data available for this period.</div>
        @endif
    </div>

    {{-- ═══ 4. COST PER TONNE MILLED ══════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(251,191,36,.12);">⚙️</div>
            <div>
                <h3>4. Cost per Tonne Milled</h3>
                <p>Total operating costs ÷ ore milled. Key milling efficiency metric.</p>
            </div>
        </div>
        <div class="an-kpi-row">
            <div class="an-kpi">
                <div class="an-kpi-val">{{ $avgCostPerTonne !== null ? '$'.number_format($avgCostPerTonne, 2) : 'N/A' }}</div>
                <div class="an-kpi-lbl">Cost per tonne milled</div>
                <div class="an-kpi-sub">USD/t period avg</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">{{ number_format($totalOreMilled, 0) }} t</div>
                <div class="an-kpi-lbl">Total Ore Milled</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-red">{{ $totalAllCosts > 0 ? '$'.number_format($totalAllCosts, 0) : 'N/A' }}</div>
                <div class="an-kpi-lbl">Total Costs</div>
            </div>
        </div>
        @if(count($cptLabels) > 0 && count(array_filter($cptData)) > 0)
        <canvas id="an_cptChart" height="55"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">Insufficient data for cost per tonne calculation.</div>
        @endif
    </div>

    {{-- ═══ 5. MoM / YTD COMPARISON ══════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(56,189,248,.12);">📊</div>
            <div>
                <h3>5. Month-over-Month & Year-to-Date Comparison</h3>
                <p>Selected period vs prior period · YTD totals to {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p>
            </div>
        </div>
        <div class="an-2col">
            <div>
                <p style="font-size:.72rem;font-weight:700;color:#9ca3af;margin-bottom:12px;text-transform:uppercase;letter-spacing:.05em;">Period vs Prior Period ({{ \Carbon\Carbon::parse($prevFrom)->format('M Y') }})</p>
                <div class="an-kpi-row" style="grid-template-columns:1fr 1fr 1fr;">
                    <div class="an-kpi">
                        <div class="an-kpi-val gv-sky">{{ number_format($totalGoldSmelted, 1) }} g</div>
                        <div class="an-kpi-lbl">Gold (current)</div>
                        @if($momGoldDelta !== null)
                        <span class="an-delta {{ $momGoldDelta >= 0 ? 'up' : 'down' }}">{{ $momGoldDelta >= 0 ? '▲' : '▼' }} {{ abs($momGoldDelta) }}%</span>
                        @endif
                    </div>
                    <div class="an-kpi">
                        <div class="an-kpi-val">{{ number_format($totalOreMilled, 0) }} t</div>
                        <div class="an-kpi-lbl">Milled (current)</div>
                        @if($momMilledDelta !== null)
                        <span class="an-delta {{ $momMilledDelta >= 0 ? 'up' : 'down' }}">{{ $momMilledDelta >= 0 ? '▲' : '▼' }} {{ abs($momMilledDelta) }}%</span>
                        @endif
                    </div>
                    <div class="an-kpi">
                        <div class="an-kpi-val gv-red">{{ $totalAllCosts > 0 ? '$'.number_format($totalAllCosts, 0) : 'N/A' }}</div>
                        <div class="an-kpi-lbl">Costs (current)</div>
                        @if($momCostDelta !== null)
                        <span class="an-delta {{ $momCostDelta <= 0 ? 'up' : 'down' }}">{{ $momCostDelta >= 0 ? '▲' : '▼' }} {{ abs($momCostDelta) }}%</span>
                        @endif
                    </div>
                </div>
                <p style="font-size:.68rem;color:#6b7280;margin-top:6px;">Prior period ({{ \Carbon\Carbon::parse($prevFrom)->format('d M') }}–{{ \Carbon\Carbon::parse($prevTo)->format('d M Y') }}): Gold {{ number_format($prevGold,1) }} g, Milled {{ number_format($prevMilled,0) }} t, Costs {{ $prevCosts > 0 ? '$'.number_format($prevCosts,0) : 'N/A' }}</p>
            </div>
            <div>
                <p style="font-size:.72rem;font-weight:700;color:#9ca3af;margin-bottom:12px;text-transform:uppercase;letter-spacing:.05em;">Year-to-Date ({{ \Carbon\Carbon::parse($from)->format('Y') }})</p>
                <div class="an-kpi-row" style="grid-template-columns:1fr 1fr;">
                    <div class="an-kpi">
                        <div class="an-kpi-val gv-green">{{ number_format($ytdGold, 1) }} g</div>
                        <div class="an-kpi-lbl">YTD Gold Smelted</div>
                    </div>
                    <div class="an-kpi">
                        <div class="an-kpi-val gv-sky">{{ number_format($ytdMilled, 0) }} t</div>
                        <div class="an-kpi-lbl">YTD Ore Milled</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ 6. STOCKPILE BALANCE TREND ════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(251,191,36,.12);">🏔️</div>
            <div>
                <h3>6. Stockpile Balance Trend</h3>
                <p>Daily uncrushed and unmilled stockpile inventory in tonnes</p>
            </div>
        </div>
        <div class="an-kpi-row">
            <div class="an-kpi">
                <div class="an-kpi-val">{{ number_format($latestUncrushed, 0) }} t</div>
                <div class="an-kpi-lbl">Latest Uncrushed</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">{{ number_format($latestUnmilled, 0) }} t</div>
                <div class="an-kpi-lbl">Latest Unmilled</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-purple">{{ number_format($latestUncrushed + $latestUnmilled, 0) }} t</div>
                <div class="an-kpi-lbl">Total Buffer Stock</div>
            </div>
        </div>
        @if(count($stockLabels) > 0)
        <canvas id="an_stockChart" height="60"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">No stockpile data for this period.</div>
        @endif
    </div>

    {{-- ═══ 7. BLASTING / POWDER FACTOR ═══════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(248,113,113,.12);">💥</div>
            <div>
                <h3>7. Blasting Consumables Trend</h3>
                <p>ANFO and oil consumption per blast event — proxy for powder factor</p>
            </div>
        </div>
        <div class="an-kpi-row">
            <div class="an-kpi">
                <div class="an-kpi-val gv-red">{{ number_format($totalAnfo, 1) }} kg</div>
                <div class="an-kpi-lbl">Total ANFO Used</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val">{{ number_format($totalOil, 1) }} L</div>
                <div class="an-kpi-lbl">Total Oil Used</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">{{ $avgAnfoPerDay }} kg</div>
                <div class="an-kpi-lbl">Avg ANFO/blast day</div>
            </div>
        </div>
        @if(count($blastLabels) > 0)
        <canvas id="an_blastChart" height="55"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">No blasting records for this period.</div>
        @endif
    </div>

    {{-- ═══ 8. SHE SAFETY RATES ═══════════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(248,113,113,.12);">⚠️</div>
            <div>
                <h3>8. Safety Health Environment (SHE) Rates</h3>
                <p>Incident counts for selected period. LTIFR requires person-hours tracking.</p>
            </div>
        </div>
        <div class="she-grid">
            <div class="she-kpi">
                <div class="she-val" style="color:{{ $totalFatal > 0 ? '#f87171' : '#34d399' }};">{{ $totalFatal }}</div>
                <div class="she-lbl">Fatalities</div>
            </div>
            <div class="she-kpi">
                <div class="she-val" style="color:{{ $totalLti > 0 ? '#f87171' : '#34d399' }};">{{ $totalLti }}</div>
                <div class="she-lbl">Lost Time Injuries</div>
            </div>
            <div class="she-kpi">
                <div class="she-val" style="color:{{ $totalNlti > 0 ? '#fcb913' : '#34d399' }};">{{ $totalNlti }}</div>
                <div class="she-lbl">Non-LTI</div>
            </div>
            <div class="she-kpi">
                <div class="she-val" style="color:#9ca3af;">{{ $totalMedical }}</div>
                <div class="she-lbl">Medical Cases</div>
            </div>
            <div class="she-kpi">
                <div class="she-val" style="color:#9ca3af;">{{ $totalSick }}</div>
                <div class="she-lbl">Sick Days</div>
            </div>
            <div class="she-kpi">
                <div class="she-val" style="color:#9ca3af;">{{ $totalLeave }}</div>
                <div class="she-lbl">Leave Days</div>
            </div>
            <div class="she-kpi">
                <div class="she-val" style="color:{{ $totalAwol > 0 ? '#fbbf24' : '#9ca3af' }};">{{ $totalAwol }}</div>
                <div class="she-lbl">AWOL</div>
            </div>
            <div class="she-kpi">
                <div class="she-val" style="color:#9ca3af;">{{ $totalAbsence }}</div>
                <div class="she-lbl">Total Absence Days</div>
            </div>
        </div>
        @if(count($sheMonthLabels) > 0)
        <div style="margin-top:16px;"><canvas id="an_sheChart" height="55"></canvas></div>
        @else
        <div style="padding:16px 0 0;text-align:center;color:#6b7280;font-size:.82rem;">No SHE records for this period.</div>
        @endif
    </div>

    {{-- ═══ 9. CONSUMABLES BURN RATE ═══════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(251,191,36,.12);">📦</div>
            <div>
                <h3>9. Consumables Burn Rate by Category</h3>
                <p>Total consumption cost per category + monthly spend trend</p>
            </div>
        </div>
        <div class="an-2col">
            <div>
                @php $maxBurn = $burnByCategory->max('total_cost') ?: 1; @endphp
                @forelse($burnByCategory as $cat)
                <div class="cat-row">
                    <div class="cat-label">{{ ucfirst($cat->category) }}</div>
                    <div class="cat-bar-bg">
                        <div class="cat-bar-fill" style="width:{{ min(100, ($cat->total_cost / $maxBurn) * 100) }}%;background:{{ \App\Models\Consumable::categoryColor($cat->category) }};"></div>
                    </div>
                    <div class="cat-val">${{ number_format($cat->total_cost, 0) }}</div>
                </div>
                @empty
                <p style="color:#6b7280;font-size:.8rem;">No consumable usage recorded for this period.</p>
                @endforelse
            </div>
            <div>
                @if(count($consumMonthLabels) > 0)
                <canvas id="an_consumChart" height="120"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ 10. DRILL METRES TREND ════════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(56,189,248,.12);">🔩</div>
            <div>
                <h3>10. Drill Metres Trend</h3>
                <p>Daily advance (metres drilled) and hole count</p>
            </div>
        </div>
        <div class="an-kpi-row">
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">{{ number_format($totalAdvance, 1) }} m</div>
                <div class="an-kpi-lbl">Total Advance</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val">{{ $totalHoles }}</div>
                <div class="an-kpi-lbl">Total Holes</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-purple">{{ $avgAdvPerDay }} m</div>
                <div class="an-kpi-lbl">Avg Advance/Day</div>
            </div>
        </div>
        @if(count($drillLabels) > 0)
        <canvas id="an_drillChart" height="55"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">No drilling records for this period.</div>
        @endif
    </div>

    {{-- ═══ 11. SPC CONTROL CHART ══════════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(167,139,250,.12);">📈</div>
            <div>
                <h3>11. Statistical Process Control — Implied Gold Grade</h3>
                <p>Daily implied grade with ±2σ control limits. Points outside limits indicate special cause variation.</p>
            </div>
        </div>
        <div class="an-kpi-row">
            <div class="an-kpi">
                <div class="an-kpi-val">{{ round($spcMean, 4) }} g/t</div>
                <div class="an-kpi-lbl">Mean Grade (x̄)</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">{{ round($spcStd, 4) }}</div>
                <div class="an-kpi-lbl">Std Dev (σ)</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-green">{{ $spcUcl }} g/t</div>
                <div class="an-kpi-lbl">UCL (+2σ)</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-red">{{ $spcLcl }} g/t</div>
                <div class="an-kpi-lbl">LCL (−2σ)</div>
            </div>
        </div>
        @if(count($spcLabels) > 1)
        <canvas id="an_spcChart" height="70"></canvas>
        @else
        <div style="padding:24px;text-align:center;color:#6b7280;font-size:.82rem;">Not enough production data points for SPC analysis (need ≥2 days with ore milled).</div>
        @endif
    </div>

    {{-- ═══ 12. PREDICTIVE MAINTENANCE ════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(251,191,36,.12);">🔧</div>
            <div>
                <h3>12. Predictive Maintenance Health Score</h3>
                <p>Days to scheduled service relative to service interval. Score = days remaining as % of interval.</p>
            </div>
        </div>
        @php
            $overdue  = count(array_filter($machineScores, fn($m) => $m['status'] === 'overdue'));
            $dueSoon  = count(array_filter($machineScores, fn($m) => $m['status'] === 'due_soon'));
            $ok       = count(array_filter($machineScores, fn($m) => $m['status'] === 'ok'));
        @endphp
        <div class="an-kpi-row" style="margin-bottom:16px;">
            <div class="an-kpi">
                <div class="an-kpi-val">{{ count($machineScores) }}</div>
                <div class="an-kpi-lbl">Total Machines</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-green">{{ $ok }}</div>
                <div class="an-kpi-lbl">Healthy</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val" style="-webkit-text-fill-color:#fcb913;">{{ $dueSoon }}</div>
                <div class="an-kpi-lbl">Due Soon (≤7 days)</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-red">{{ $overdue }}</div>
                <div class="an-kpi-lbl">Overdue</div>
            </div>
        </div>
        @forelse($machineScores as $m)
        <div class="mach-row">
            <div class="mach-info">
                <div class="mach-code">{{ $m['code'] }}</div>
                <div class="mach-desc">{{ $m['description'] }}</div>
            </div>
            <div style="font-size:.68rem;color:#6b7280;width:90px;flex-shrink:0;text-align:center;">
                {{ $m['next_service'] ?? 'No date set' }}
                @if($m['days_to_service'] !== null)
                <br><span style="color:{{ $m['status']==='overdue'?'#f87171':($m['status']==='due_soon'?'#fcb913':'#34d399') }};font-weight:700;">
                    {{ $m['days_to_service'] < 0 ? abs($m['days_to_service']).'d overdue' : $m['days_to_service'].'d left' }}
                </span>
                @endif
            </div>
            <div class="mach-bar-wrap">
                <div class="mach-bar">
                    @if($m['score'] !== null)
                    <div class="mach-fill" style="width:{{ $m['score'] }}%;background:{{ $m['status']==='overdue'?'#ef4444':($m['status']==='due_soon'?'#fcb913':'#34d399') }};"></div>
                    @endif
                </div>
            </div>
            <div class="mach-score" style="color:{{ $m['status']==='overdue'?'#f87171':($m['status']==='due_soon'?'#fcb913':'#34d399') }};">
                {{ $m['score'] !== null ? $m['score'].'%' : '—' }}
            </div>
            <span class="mach-badge {{ $m['status']==='overdue'?'mb-over':($m['status']==='due_soon'?'mb-soon':($m['status']==='ok'?'mb-ok':'mb-unk')) }}">
                {{ $m['status']==='overdue'?'Overdue':($m['status']==='due_soon'?'Due Soon':($m['status']==='ok'?'OK':'Unknown')) }}
            </span>
        </div>
        @empty
        <p style="color:#6b7280;font-size:.8rem;">No machines configured yet. Add machines in the Machines module.</p>
        @endforelse
    </div>

    {{-- ═══ 13. ANOMALY DETECTION ══════════════════════════════════════════ --}}
    <div class="an-card">
        <div class="an-sec-hd">
            <div class="an-sec-icon" style="background:rgba(248,113,113,.12);">🔍</div>
            <div>
                <h3>13. Anomaly Detection</h3>
                <p>Statistical outliers (&gt;2σ from period mean) in gold grade and production. Investigate cause when detected.</p>
            </div>
        </div>
        <div class="an-kpi-row" style="margin-bottom:14px;">
            <div class="an-kpi">
                <div class="an-kpi-val {{ count($anomalies) > 0 ? 'gv-red' : 'gv-green' }}">{{ count($anomalies) }}</div>
                <div class="an-kpi-lbl">Anomalies Detected</div>
                <div class="an-kpi-sub">|z| > 2σ threshold</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val">{{ round($spcMean, 4) }} g/t</div>
                <div class="an-kpi-lbl">Mean Implied Grade</div>
            </div>
            <div class="an-kpi">
                <div class="an-kpi-val gv-sky">±{{ round($spcStd * 2, 4) }} g/t</div>
                <div class="an-kpi-lbl">2σ Band Width</div>
            </div>
        </div>
        @if(count($anomalies) > 0)
        <table class="an-tbl">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Metric</th>
                    <th>Value</th>
                    <th>Z-Score</th>
                    <th>Direction</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anomalies as $a)
                <tr>
                    <td>{{ $a['date'] }}</td>
                    <td>{{ $a['metric'] }}</td>
                    <td>{{ $a['value'] }}</td>
                    <td class="{{ abs($a['z']) > 3 ? 'z-high' : 'z-low' }}">{{ $a['z'] }}</td>
                    <td>
                        <span class="an-delta {{ $a['dir']==='above' ? 'up' : 'down' }}">
                            {{ $a['dir'] === 'above' ? '▲ Above normal' : '▼ Below normal' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:16px 0;text-align:center;">
            <span style="font-size:1.5rem;">✅</span>
            <p style="color:#34d399;font-weight:700;margin-top:8px;font-size:.85rem;">No anomalies detected — all production within normal range</p>
        </div>
        @endif
    </div>

</div>{{-- /an-wrap --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = () => document.documentElement.classList.contains('dark');

    function cc() {
        return {
            grid:    isDark() ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)',
            text:    isDark() ? '#94a3b8' : '#6b7280',
            ttBg:    isDark() ? '#1e2535' : '#fff',
            ttTitle: isDark() ? '#f1f5f9' : '#0f172a',
            ttBody:  isDark() ? '#94a3b8' : '#6b7280',
            ttBord:  isDark() ? '#334155' : '#e2e8f0',
        };
    }

    function ttOpts(c, unit) {
        return {
            backgroundColor:c.ttBg, titleColor:c.ttTitle, bodyColor:c.ttBody,
            borderColor:c.ttBord, borderWidth:1, padding:10, cornerRadius:10,
            callbacks: { label: x => x.parsed.y !== null ? '  ' + x.dataset.label + ': ' + x.parsed.y.toFixed(2) + (unit ? ' '+unit : '') : null }
        };
    }

    function xScale(c) { return { ticks:{color:c.text,font:{size:10},maxTicksLimit:12}, grid:{color:c.grid}, border:{display:false} }; }
    function yScale(c, unit) { return { beginAtZero:false, ticks:{color:c.text,font:{size:10},callback:v=>v+(unit?unit:'')}, grid:{color:c.grid}, border:{display:false} }; }

    const charts = {};
    function mk(id, cfg) {
        const el = document.getElementById(id);
        if (!el) return;
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(el.getContext('2d'), cfg);
    }

    function buildAll() {
        const c = cc();

        // ── 1. Mill Recovery Trend ──────────────────────────────────────
        @if($hasAssayData)
        mk('an_recoveryChart', {
            type: 'line',
            data: {
                labels: @json($recoveryTrendLabels),
                datasets: [
                    { label:'Recovery %', data:@json($recoveryTrendData), borderColor:'#34d399', backgroundColor:'rgba(52,211,153,.08)', fill:true, borderWidth:2.5, pointRadius:3, pointHoverRadius:6, tension:.4, spanGaps:true },
                    { label:'Target (85%)', data:Array(@json($recoveryTrendLabels).length).fill(85), borderColor:'#a78bfa', backgroundColor:'transparent', borderWidth:1.5, borderDash:[6,4], pointRadius:0, tension:0 },
                ]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{position:'top',labels:{color:c.text,boxWidth:12,boxHeight:2,padding:14,font:{size:11}}}, tooltip:ttOpts(c,'%') },
                scales:{ x:xScale(c), y:{...yScale(c,'%'), min:0, max:110} }
            }
        });
        @endif

        // ── 2. AISC per gram ────────────────────────────────────────────
        @if(count($aiscLabels) > 0)
        mk('an_aiscChart', {
            type: 'bar',
            data: {
                labels: @json($aiscLabels),
                datasets: [{ label:'AISC ($/g)', data:@json($aiscData), backgroundColor:'rgba(252,185,19,.5)', borderColor:'#fcb913', borderWidth:2, borderRadius:6, borderSkipped:false }]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{display:false}, tooltip:ttOpts(c,'$/g') },
                scales:{ x:xScale(c), y:{...yScale(c,''), beginAtZero:true, ticks:{...yScale(c,'').ticks, callback:v=>'$'+v}} }
            }
        });
        @endif

        // ── 3. Grade Reconciliation ─────────────────────────────────────
        @if(count($gradeRecLabels) > 0)
        mk('an_gradeRecChart', {
            type: 'line',
            data: {
                labels: @json($gradeRecLabels),
                datasets: [
                    { label:'Fire Assay (g/t)', data:@json($gradeRecFire), borderColor:'#ef4444', backgroundColor:'transparent', borderWidth:2.5, pointRadius:4, pointHoverRadius:7, tension:.3, spanGaps:false },
                    { label:'Implied Grade (g/t)', data:@json($gradeRecImplied), borderColor:'#a78bfa', backgroundColor:'rgba(167,139,250,.08)', fill:true, borderWidth:2, pointRadius:2, tension:.4, spanGaps:true },
                ]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{position:'top',labels:{color:c.text,boxWidth:12,boxHeight:2,padding:14,font:{size:11}}}, tooltip:ttOpts(c,'g/t') },
                scales:{ x:xScale(c), y:{...yScale(c,'g/t'), beginAtZero:true} }
            }
        });
        @endif

        // ── 4. Cost per tonne ───────────────────────────────────────────
        @if(count($cptLabels) > 0)
        mk('an_cptChart', {
            type: 'bar',
            data: {
                labels: @json($cptLabels),
                datasets: [{ label:'Cost/t milled', data:@json($cptData), backgroundColor:'rgba(56,189,248,.45)', borderColor:'#38bdf8', borderWidth:2, borderRadius:6, borderSkipped:false }]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{display:false}, tooltip:ttOpts(c,'$/t') },
                scales:{ x:xScale(c), y:{...yScale(c,''), beginAtZero:true, ticks:{...yScale(c,'').ticks, callback:v=>'$'+v}} }
            }
        });
        @endif

        // ── 6. Stockpile trend ──────────────────────────────────────────
        @if(count($stockLabels) > 0)
        mk('an_stockChart', {
            type: 'line',
            data: {
                labels: @json($stockLabels),
                datasets: [
                    { label:'Uncrushed (t)', data:@json($stockUncrushed), borderColor:'#fbbf24', backgroundColor:'rgba(251,191,36,.07)', fill:true, borderWidth:2, pointRadius:0, pointHoverRadius:4, tension:.4 },
                    { label:'Unmilled (t)',  data:@json($stockUnmilled),  borderColor:'#38bdf8', backgroundColor:'rgba(56,189,248,.07)', fill:true, borderWidth:2, pointRadius:0, pointHoverRadius:4, tension:.4 },
                ]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{position:'top',labels:{color:c.text,boxWidth:12,boxHeight:2,padding:14,font:{size:11}}}, tooltip:ttOpts(c,'t') },
                scales:{ x:xScale(c), y:{...yScale(c,'t'), beginAtZero:true} }
            }
        });
        @endif

        // ── 7. Blasting ANFO / oil ──────────────────────────────────────
        @if(count($blastLabels) > 0)
        mk('an_blastChart', {
            type: 'bar',
            data: {
                labels: @json($blastLabels),
                datasets: [
                    { label:'ANFO (kg)', data:@json($blastAnfo), backgroundColor:'rgba(248,113,113,.55)', borderColor:'#f87171', borderWidth:1.5, borderRadius:4, borderSkipped:false },
                    { label:'Oil (L)',   data:@json($blastOil),  backgroundColor:'rgba(252,185,19,.45)', borderColor:'#fcb913', borderWidth:1.5, borderRadius:4, borderSkipped:false },
                ]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{position:'top',labels:{color:c.text,boxWidth:12,boxHeight:2,padding:14,font:{size:11}}}, tooltip:{backgroundColor:c.ttBg,titleColor:c.ttTitle,bodyColor:c.ttBody,borderColor:c.ttBord,borderWidth:1,padding:10,cornerRadius:10} },
                scales:{ x:xScale(c), y:{...yScale(c,''), beginAtZero:true} }
            }
        });
        @endif

        // ── 8. SHE monthly ─────────────────────────────────────────────
        @if(count($sheMonthLabels) > 0)
        mk('an_sheChart', {
            type: 'bar',
            data: {
                labels: @json($sheMonthLabels),
                datasets: [
                    { label:'LTI',     data:@json($sheMonthLti),     backgroundColor:'rgba(248,113,113,.65)', borderColor:'#f87171', borderWidth:1.5, borderRadius:4, borderSkipped:false },
                    { label:'NLTI',    data:@json($sheMonthNlti),    backgroundColor:'rgba(252,185,19,.55)',  borderColor:'#fcb913', borderWidth:1.5, borderRadius:4, borderSkipped:false },
                    { label:'Medical', data:@json($sheMonthMedical), backgroundColor:'rgba(56,189,248,.45)', borderColor:'#38bdf8', borderWidth:1.5, borderRadius:4, borderSkipped:false },
                ]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{position:'top',labels:{color:c.text,boxWidth:12,boxHeight:2,padding:14,font:{size:11}}}, tooltip:{backgroundColor:c.ttBg,titleColor:c.ttTitle,bodyColor:c.ttBody,borderColor:c.ttBord,borderWidth:1,padding:10,cornerRadius:10} },
                scales:{ x:xScale(c), y:{...yScale(c,''), beginAtZero:true, ticks:{...yScale(c,'').ticks, stepSize:1}} }
            }
        });
        @endif

        // ── 9. Consumables monthly ─────────────────────────────────────
        @if(count($consumMonthLabels) > 0)
        mk('an_consumChart', {
            type: 'line',
            data: {
                labels: @json($consumMonthLabels),
                datasets: [{ label:'Consumables Spend ($)', data:@json($consumMonthData), borderColor:'#fbbf24', backgroundColor:'rgba(251,191,36,.1)', fill:true, borderWidth:2, pointRadius:4, pointHoverRadius:7, tension:.3 }]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{display:false}, tooltip:ttOpts(c,'$') },
                scales:{ x:xScale(c), y:{...yScale(c,''), beginAtZero:true, ticks:{...yScale(c,'').ticks, callback:v=>'$'+v}} }
            }
        });
        @endif

        // ── 10. Drill metres trend ─────────────────────────────────────
        @if(count($drillLabels) > 0)
        mk('an_drillChart', {
            type: 'bar',
            data: {
                labels: @json($drillLabels),
                datasets: [{ label:'Advance (m)', data:@json($drillAdvance), backgroundColor:'rgba(56,189,248,.5)', borderColor:'#38bdf8', borderWidth:1.5, borderRadius:4, borderSkipped:false }]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{display:false}, tooltip:ttOpts(c,'m') },
                scales:{ x:xScale(c), y:{...yScale(c,'m'), beginAtZero:true} }
            }
        });
        @endif

        // ── 11. SPC control chart ──────────────────────────────────────
        @if(count($spcLabels) > 1)
        const spcN   = @json($spcLabels).length;
        const spcMean = {{ $spcMean }};
        const spcUcl  = {{ $spcUcl }};
        const spcLcl  = {{ $spcLcl }};
        mk('an_spcChart', {
            type: 'line',
            data: {
                labels: @json($spcLabels),
                datasets: [
                    { label:'Implied Grade (g/t)', data:@json($spcValues), borderColor:'#a78bfa', backgroundColor:'rgba(167,139,250,.07)', fill:false, borderWidth:2, pointRadius:3, pointHoverRadius:6, tension:.2,
                      pointBackgroundColor: @json($spcValues).map(v => v > spcUcl || v < spcLcl ? '#f87171' : '#a78bfa'),
                      pointBorderColor:     @json($spcValues).map(v => v > spcUcl || v < spcLcl ? '#ef4444' : '#a78bfa'),
                      pointRadius:          @json($spcValues).map(v => v > spcUcl || v < spcLcl ? 6 : 3),
                    },
                    { label:'Mean (x̄)',  data:Array(spcN).fill(spcMean), borderColor:'#34d399', borderWidth:1.5, borderDash:[4,3], pointRadius:0, tension:0 },
                    { label:'UCL (+2σ)', data:Array(spcN).fill(spcUcl),  borderColor:'#f87171', borderWidth:1,   borderDash:[6,4], pointRadius:0, tension:0 },
                    { label:'LCL (−2σ)', data:Array(spcN).fill(spcLcl),  borderColor:'#f87171', borderWidth:1,   borderDash:[6,4], pointRadius:0, tension:0 },
                ]
            },
            options: {
                responsive:true, interaction:{mode:'index',intersect:false},
                plugins:{ legend:{position:'top',labels:{color:c.text,boxWidth:12,boxHeight:2,padding:14,font:{size:11}}}, tooltip:ttOpts(c,'g/t') },
                scales:{ x:xScale(c), y:{...yScale(c,'g/t'), beginAtZero:true} }
            }
        });
        @endif
    }

    buildAll();
    document.getElementById('darkToggle').addEventListener('click', () => { setTimeout(buildAll, 60); });

    // ── Preset date buttons ──────────────────────────────────────────────
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const today = new Date();
            let from, to;
            to = today.toISOString().split('T')[0];
            if (this.dataset.months) {
                const m = parseInt(this.dataset.months);
                const d = new Date(today);
                d.setMonth(d.getMonth() - m);
                d.setDate(1);
                from = d.toISOString().split('T')[0];
            } else if (this.dataset.ytd) {
                from = today.getFullYear() + '-01-01';
            }
            document.getElementById('anFrom').value = from;
            document.getElementById('anTo').value   = to;
            document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>
@endpush
