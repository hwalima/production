@extends('layouts.app')
@section('page-title', 'Production Calendar')
@section('content')

@php
use Carbon\Carbon;

$prevMonth      = Carbon::parse($month . '-01')->subMonth()->format('Y-m');
$nextMonth      = Carbon::parse($month . '-01')->addMonth()->format('Y-m');
$today          = Carbon::today();
$daysInMonth    = $start->daysInMonth;
$leadingBlanks  = $start->dayOfWeek; // 0=Sun, 6=Sat
$isCurrentMonth = Carbon::parse($month . '-01')->isSameMonth($today);
@endphp

<style>
/* ── Calendar layout ───────────────────────────────────────── */
.cal-wrap { max-width:960px; margin:0 auto; }

.cal-header {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:20px; gap:12px; flex-wrap:wrap;
}
.cal-nav { display:flex; align-items:center; gap:10px; }
.cal-nav-btn {
    display:inline-flex; align-items:center; justify-content:center;
    width:36px; height:36px; border-radius:10px;
    border:1px solid var(--topbar-border); background:var(--card);
    color:var(--text); cursor:pointer; font-size:1.1rem;
    text-decoration:none; transition:background .15s,color .15s,border-color .15s;
}
.cal-nav-btn:hover { background:#fcb913; color:#001a4d; border-color:#fcb913; }
.cal-nav-btn.today-btn { font-size:.72rem; font-weight:800; width:auto; padding:0 12px; letter-spacing:.04em; text-transform:uppercase; }
.cal-month-label { font-size:1.25rem; font-weight:800; color:var(--text); min-width:160px; text-align:center; }
.cal-view-toggle {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 16px; border-radius:10px; font-size:.8rem; font-weight:700;
    background:var(--card); border:1px solid var(--topbar-border);
    color:var(--text); text-decoration:none; transition:background .15s,color .15s,border-color .15s;
}
.cal-view-toggle:hover { background:#fcb913; color:#001a4d; border-color:#fcb913; }

/* ── Summary strip ─────────────────────────────────────────── */
.cal-summary {
    display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px;
}
.cal-stat {
    background:var(--card); border:1px solid var(--topbar-border);
    border-radius:12px; padding:14px 16px;
}
.cal-stat-label {
    font-size:.62rem; font-weight:700; letter-spacing:.1em;
    text-transform:uppercase; color:#9ca3af; margin-bottom:4px;
}
.cal-stat-value { font-size:1.4rem; font-weight:800; color:var(--text); line-height:1.1; }
.cal-stat-sub { font-size:.71rem; color:#9ca3af; margin-top:3px; }

/* ── Grid container ────────────────────────────────────────── */
.cal-grid {
    background:var(--card); border:1px solid var(--topbar-border);
    border-radius:16px; overflow:hidden;
}
.cal-weekdays {
    display:grid; grid-template-columns:repeat(7,1fr);
    background:linear-gradient(135deg,#001f5c,#000f30);
}
.cal-weekday {
    padding:10px 0; text-align:center;
    font-size:.64rem; font-weight:800; letter-spacing:.1em;
    text-transform:uppercase; color:rgba(255,255,255,.5);
}
.cal-weekday.weekend { color:rgba(252,185,19,.7); }

.cal-days { display:grid; grid-template-columns:repeat(7,1fr); gap:1px; background:var(--topbar-border); }

/* ── Day cell ──────────────────────────────────────────────── */
.cal-cell {
    background:var(--card);
    min-height:96px; padding:8px 10px;
    position:relative; display:flex; flex-direction:column;
    transition:filter .12s, transform .12s;
    cursor:default;
}
.cal-cell.has-data { cursor:pointer; }
.cal-cell.has-data:hover { filter:brightness(1.09); transform:scale(1.02); z-index:2; }
.cal-cell.is-blank { background:rgba(0,0,0,.04); cursor:default; }
html.dark .cal-cell.is-blank { background:rgba(0,0,0,.3); }
.cal-cell.is-future { opacity:.45; pointer-events:none; }
.cal-cell.is-today { box-shadow:inset 0 0 0 2px #fcb913; }

.cal-day-num {
    font-size:.74rem; font-weight:700; color:#9ca3af; line-height:1; margin-bottom:auto;
}
.cal-cell.has-data  .cal-day-num { color:inherit; }
.cal-cell.is-today  .cal-day-num { color:#fcb913 !important; }

.cal-badges { display:flex; gap:4px; margin-bottom:6px; flex-wrap:wrap; }
.cal-badge-multi {
    font-size:.56rem; font-weight:800; background:#fcb913; color:#001a4d;
    border-radius:4px; padding:1px 5px; line-height:1.4;
}

.cal-gold-row { margin-top:auto; }
.cal-gold {
    font-size:1.05rem; font-weight:800; line-height:1;
    display:flex; align-items:baseline; gap:2px;
}
.cal-gold-unit { font-size:.6rem; font-weight:600; opacity:.7; }
.cal-ore-row { font-size:.62rem; margin-top:3px; opacity:.65; }

/* ── Legend ────────────────────────────────────────────────── */
.cal-legend {
    display:flex; align-items:center; gap:10px; padding:12px 16px;
    border-top:1px solid var(--topbar-border); flex-wrap:wrap;
}
.cal-legend-label { font-size:.63rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.08em; }
.cal-legend-swatch { display:flex; align-items:center; gap:4px; font-size:.63rem; color:#9ca3af; }
.cal-legend-box { width:14px; height:14px; border-radius:3px; border:1px solid rgba(255,255,255,.08); flex-shrink:0; }

/* ── No records banner ─────────────────────────────────────── */
.cal-empty {
    text-align:center; padding:40px 16px; color:#9ca3af; font-size:.9rem;
}
.cal-empty a { color:#fcb913; font-weight:700; text-decoration:none; }
.cal-empty a:hover { text-decoration:underline; }

/* ── Responsive ────────────────────────────────────────────── */
@media(max-width:640px) {
    .cal-summary { grid-template-columns:1fr 1fr; }
    .cal-summary .cal-stat:last-child { grid-column:span 2; }
    .cal-cell { min-height:68px; padding:6px; }
    .cal-gold { font-size:.85rem; }
    .cal-ore-row { display:none; }
    .cal-month-label { font-size:1rem; min-width:120px; }
}
@media(max-width:400px) {
    .cal-cell { min-height:52px; padding:4px; }
    .cal-gold { font-size:.75rem; }
    .cal-badges { display:none; }
}
</style>

<div class="cal-wrap">

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="cal-header">
        <div class="cal-nav">
            <a href="{{ route('production.calendar', ['month' => $prevMonth]) }}" class="cal-nav-btn" title="Previous month">&#8592;</a>
            <div class="cal-month-label">{{ Carbon::parse($month.'-01')->format('F Y') }}</div>
            <a href="{{ route('production.calendar', ['month' => $nextMonth]) }}" class="cal-nav-btn" title="Next month">&#8594;</a>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            @unless($isCurrentMonth)
            <a href="{{ route('production.calendar') }}" class="cal-nav-btn today-btn" title="Current month">Today</a>
            @endunless
            <a href="{{ route('production.index') }}" class="cal-view-toggle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Table View
            </a>
        </div>
    </div>

    {{-- ── Summary strip ────────────────────────────────────── --}}
    <div class="cal-summary">
        <div class="cal-stat" style="border-top:3px solid #fcb913;">
            <div class="cal-stat-label">Total Gold Smelted</div>
            <div class="cal-stat-value" style="color:#d97706;">
                {{ number_format($totalGold, 1) }}<span style="font-size:.75rem;font-weight:600;color:#9ca3af;margin-left:3px;">g</span>
            </div>
            <div class="cal-stat-sub">{{ Carbon::parse($month.'-01')->format('F Y') }}</div>
        </div>
        <div class="cal-stat" style="border-top:3px solid #3b82f6;">
            <div class="cal-stat-label">Active Days</div>
            <div class="cal-stat-value">
                {{ $activeDays }}<span style="font-size:.9rem;font-weight:600;color:#9ca3af;"> / {{ $start->daysInMonth }}</span>
            </div>
            <div class="cal-stat-sub">days with production records</div>
        </div>
        <div class="cal-stat" style="border-top:3px solid #22c55e;">
            <div class="cal-stat-label">Best Day</div>
            @if($bestDayKey)
            <div class="cal-stat-value" style="color:#16a34a;">
                {{ number_format($bestGold, 1) }}<span style="font-size:.75rem;font-weight:600;color:#9ca3af;margin-left:3px;">g</span>
            </div>
            <div class="cal-stat-sub">{{ Carbon::parse($bestDayKey)->format('d M Y') }}</div>
            @else
            <div class="cal-stat-value" style="color:#9ca3af;">—</div>
            <div class="cal-stat-sub">no records this month</div>
            @endif
        </div>
    </div>

    {{-- ── Calendar grid ─────────────────────────────────────── --}}
    <div class="cal-grid">

        {{-- Weekday headers --}}
        <div class="cal-weekdays">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $wi => $wday)
            <div class="cal-weekday{{ in_array($wi,[0,6]) ? ' weekend' : '' }}">{{ $wday }}</div>
            @endforeach
        </div>

        {{-- Day cells --}}
        <div class="cal-days">

            {{-- Leading blank cells before the 1st --}}
            @for($b = 0; $b < $leadingBlanks; $b++)
            <div class="cal-cell is-blank"></div>
            @endfor

            {{-- One cell per day of month --}}
            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateStr  = Carbon::parse($month . sprintf('-%02d', $day))->format('Y-m-d');
                $isToday  = $dateStr === $today->format('Y-m-d');
                $isFuture = $dateStr > $today->format('Y-m-d');
                $data     = $byDate[$dateStr] ?? null;

                // Heat-map background
                $bgStyle     = '';
                $lightText   = false;  // false = use default text colour
                if ($data && $data['gold'] > 0) {
                    $intensity = $data['gold'] / $maxGold;            // 0..1
                    $alpha     = round(0.13 + ($intensity * 0.77), 3); // 0.13..0.90
                    $bgStyle   = "background:rgba(252,185,19,{$alpha});";
                    $lightText = ($intensity > 0.55); // dark text on bright gold cells
                }

                // Drill-down link
                $link = null;
                if ($data) {
                    $link = ($data['count'] === 1)
                        ? route('production.show', $data['ids'][0])
                        : route('production.index', ['from' => $dateStr, 'to' => $dateStr]);
                }
            @endphp

            <div class="cal-cell{{ $data ? ' has-data' : '' }}{{ $isToday ? ' is-today' : '' }}{{ $isFuture ? ' is-future' : '' }}"
                 style="{{ $bgStyle }}"
                 @if($link) onclick="window.location='{{ $link }}'" @endif
                 @if($data) title="{{ number_format($data['gold'],2) }}g gold • {{ number_format($data['ore'],1) }}t milled{{ $data['count'] > 1 ? ' • '.$data['count'].' shifts' : '' }}" @endif>

                {{-- Day number + multi-shift badge --}}
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px;">
                    <span class="cal-day-num"
                          style="{{ $lightText ? 'color:rgba(0,26,77,.6);' : '' }}">{{ $day }}</span>
                    @if($data && $data['count'] > 1)
                    <span class="cal-badge-multi">{{ $data['count'] }}×</span>
                    @endif
                </div>

                @if($data)
                <div class="cal-gold-row">
                    <div class="cal-gold" style="{{ $lightText ? 'color:#001a4d;' : 'color:var(--text);' }}">
                        {{ number_format($data['gold'], 1) }}<span class="cal-gold-unit">g</span>
                    </div>
                    <div class="cal-ore-row" style="{{ $lightText ? 'color:rgba(0,26,77,.55);' : '' }}">
                        {{ number_format($data['ore'], 1) }} t milled
                    </div>
                </div>
                @endif

            </div>
            @endfor

        </div>{{-- .cal-days --}}

        {{-- Legend --}}
        <div class="cal-legend">
            <span class="cal-legend-label">Intensity:</span>
            <span class="cal-legend-swatch">
                <span class="cal-legend-box" style="background:var(--card);"></span> No data
            </span>
            <span class="cal-legend-swatch">
                <span class="cal-legend-box" style="background:rgba(252,185,19,.15);"></span> Low
            </span>
            <span class="cal-legend-swatch">
                <span class="cal-legend-box" style="background:rgba(252,185,19,.40);"></span> Medium
            </span>
            <span class="cal-legend-swatch">
                <span class="cal-legend-box" style="background:rgba(252,185,19,.67);"></span> High
            </span>
            <span class="cal-legend-swatch">
                <span class="cal-legend-box" style="background:rgba(252,185,19,.90);"></span> Peak
            </span>
            <span class="cal-legend-swatch" style="margin-left:6px;">
                <span class="cal-legend-box" style="background:transparent;box-shadow:inset 0 0 0 2px #fcb913;"></span> Today
            </span>
            <span class="cal-legend-swatch">
                <span class="cal-badge-multi" style="border-radius:4px;">2×</span> Multi-shift
            </span>
        </div>

    </div>{{-- .cal-grid --}}

    @if($activeDays === 0)
    <div class="cal-empty">
        No production records for {{ Carbon::parse($month.'-01')->format('F Y') }}.
        @if(auth()->user()->canWrite())
        <a href="{{ route('production.create') }}">Add first record &rarr;</a>
        @endif
    </div>
    @endif

</div>{{-- .cal-wrap --}}
@endsection
