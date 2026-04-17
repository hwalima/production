@extends('layouts.app')
@section('title', 'Assay Trend Analysis')
@section('page-title', 'Assay Results')
@section('content')

@php
    // Grade classification thresholds (g/t Au) — typical open-pit / underground gold
    $grades = [
        ['label' => 'Sub-economic',  'max' => 1,  'color' => '#6b7280', 'bg' => 'rgba(107,114,128,.08)'],
        ['label' => 'Low Grade',     'max' => 3,  'color' => '#3b82f6', 'bg' => 'rgba(59,130,246,.08)'],
        ['label' => 'Medium Grade',  'max' => 8,  'color' => '#fcb913', 'bg' => 'rgba(252,185,19,.10)'],
        ['label' => 'High Grade',    'max' => 999,'color' => '#ef4444', 'bg' => 'rgba(239,68,68,.08)'],
    ];
    $typesMeta = [
        'fire_assay'     => ['label' => 'Fire Assay',     'color' => '#ef4444'],
        'gold_on_carbon' => ['label' => 'Gold on Carbon', 'color' => '#fcb913'],
        'bottle_roll'    => ['label' => 'Bottle Roll',    'color' => '#3b82f6'],
    ];
    $hasAnyData = collect($chartData)->contains(fn($d) => $d['stats'] !== null);
    $hasPurity  = $purityStats !== null;
@endphp

{{-- Page header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Assay Trend Analysis</h1>
        <p style="color:#9ca3af;font-size:.8rem;margin-top:2px;">Gold grade (g/t Au) &amp; purity % over time — geological trend visualisation</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <a href="{{ route('assay.index') }}" class="btn-cancel" style="display:inline-flex;align-items:center;gap:6px;">
            ← Results
        </a>
    </div>
</div>

{{-- Date range filter --}}
<div class="data-card" style="padding:14px 18px;margin-bottom:22px;">
    <form method="GET" action="{{ route('assay.trends') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
        <div>
            <label style="display:block;font-size:.72rem;font-weight:700;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">From</label>
            <input type="date" name="from" class="fc-input" style="min-width:140px;padding:6px 10px;font-size:.85rem;" value="{{ $from }}">
        </div>
        <div>
            <label style="display:block;font-size:.72rem;font-weight:700;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">To</label>
            <input type="date" name="to" class="fc-input" style="min-width:140px;padding:6px 10px;font-size:.85rem;" value="{{ $to }}">
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button type="submit" class="btn-add" style="padding:7px 18px;font-size:.85rem;">Apply</button>
            {{-- Quick presets --}}
            @foreach([['30','30d'],['90','90d'],['180','6mo'],['365','1yr']] as [$days,$lbl])
            @php $pFrom = now()->subDays((int)$days)->format('Y-m-d'); $pTo = now()->format('Y-m-d'); @endphp
            <a href="{{ route('assay.trends', ['from'=>$pFrom,'to'=>$pTo]) }}"
               style="padding:7px 13px;font-size:.78rem;font-weight:600;border-radius:8px;text-decoration:none;border:1px solid var(--topbar-border);color:var(--text);background:var(--input-bg);transition:background .15s;"
               @if($from===$pFrom && $to===$pTo) style="background:#fcb913;color:#001a4d;border-color:#fcb913;" @endif>
                {{ $lbl }}
            </a>
            @endforeach
        </div>
        <div style="margin-left:auto;font-size:.78rem;color:#9ca3af;align-self:center;">
            {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            ({{ \Carbon\Carbon::parse($from)->diffInDays(\Carbon\Carbon::parse($to)) + 1 }} days)
        </div>
    </form>
</div>

{{-- Stats cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:22px;">
    @foreach($typesMeta as $key => $meta)
    @php
        $s = $chartData[$key]['stats'] ?? null;
        $trendIcon  = match($s['trend'] ?? 'flat') { 'up' => '↑', 'down' => '↓', default => '→' };
        $trendColor = match($s['trend'] ?? 'flat') { 'up' => '#34d399', 'down' => '#f87171', default => '#9ca3af' };
        // Grade zone for avg
        $gradeLabel = '—'; $gradeBg = '#6b7280';
        if ($s) {
            foreach ($grades as $g) { if ($s['avg'] <= $g['max']) { $gradeLabel = $g['label']; $gradeBg = $g['color']; break; } }
        }
    @endphp
    <div class="data-card" style="padding:16px 18px;border-top:3px solid {{ $meta['color'] }};">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;">{{ $meta['label'] }}</div>
            @if($s)
            <span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:20px;background:{{ $gradeBg }}22;color:{{ $gradeBg }};border:1px solid {{ $gradeBg }}44;">{{ $gradeLabel }}</span>
            @endif
        </div>
        @if($s)
        <div style="font-size:1.7rem;font-weight:800;color:{{ $meta['color'] }};margin:6px 0 2px;line-height:1.1;">
            {{ number_format($s['avg'],2) }} <span style="font-size:.8rem;font-weight:500;color:#9ca3af;">g/t</span>
            <span style="font-size:.95rem;color:{{ $trendColor }};margin-left:4px;">{{ $trendIcon }}</span>
        </div>
        <div style="font-size:.75rem;color:#9ca3af;display:flex;gap:12px;flex-wrap:wrap;">
            <span>Max <b style="color:var(--text);">{{ number_format($s['max'],2) }}</b></span>
            <span>Min <b style="color:var(--text);">{{ number_format($s['min'],2) }}</b></span>
            <span>n=<b style="color:var(--text);">{{ $s['count'] }}</b></span>
        </div>
        <div style="margin-top:10px;height:3px;border-radius:3px;background:rgba(255,255,255,.07);">
            @php $pct = min(100, max(0, ($s['avg'] / 20) * 100)); @endphp
            <div style="width:{{ $pct }}%;height:3px;background:{{ $meta['color'] }};border-radius:3px;transition:width .6s;"></div>
        </div>
        @else
        <div style="margin-top:10px;font-size:.82rem;color:#9ca3af;font-style:italic;">No data in range</div>
        @endif
    </div>
    @endforeach

    {{-- Purity card --}}
    <div class="data-card" style="padding:16px 18px;border-top:3px solid #34d399;">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;">Gold Purity</div>
        @if($hasPurity)
        <div style="font-size:1.7rem;font-weight:800;color:#34d399;margin:6px 0 2px;line-height:1.1;">
            {{ number_format($purityStats['avg'],1) }}<span style="font-size:.8rem;font-weight:500;color:#9ca3af;">%</span>
        </div>
        <div style="font-size:.75rem;color:#9ca3af;display:flex;gap:12px;flex-wrap:wrap;">
            <span>Max <b style="color:var(--text);">{{ number_format($purityStats['max'],1) }}%</b></span>
            <span>Min <b style="color:var(--text);">{{ number_format($purityStats['min'],1) }}%</b></span>
            <span>n=<b style="color:var(--text);">{{ $purityStats['count'] }}</b></span>
        </div>
        <div style="margin-top:10px;height:3px;border-radius:3px;background:rgba(255,255,255,.07);">
            @php $purityPct = min(100, max(0, $purityStats['avg'])); @endphp
            <div style="width:{{ $purityPct }}%;height:3px;background:#34d399;border-radius:3px;"></div>
        </div>
        @else
        <div style="margin-top:10px;font-size:.82rem;color:#9ca3af;font-style:italic;">No data in range</div>
        @endif
    </div>
</div>

@if(!$hasAnyData && !$hasPurity)
<div class="data-card" style="padding:40px;text-align:center;color:#9ca3af;">
    <div style="font-size:2rem;margin-bottom:12px;">📊</div>
    <div style="font-weight:600;font-size:.95rem;margin-bottom:6px;">No assay data in this date range</div>
    <div style="font-size:.82rem;">Try widening the date range or <a href="{{ route('assay.create') }}" style="color:#fcb913;">add some results</a>.</div>
</div>
@else

{{-- Main trend chart --}}
<div class="data-card" style="padding:20px 22px 16px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div>
            <p style="font-size:.9rem;font-weight:700;color:var(--text);margin:0;">Gold Grade &amp; Purity Trend</p>
            <p style="font-size:.75rem;color:#9ca3af;margin:3px 0 0;">Assay value (g/t Au) by type with 7-sample rolling average — Purity % on right axis</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            {{-- Toggle buttons for each dataset --}}
            <button onclick="toggleDataset(0)" data-idx="0" class="legend-toggle active" style="--c:#ef4444">
                <span class="lt-dot"></span>Fire Assay
            </button>
            <button onclick="toggleDataset(1)" data-idx="1" class="legend-toggle active" style="--c:#fcb913">
                <span class="lt-dot"></span>Gold on Carbon
            </button>
            <button onclick="toggleDataset(2)" data-idx="2" class="legend-toggle active" style="--c:#3b82f6">
                <span class="lt-dot"></span>Bottle Roll
            </button>
            <button onclick="toggleDataset(6)" data-idx="6" class="legend-toggle active" style="--c:#34d399">
                <span class="lt-dot" style="border-radius:2px;width:16px;height:3px;"></span>Purity %
            </button>
            <button onclick="toggleAllRolling()" id="rollingToggle" class="legend-toggle active"
                    style="--c:#ffffff;opacity:.6;font-style:italic;">
                <span class="lt-dot" style="border-top:2px dashed #fff;background:transparent;width:16px;border-radius:0;height:0;"></span>Avg Lines
            </button>
        </div>
    </div>
    <style>
        .legend-toggle {
            display:inline-flex;align-items:center;gap:6px;padding:5px 11px;font-size:.75rem;
            font-weight:600;border-radius:20px;border:1px solid var(--c,#9ca3af);color:var(--c,#9ca3af);
            background:transparent;cursor:pointer;transition:all .15s;white-space:nowrap;
        }
        .legend-toggle.active { background:color-mix(in srgb,var(--c,#9ca3af) 15%,transparent); }
        .legend-toggle .lt-dot { width:10px;height:10px;border-radius:50%;background:var(--c,#9ca3af);flex-shrink:0; }
        .legend-toggle:hover { opacity:.8; }
    </style>
    <canvas id="assayTrendChart" height="90"></canvas>
</div>

{{-- Grade distribution chart --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">

    <div class="data-card" style="padding:20px 22px 16px;">
        <p style="font-size:.9rem;font-weight:700;color:var(--text);margin:0 0 4px;">Grade Distribution</p>
        <p style="font-size:.75rem;color:#9ca3af;margin:0 0 16px;">Proportion of samples per grade zone</p>
        <canvas id="gradeDistChart" height="160"></canvas>
    </div>

    <div class="data-card" style="padding:20px 22px 16px;">
        <p style="font-size:.9rem;font-weight:700;color:var(--text);margin:0 0 4px;">Grade Zone Reference</p>
        <p style="font-size:.75rem;color:#9ca3af;margin:0 0 16px;">Industry standard gold grade classification</p>
        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($grades as $g)
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:12px;height:12px;border-radius:3px;background:{{ $g['color'] }};flex-shrink:0;"></div>
                <div style="flex:1;">
                    <div style="font-size:.82rem;font-weight:600;color:var(--text);">{{ $g['label'] }}</div>
                    <div style="font-size:.72rem;color:#9ca3af;">
                        @if($loop->first) < 1 g/t
                        @elseif($loop->last) > 8 g/t
                        @else {{ $loop->index }} – {{ $g['max'] }} g/t
                        @endif
                    </div>
                </div>
                {{-- Count across all types --}}
                @php
                    $prevMax = $loop->first ? 0 : $grades[$loop->index-1]['max'];
                    $cnt = collect($chartData)
                        ->flatMap(fn($d) => array_filter($d['aligned'], fn($v) => $v !== null))
                        ->filter(fn($v) => $v > $prevMax && $v <= $g['max'])
                        ->count();
                    $total = collect($chartData)->flatMap(fn($d) => array_filter($d['aligned'], fn($v) => $v !== null))->count();
                    $pct = $total > 0 ? round($cnt / $total * 100) : 0;
                @endphp
                <div style="text-align:right;min-width:60px;">
                    <span style="font-weight:700;font-size:.85rem;color:{{ $g['color'] }};">{{ $cnt }}</span>
                    <span style="font-size:.72rem;color:#9ca3af;"> ({{ $pct }}%)</span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Detected vs below detection limit note --}}
        @php
            $belowDL = $assays->where('assay_value', '<=', 0.01)->count();
            $totalA  = $assays->count();
        @endphp
        @if($belowDL > 0)
        <div style="margin-top:16px;padding:10px 12px;border-radius:8px;background:rgba(107,114,128,.1);font-size:.75rem;color:#9ca3af;border-left:2px solid #6b7280;">
            ⚠️ <b>{{ $belowDL }}</b> of {{ $totalA }} samples at or below detection limit (≤ 0.01 g/t)
        </div>
        @endif
    </div>

</div>

{{-- Insight bar --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;margin-top:16px;">
    @foreach($typesMeta as $key => $meta)
    @php $s = $chartData[$key]['stats'] ?? null; @endphp
    @if($s)
    @php
        $cv = $s['avg'] > 0 ? round(($s['max'] - $s['min']) / $s['avg'] * 100, 1) : 0;
        $trendColor = match($s['trend']) { 'up' => '#34d399', 'down' => '#f87171', default => '#9ca3af' };
        $trendText  = match($s['trend']) { 'up' => 'Improving — grades trending upward', 'down' => 'Declining — grades trending downward', default => 'Stable — no significant trend' };
    @endphp
    <div class="data-card" style="padding:14px 16px;border-left:3px solid {{ $meta['color'] }};">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:{{ $meta['color'] }};margin-bottom:6px;">{{ $meta['label'] }} Insight</div>
        <div style="font-size:.82rem;color:var(--text);margin-bottom:4px;">
            <span style="color:{{ $trendColor }};font-weight:700;">{{ $s['trend'] === 'up' ? '↑' : ($s['trend'] === 'down' ? '↓' : '→') }}</span>
            {{ $trendText }}
        </div>
        <div style="font-size:.75rem;color:#9ca3af;">
            Variability (CV): <b style="color:var(--text);">{{ $cv }}%</b>
            @if($cv > 60) — <span style="color:#f87171;">High variability</span>
            @elseif($cv > 30) — <span style="color:#fcb913;">Moderate variability</span>
            @else — <span style="color:#34d399;">Consistent</span>
            @endif
        </div>
        <div style="font-size:.75rem;color:#9ca3af;margin-top:3px;">
            Last reading: <b style="color:{{ $meta['color'] }};">{{ number_format($s['last'],2) }} g/t</b>
            (vs avg {{ number_format($s['avg'],2) }} g/t)
        </div>
    </div>
    @endif
    @endforeach
</div>

@endif {{-- hasAnyData --}}

@push('scripts')
<script>
(function(){
'use strict';

function cc(){
    const d = document.documentElement.classList.contains('dark');
    return {
        grid:   d ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)',
        text:   d ? '#d1d5db' : '#374151',
        ttBg:   d ? '#1f2937' : '#fff',
        ttTitle:d ? '#f9fafb' : '#111827',
        ttBody: d ? '#d1d5db' : '#374151',
        ttBord: d ? '#374151' : '#e5e7eb',
    };
}

// ── Dataset index map:
// 0 = Fire Assay (raw)     3 = Fire Assay (rolling)
// 1 = Gold on Carbon (raw) 4 = Gold on Carbon (rolling)
// 2 = Bottle Roll (raw)    5 = Bottle Roll (rolling)
// 6 = Purity %

const labels = @json($chartLabels);

const fireRaw    = @json($chartData['fire_assay']['aligned']);
const fireRoll   = @json($chartData['fire_assay']['rolling']);
const gocRaw     = @json($chartData['gold_on_carbon']['aligned']);
const gocRoll    = @json($chartData['gold_on_carbon']['rolling']);
const bottleRaw  = @json($chartData['bottle_roll']['aligned']);
const bottleRoll = @json($chartData['bottle_roll']['rolling']);
const purityData = @json($purityAligned);

let trendChart = null;
let rollingVisible = true;

function buildTrendChart(){
    const c = cc();
    if(trendChart) trendChart.destroy();
    trendChart = new Chart(document.getElementById('assayTrendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                // Raw data lines (thicker, with dots)
                { label:'Fire Assay',     data:fireRaw,    yAxisID:'yG', borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.06)',
                  fill:false, borderWidth:2, pointRadius:3, pointHoverRadius:6, tension:.3, spanGaps:true },
                { label:'Gold on Carbon', data:gocRaw,     yAxisID:'yG', borderColor:'#fcb913', backgroundColor:'rgba(252,185,19,.06)',
                  fill:false, borderWidth:2, pointRadius:3, pointHoverRadius:6, tension:.3, spanGaps:true },
                { label:'Bottle Roll',    data:bottleRaw,  yAxisID:'yG', borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.06)',
                  fill:false, borderWidth:2, pointRadius:3, pointHoverRadius:6, tension:.3, spanGaps:true },
                // Rolling average lines (thin, dashed, no dots)
                { label:'Fire Assay (7-sample avg)',     data:fireRoll,   yAxisID:'yG', borderColor:'#ef4444',
                  backgroundColor:'transparent', borderWidth:1.5, borderDash:[5,3], pointRadius:0, tension:.4, spanGaps:true },
                { label:'Gold on Carbon (7-sample avg)', data:gocRoll,    yAxisID:'yG', borderColor:'#fcb913',
                  backgroundColor:'transparent', borderWidth:1.5, borderDash:[5,3], pointRadius:0, tension:.4, spanGaps:true },
                { label:'Bottle Roll (7-sample avg)',    data:bottleRoll, yAxisID:'yG', borderColor:'#3b82f6',
                  backgroundColor:'transparent', borderWidth:1.5, borderDash:[5,3], pointRadius:0, tension:.4, spanGaps:true },
                // Purity on right axis
                { label:'Purity %',       data:purityData, yAxisID:'yP', borderColor:'#34d399', backgroundColor:'rgba(52,211,153,.05)',
                  fill:true, borderWidth:2, pointRadius:2, pointHoverRadius:5, tension:.4, spanGaps:true },
            ]
        },
        options: {
            responsive: true,
            interaction: { mode:'index', intersect:false },
            plugins: {
                legend: { display:false },
                tooltip: {
                    backgroundColor:c.ttBg, titleColor:c.ttTitle, bodyColor:c.ttBody,
                    borderColor:c.ttBord, borderWidth:1, padding:12, cornerRadius:12,
                    filter: item => !item.dataset.label.includes('avg'),
                    callbacks: {
                        label: x => {
                            if(x.parsed.y === null) return null;
                            const u = x.dataset.yAxisID === 'yP' ? '%' : ' g/t';
                            return '  ' + x.dataset.label + ': ' + x.parsed.y.toFixed(2) + u;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks:{ color:c.text, font:{size:10}, maxTicksLimit:12 },
                    grid:{ color:c.grid }, border:{ display:false }
                },
                yG: {
                    type:'linear', position:'left', beginAtZero:true,
                    title:{ display:true, text:'Grade (g/t Au)', color:'#fcb913', font:{size:10} },
                    ticks:{ color:c.text, font:{size:10}, callback:v=>v.toFixed(1)+' g/t' },
                    grid:{ color:c.grid }, border:{ display:false }
                },
                yP: {
                    type:'linear', position:'right', beginAtZero:true,
                    title:{ display:true, text:'Purity (%)', color:'#34d399', font:{size:10} },
                    ticks:{ color:'#34d399', font:{size:10}, callback:v=>v.toFixed(0)+'%' },
                    grid:{ drawOnChartArea:false }, border:{ display:false }
                }
            }
        }
    });
}

window.toggleDataset = function(idx){
    if(!trendChart) return;
    const ds  = trendChart.data.datasets[idx];
    const btn = document.querySelector('[data-idx="'+idx+'"]');
    const hidden = trendChart.getDatasetMeta(idx).hidden;
    trendChart.getDatasetMeta(idx).hidden = !hidden;
    // also toggle the corresponding rolling average (idx+3 for raw 0-2)
    if(idx < 3){
        trendChart.getDatasetMeta(idx+3).hidden = !hidden;
    }
    trendChart.update();
    btn && btn.classList.toggle('active', hidden);
};

window.toggleAllRolling = function(){
    if(!trendChart) return;
    rollingVisible = !rollingVisible;
    [3,4,5].forEach(i => { trendChart.getDatasetMeta(i).hidden = !rollingVisible; });
    trendChart.update();
    document.getElementById('rollingToggle').classList.toggle('active', rollingVisible);
};

buildTrendChart();

// Rebuild on dark mode toggle
document.getElementById('darkToggle') && document.getElementById('darkToggle').addEventListener('click', ()=>{
    setTimeout(()=>{ buildTrendChart(); buildDistChart(); }, 60);
});

// ── Grade Distribution Doughnut ─────────────────────────────────────────────
const allValues = [
    ...fireRaw.filter(v=>v!==null),
    ...gocRaw.filter(v=>v!==null),
    ...bottleRaw.filter(v=>v!==null),
];
const gradeBuckets = [
    { label:'Sub-economic (<1)', max:1,   color:'#6b7280' },
    { label:'Low Grade (1–3)',   max:3,   color:'#3b82f6' },
    { label:'Medium (3–8)',      max:8,   color:'#fcb913' },
    { label:'High Grade (>8)',   max:9999,color:'#ef4444' },
];
function countBucket(min,max){ return allValues.filter(v=>v>min&&v<=max).length; }

let distChart = null;
function buildDistChart(){
    const c = cc();
    if(distChart) distChart.destroy();
    if(allValues.length === 0) return;
    distChart = new Chart(document.getElementById('gradeDistChart').getContext('2d'),{
        type:'doughnut',
        data:{
            labels: gradeBuckets.map(b=>b.label),
            datasets:[{
                data: [
                    countBucket(0,   1),
                    countBucket(1,   3),
                    countBucket(3,   8),
                    countBucket(8, 9999),
                ],
                backgroundColor: gradeBuckets.map(b=>b.color+'cc'),
                borderColor:     gradeBuckets.map(b=>b.color),
                borderWidth:2, hoverOffset:8,
            }]
        },
        options:{
            responsive:true,
            cutout:'62%',
            plugins:{
                legend:{ position:'bottom', labels:{ color:c.text, boxWidth:12, padding:12, font:{size:11} } },
                tooltip:{
                    backgroundColor:c.ttBg, titleColor:c.ttTitle, bodyColor:c.ttBody,
                    borderColor:c.ttBord, borderWidth:1,
                    callbacks:{
                        label: x => {
                            const total = x.dataset.data.reduce((a,b)=>a+b,0);
                            const pct = total>0 ? Math.round(x.parsed/total*100) : 0;
                            return '  ' + x.label + ': ' + x.parsed + ' samples (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
}
buildDistChart();

})();
</script>
@endpush

@endsection
