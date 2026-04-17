@extends('layouts.app')
@section('page-title', 'Targets vs Actuals')

@push('styles')
<style>
.tgt-wrap { max-width:1100px; margin:0 auto; }

/* ── Stat cards ── */
.tgt-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-bottom:22px; }
.tgt-stat {
    background:var(--card); border:1px solid var(--topbar-border);
    border-radius:14px; padding:16px 18px;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
    position:relative; overflow:hidden;
}
.tgt-stat .ts-label { font-size:.63rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#9ca3af; margin-bottom:6px; }
.tgt-stat .ts-val   { font-size:1.45rem; font-weight:900; line-height:1.1; color:var(--text); }
.tgt-stat .ts-sub   { font-size:.68rem; color:#9ca3af; margin-top:4px; }
.tgt-stat .ts-orb   { position:absolute; width:80px; height:80px; border-radius:50%; top:-24px; right:-18px; opacity:.18; filter:blur(18px); pointer-events:none; }

/* ── Chart card ── */
.tgt-chart-card {
    background:var(--card); border:1px solid var(--topbar-border);
    border-radius:16px; padding:20px 22px;
    box-shadow:0 2px 12px rgba(0,0,0,.08);
    margin-bottom:22px;
}
.tgt-chart-card canvas { max-height: 340px; }

/* ── Legend chips ── */
.tgt-legend { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; align-items:center; }
.tgt-chip { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:99px; font-size:.7rem; font-weight:600; }
.chip-green  { background:rgba(34,197,94,.12);  color:#16a34a; border:1px solid rgba(34,197,94,.3);  }
.chip-amber  { background:rgba(251,191,36,.12); color:#92400e; border:1px solid rgba(251,191,36,.3); }
.chip-red    { background:rgba(239,68,68,.12);  color:#b91c1c; border:1px solid rgba(239,68,68,.3);  }
.chip-gray   { background:rgba(156,163,175,.1); color:#6b7280; border:1px solid rgba(156,163,175,.25);}
.chip-target { background:rgba(252,185,19,.12); color:#b45309; border:1px solid rgba(252,185,19,.3); }
html.dark .chip-green  { color:#4ade80; }
html.dark .chip-amber  { color:#fcd34d; }
html.dark .chip-red    { color:#f87171; }
html.dark .chip-target { color:#fbbf24; }

/* ── Tab toggle ── */
.tgt-tabs { display:flex; gap:6px; }
.tgt-tab {
    padding:6px 16px; border-radius:8px; font-size:.78rem; font-weight:700;
    border:1.5px solid var(--topbar-border); background:transparent;
    color:#9ca3af; cursor:pointer; transition:all .15s;
}
.tgt-tab:hover  { border-color:#fcb913; color:#fcb913; }
.tgt-tab.active { background:#fcb913; border-color:#fcb913; color:#001a4d; }

/* ── Month selector ── */
.month-nav { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.mnav-btn {
    width:32px; height:32px; border-radius:8px;
    background:var(--input-bg); border:1px solid var(--topbar-border);
    color:var(--text); cursor:pointer; font-size:1rem; display:flex; align-items:center; justify-content:center;
    transition:background .15s,border-color .15s;
}
.mnav-btn:hover { background:rgba(252,185,19,.15); border-color:#fcb913; }
.mnav-select {
    background:var(--input-bg); border:1px solid var(--topbar-border);
    color:var(--text); border-radius:9px; padding:6px 12px;
    font-size:.82rem; font-weight:600; cursor:pointer; outline:none;
    transition:border-color .15s;
}
.mnav-select:focus { border-color:#fcb913; }

/* ── Progress bar ── */
.tgt-progress { height:8px; border-radius:99px; background:rgba(0,0,0,.07); overflow:hidden; margin-top:8px; }
html.dark .tgt-progress { background:rgba(255,255,255,.08); }
.tgt-progress-fill { height:100%; border-radius:99px; transition:width .8s cubic-bezier(.4,0,.2,1); }

/* ── Status badge ── */
.status-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:99px; font-size:.65rem; font-weight:700;
}
.badge-green { background:rgba(34,197,94,.12);  color:#16a34a; border:1px solid rgba(34,197,94,.3);  }
.badge-red   { background:rgba(239,68,68,.12);  color:#b91c1c; border:1px solid rgba(239,68,68,.3);  }
html.dark .badge-green { color:#4ade80; }
html.dark .badge-red   { color:#f87171; }

/* ── Table ── */
.tgt-table-card { background:var(--card); border:1px solid var(--topbar-border); border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.07); }
.tgt-table-head { padding:14px 18px; font-size:.75rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#9ca3af; border-bottom:1px solid var(--topbar-border); display:flex; align-items:center; justify-content:space-between; }
.tgt-table { width:100%; border-collapse:collapse; }
.tgt-table th { padding:9px 14px; font-size:.7rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#9ca3af; border-bottom:1px solid var(--topbar-border); text-align:left; }
.tgt-table th.th-r { text-align:right; }
.tgt-table td { padding:8px 14px; font-size:.82rem; color:var(--text); border-bottom:1px solid var(--topbar-border); }
.tgt-table td.td-r { text-align:right; }
.tgt-table tbody tr:last-child td { border-bottom:none; }
.tgt-table tbody tr:hover { background:rgba(252,185,19,.04); }

@media(max-width:640px) {
    .tgt-stats { grid-template-columns:repeat(2, 1fr); }
    .tgt-tab { font-size:.72rem; padding:5px 11px; }
}
</style>
@endpush

@section('content')
@php use Carbon\Carbon; @endphp

<div class="tgt-wrap">

{{-- ── Header ─────────────────────────────────────────────────────────── --}}
<div class="page-header" style="margin-bottom:20px;">
    <div>
        <h1 style="font-size:1.35rem;font-weight:800;color:var(--text);">Targets vs Actuals</h1>
        <p style="color:#9ca3af;font-size:.82rem;margin-top:2px;">
            Daily &amp; weekly gold production vs monthly target pace
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('production.index') }}" style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;font-size:.8rem;font-weight:700;border-radius:10px;border:1px solid var(--topbar-border);background:var(--card);color:var(--text);text-decoration:none;">
            ← Records
        </a>
        <a href="{{ route('production.calendar') }}" style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;font-size:.8rem;font-weight:700;border-radius:10px;border:1px solid var(--topbar-border);background:var(--card);color:var(--text);text-decoration:none;">
            📅 Calendar
        </a>
    </div>
</div>

{{-- ── Month selector ──────────────────────────────────────────────────── --}}
<div class="tgt-chart-card" style="padding:14px 18px;margin-bottom:20px;">
    <form method="GET" action="{{ route('production.targets') }}" id="monthForm" style="display:inline;">
        <div class="month-nav">
            <span style="font-size:.63rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9ca3af;">Month</span>
            @php
                $prevMonth = Carbon::parse($month . '-01')->subMonth()->format('Y-m');
                $nextMonth = Carbon::parse($month . '-01')->addMonth()->format('Y-m');
            @endphp
            <a href="{{ route('production.targets', ['month' => $prevMonth]) }}" class="mnav-btn" title="Previous month">‹</a>
            <select name="month" class="mnav-select" onchange="document.getElementById('monthForm').submit()">
                @foreach($months as $m)
                    <option value="{{ $m['value'] }}" {{ $m['value'] === $month ? 'selected' : '' }}>{{ $m['label'] }}</option>
                @endforeach
            </select>
            <a href="{{ route('production.targets', ['month' => $nextMonth]) }}" class="mnav-btn" title="Next month">›</a>
            <span style="margin-left:4px;font-size:.8rem;font-weight:700;color:var(--text);">
                {{ $start->format('F Y') }}
            </span>
            @if($month === \Carbon\Carbon::now()->format('Y-m'))
                <span class="status-badge badge-green" style="margin-left:4px;">Current Month</span>
            @endif
        </div>
    </form>
</div>

{{-- ── Summary stat cards ──────────────────────────────────────────────── --}}
<div class="tgt-stats">

    {{-- Actual --}}
    <div class="tgt-stat" style="border-top:3px solid #f59e0b;">
        <div class="ts-orb" style="background:#f59e0b;"></div>
        <div class="ts-label">Actual Gold</div>
        <div class="ts-val" style="background:linear-gradient(135deg,#f59e0b,#fcc104);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            {{ number_format($totalActual, 1) }}<span style="font-size:.8rem;font-weight:600;"> g</span>
        </div>
        <div class="ts-sub">smelted this month</div>
    </div>

    {{-- Target --}}
    <div class="tgt-stat" style="border-top:3px solid #6b7280;">
        <div class="ts-orb" style="background:#9ca3af;"></div>
        <div class="ts-label">Monthly Target</div>
        <div class="ts-val">{{ number_format($goldTarget, 0) }}<span style="font-size:.8rem;font-weight:600;"> g</span></div>
        <div class="ts-sub">{{ number_format($dailyTarget, 1) }} g/day pace</div>
    </div>

    {{-- Achieved % --}}
    <div class="tgt-stat" style="border-top:3px solid {{ $achieved >= 100 ? '#22c55e' : ($achieved >= 75 ? '#fbbf24' : '#ef4444') }};">
        <div class="ts-label">Achieved</div>
        <div class="ts-val" style="color:{{ $achieved >= 100 ? '#22c55e' : ($achieved >= 75 ? '#fbbf24' : '#ef4444') }};">
            {{ $achieved }}<span style="font-size:.9rem;">%</span>
        </div>
        <div class="tgt-progress">
            <div class="tgt-progress-fill" style="width:{{ min(100, $achieved) }}%;background:{{ $achieved >= 100 ? '#22c55e' : ($achieved >= 75 ? '#fbbf24' : '#ef4444') }};"></div>
        </div>
    </div>

    {{-- Remaining --}}
    <div class="tgt-stat" style="border-top:3px solid #38bdf8;">
        <div class="ts-orb" style="background:#38bdf8;"></div>
        <div class="ts-label">Remaining</div>
        <div class="ts-val" style="background:linear-gradient(135deg,#0369a1,#38bdf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            {{ number_format($remaining, 1) }}<span style="font-size:.8rem;font-weight:600;"> g</span>
        </div>
        <div class="ts-sub">{{ $daysLeft > 0 ? $daysLeft . ' days left' : 'month complete' }}</div>
    </div>

    {{-- Best Day --}}
    <div class="tgt-stat" style="border-top:3px solid #a78bfa;">
        <div class="ts-orb" style="background:#a78bfa;"></div>
        <div class="ts-label">Best Day</div>
        <div class="ts-val" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            @if($bestVal > 0)
                {{ number_format($bestVal, 1) }}<span style="font-size:.8rem;font-weight:600;"> g</span>
            @else
                <span style="font-size:1rem;color:#9ca3af;">—</span>
            @endif
        </div>
        <div class="ts-sub">{{ $bestDayKey ? Carbon::parse($bestDayKey)->format('d M') : 'no records' }}</div>
    </div>

    {{-- On-track projection --}}
    <div class="tgt-stat" style="border-top:3px solid {{ $onTrack ? '#22c55e' : '#ef4444' }};">
        <div class="ts-label">Projected</div>
        <div class="ts-val" style="color:{{ $onTrack ? '#22c55e' : '#ef4444' }};">
            {{ number_format($paceGold, 0) }}<span style="font-size:.8rem;"> g</span>
        </div>
        <div class="ts-sub">
            @if($daysRecorded > 0)
                <span class="status-badge {{ $onTrack ? 'badge-green' : 'badge-red' }}">
                    {{ $onTrack ? '✓ On Track' : '↓ Below Pace' }}
                </span>
            @else
                no data yet
            @endif
        </div>
    </div>

</div>

{{-- ── Main chart card ─────────────────────────────────────────────────── --}}
<div class="tgt-chart-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
        <div>
            <p style="font-size:.95rem;font-weight:800;color:var(--text);margin:0;">
                Gold Production vs Target — {{ $start->format('F Y') }}
            </p>
            <p style="font-size:.75rem;color:#9ca3af;margin:2px 0 0;">
                Daily target pace: <b style="color:#fcb913;">{{ number_format($dailyTarget, 2) }} g/day</b>
                &bull; Days with data: <b style="color:var(--text);">{{ $daysRecorded }} / {{ $daysInMonth }}</b>
                @if($daysMet > 0)
                    &bull; Days meeting target: <b style="color:#22c55e;">{{ $daysMet }}</b>
                @endif
            </p>
        </div>
        <div class="tgt-tabs">
            <button class="tgt-tab active" id="tabDaily"   onclick="switchTab('daily')">Daily</button>
            <button class="tgt-tab"        id="tabWeekly"  onclick="switchTab('weekly')">Weekly</button>
        </div>
    </div>

    <div class="tgt-legend">
        <span class="tgt-chip chip-green">&#9646; Met target (≥ {{ number_format($dailyTarget, 1) }} g)</span>
        <span class="tgt-chip chip-amber">&#9646; Close (≥ 75%)</span>
        <span class="tgt-chip chip-red">&#9646; Below target</span>
        <span class="tgt-chip chip-gray">&#9646; No data</span>
        <span class="tgt-chip chip-target">— Daily target line</span>
    </div>

    <div id="chartDaily"><canvas id="dailyChart"></canvas></div>
    <div id="chartWeekly" style="display:none;"><canvas id="weeklyChart"></canvas></div>
</div>

{{-- ── Daily breakdown table ──────────────────────────────────────────── --}}
@if($daysRecorded > 0)
<div class="tgt-table-card">
    <div class="tgt-table-head">
        <span>Daily Breakdown</span>
        <span style="font-size:.7rem;color:#9ca3af;">{{ $daysRecorded }} days with production data</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="tgt-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="th-r">Actual (g)</th>
                    <th class="th-r">Daily Target (g)</th>
                    <th class="th-r">Variance (g)</th>
                    <th class="th-r">Vs Target</th>
                </tr>
            </thead>
            <tbody>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dt  = $start->copy()->addDays($d - 1);
                        $key = $dt->format('Y-m-d');
                        $act = null;
                        // Rebuild from dailyActual array
                        $actVal = $dailyActual[$d - 1] ?? null;
                        if ($actVal === null) continue; // future
                        if ($actVal == 0 && !in_array($key, array_keys(array_filter($dailyActual, fn($v) => $v > 0)))) {
                            // Check if there's genuinely 0 recorded vs just missing
                        }
                        $variance = $actVal - $dailyTarget;
                        $pct = $dailyTarget > 0 ? round(($actVal / $dailyTarget) * 100, 1) : 0;
                    @endphp
                    @if($actVal > 0)
                    <tr>
                        <td style="font-weight:600;">{{ $dt->format('D, d M Y') }}</td>
                        <td class="td-r" style="font-weight:700;">{{ number_format($actVal, 2) }}</td>
                        <td class="td-r" style="color:#9ca3af;">{{ number_format($dailyTarget, 2) }}</td>
                        <td class="td-r" style="font-weight:700;color:{{ $variance >= 0 ? '#22c55e' : '#ef4444' }};">
                            {{ ($variance >= 0 ? '+' : '') . number_format($variance, 2) }}
                        </td>
                        <td class="td-r">
                            @if($pct >= 100)
                                <span class="status-badge badge-green">{{ $pct }}%</span>
                            @elseif($pct >= 75)
                                <span style="font-size:.7rem;font-weight:700;color:#fbbf24;">{{ $pct }}%</span>
                            @else
                                <span class="status-badge badge-red">{{ $pct }}%</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                @endfor
                {{-- Totals row --}}
                @php $totalVariance = $totalActual - ($dailyTarget * $daysRecorded); @endphp
                <tr style="background:rgba(252,185,19,.06);font-weight:700;border-top:2px solid var(--topbar-border);">
                    <td style="font-weight:800;">TOTAL ({{ $daysRecorded }} days)</td>
                    <td class="td-r">{{ number_format($totalActual, 2) }} g</td>
                    <td class="td-r" style="color:#9ca3af;">{{ number_format($dailyTarget * $daysRecorded, 2) }} g</td>
                    <td class="td-r" style="color:{{ $totalVariance >= 0 ? '#22c55e' : '#ef4444' }};">
                        {{ ($totalVariance >= 0 ? '+' : '') . number_format($totalVariance, 2) }} g
                    </td>
                    <td class="td-r">
                        <span class="status-badge {{ $achieved >= 100 ? 'badge-green' : 'badge-red' }}">{{ $achieved }}%</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@else
<div style="text-align:center;padding:48px;color:#9ca3af;background:var(--card);border-radius:16px;border:1px solid var(--topbar-border);">
    <div style="font-size:2rem;margin-bottom:10px;">📊</div>
    <div style="font-weight:700;margin-bottom:6px;">No production data for {{ $start->format('F Y') }}</div>
    <a href="{{ route('production.create') }}" style="color:#fcb913;font-size:.82rem;font-weight:600;">Add production record →</a>
</div>
@endif

</div>{{-- /.tgt-wrap --}}
@endsection

@push('scripts')
<script>
(function () {
    const dailyLabels   = @json($dailyLabels);
    const dailyActual   = @json($dailyActual);
    const dailyColors   = @json($dailyColors);
    const dailyBorder   = @json($dailyBorder);
    const dailyTarget   = {{ $dailyTarget }};

    const weeklyLabels  = @json($weeklyLabels);
    const weeklyActual  = @json($weeklyActual);
    const weeklyTargets = @json($weeklyTargets);
    const weeklyColors  = @json($weeklyColors);

    function cc() {
        const dark = document.documentElement.classList.contains('dark');
        return {
            grid:    dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)',
            text:    dark ? '#9ca3af' : '#6b7280',
            ttBg:    dark ? '#1e2a3a' : '#fff',
            ttTitle: dark ? '#f3f4f6' : '#111827',
            ttBody:  dark ? '#d1d5db' : '#374151',
            ttBord:  dark ? '#374151' : '#e5e7eb',
        };
    }

    let dailyChart  = null;
    let weeklyChart = null;

    function buildDailyChart() {
        const c = cc();
        if (dailyChart) dailyChart.destroy();

        // Target line: same value for every day
        const targetLine = dailyLabels.map(() => dailyTarget);

        dailyChart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: dailyLabels,
                datasets: [
                    {
                        label: 'Actual Gold (g)',
                        data: dailyActual,
                        backgroundColor: dailyColors,
                        borderColor: dailyBorder,
                        borderWidth: 1.5,
                        borderRadius: 4,
                        barPercentage: 0.75,
                        spanGaps: false,
                    },
                    {
                        label: 'Daily Target (' + dailyTarget.toFixed(1) + ' g)',
                        data: targetLine,
                        type: 'line',
                        borderColor: '#fcb913',
                        borderDash: [6, 4],
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        fill: false,
                        tension: 0,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: c.text, boxWidth: 14, boxHeight: 2, padding: 16, font: { size: 11 } },
                    },
                    tooltip: {
                        backgroundColor: c.ttBg, titleColor: c.ttTitle, bodyColor: c.ttBody,
                        borderColor: c.ttBord, borderWidth: 1, padding: 10, cornerRadius: 10,
                        callbacks: {
                            label: ctx => {
                                const v = ctx.parsed.y;
                                if (v === null) return null;
                                if (ctx.datasetIndex === 1) return '  Target: ' + v.toFixed(2) + ' g';
                                const diff = v - dailyTarget;
                                const sign = diff >= 0 ? '+' : '';
                                return ['  Actual: ' + v.toFixed(2) + ' g', '  vs Target: ' + sign + diff.toFixed(2) + ' g'];
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: c.grid }, border: { display: false },
                        ticks: { color: c.text, font: { size: 10 }, maxRotation: 0 },
                    },
                    y: {
                        grid: { color: c.grid }, border: { display: false },
                        ticks: {
                            color: c.text, font: { size: 11 },
                            callback: v => v.toFixed(1) + ' g',
                        },
                        min: 0,
                    },
                },
            },
        });
    }

    function buildWeeklyChart() {
        const c = cc();
        if (weeklyChart) weeklyChart.destroy();

        weeklyChart = new Chart(document.getElementById('weeklyChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: weeklyLabels,
                datasets: [
                    {
                        label: 'Actual Gold (g)',
                        data: weeklyActual,
                        backgroundColor: weeklyColors,
                        borderColor: weeklyColors.map(col => col.replace('0.72)', '1)')),
                        borderWidth: 1.5,
                        borderRadius: 6,
                        barPercentage: 0.55,
                    },
                    {
                        label: 'Weekly Target (g)',
                        data: weeklyTargets,
                        backgroundColor: 'rgba(252,185,19,0.15)',
                        borderColor: '#fcb913',
                        borderWidth: 2,
                        borderDash: [6, 4],
                        borderRadius: 6,
                        barPercentage: 0.55,
                        type: 'bar',
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: c.text, boxWidth: 14, padding: 16, font: { size: 11 } },
                    },
                    tooltip: {
                        backgroundColor: c.ttBg, titleColor: c.ttTitle, bodyColor: c.ttBody,
                        borderColor: c.ttBord, borderWidth: 1, padding: 10, cornerRadius: 10,
                        callbacks: {
                            label: ctx => {
                                const v = ctx.parsed.y;
                                const label = ctx.dataset.label;
                                if (ctx.datasetIndex === 0) {
                                    const tgt = weeklyTargets[ctx.dataIndex];
                                    const diff = v - tgt;
                                    const sign = diff >= 0 ? '+' : '';
                                    return ['  ' + label + ': ' + v.toFixed(2) + ' g', '  vs Target: ' + sign + diff.toFixed(2) + ' g'];
                                }
                                return '  ' + label + ': ' + v.toFixed(2) + ' g';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: c.grid }, border: { display: false },
                        ticks: { color: c.text, font: { size: 11 } },
                    },
                    y: {
                        grid: { color: c.grid }, border: { display: false },
                        ticks: { color: c.text, font: { size: 11 }, callback: v => v.toFixed(1) + ' g' },
                        min: 0,
                    },
                },
            },
        });
    }

    window.switchTab = function (tab) {
        document.getElementById('tabDaily').classList.toggle('active', tab === 'daily');
        document.getElementById('tabWeekly').classList.toggle('active', tab === 'weekly');
        document.getElementById('chartDaily').style.display  = tab === 'daily'  ? '' : 'none';
        document.getElementById('chartWeekly').style.display = tab === 'weekly' ? '' : 'none';
        if (tab === 'weekly' && !weeklyChart) buildWeeklyChart();
    };

    buildDailyChart();

    // Rebuild on dark mode toggle
    document.getElementById('darkToggle')?.addEventListener('click', () => {
        setTimeout(() => {
            buildDailyChart();
            if (weeklyChart) { weeklyChart.destroy(); weeklyChart = null; }
            const active = document.getElementById('tabWeekly').classList.contains('active');
            if (active) buildWeeklyChart();
        }, 60);
    });
})();
</script>
@endpush
