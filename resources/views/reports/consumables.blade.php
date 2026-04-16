@extends('layouts.app')
@section('title', 'Stores Inventory Report')
@section('page-title', 'Reports')
@section('content')
<div class="page-header">
    <h1 class="page-title">Stores Inventory Report</h1>
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="{{ route('reports.consumables.pdf') }}"
           style="padding:8px 16px;border-radius:8px;background:#b45309;color:#fff;font-weight:700;font-size:.82rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="12" y2="18"/><line x1="15" y1="15" x2="12" y2="18"/></svg>
            Export PDF
        </a>
        <a href="{{ route('consumables.index') }}"
           style="padding:8px 16px;border-radius:8px;background:var(--input-bg);color:#9ca3af;font-weight:700;font-size:.82rem;text-decoration:none;border:1px solid var(--topbar-border);">
            ← Stores
        </a>
    </div>
</div>

{{-- Summary row --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px;">
    <div style="background:var(--card);border-radius:12px;padding:16px 18px;border:1px solid var(--topbar-border);">
        <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Total Items</div>
        <div style="font-size:1.8rem;font-weight:800;color:var(--text);">{{ $consumables->count() }}</div>
    </div>
    <div style="background:var(--card);border-radius:12px;padding:16px 18px;border:1px solid var(--topbar-border);">
        <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Total Stock Value</div>
        <div style="font-size:1.5rem;font-weight:800;color:#fcb913;">{{ $currencySymbol }}{{ number_format($totalValue, 2) }}</div>
    </div>
    <div style="background:var(--card);border-radius:12px;padding:16px 18px;border:1px solid {{ $lowStockCount ? '#f59e0b' : 'var(--topbar-border)' }};{{ $lowStockCount ? 'background:rgba(245,158,11,.05);' : '' }}">
        <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:{{ $lowStockCount ? '#d97706' : '#9ca3af' }};margin-bottom:4px;">Low / Out of Stock</div>
        <div style="font-size:1.8rem;font-weight:800;color:{{ $lowStockCount ? '#d97706' : 'var(--text)' }};">{{ $lowStockCount }}</div>
    </div>
</div>

{{-- Per-category sections --}}
@foreach(['blasting'=>'🧨 Blasting','chemicals'=>'⚗️ Chemicals','mechanical'=>'🔧 Mechanical','ppe'=>'🦺 PPE','general'=>'📦 General'] as $cat => $label)
@php $catItems = $consumables->where('category', $cat); @endphp
@if($catItems->count())
<h2 style="font-size:.9rem;font-weight:700;color:var(--text);margin:16px 0 8px;border-left:3px solid #fcb913;padding-left:8px;">{{ $label }}</h2>
<div class="data-card" style="margin-bottom:16px;">
    <div class="tbl-scroll">
    <table class="data-table" style="min-width:640px;">
        <thead>
            <tr>
                <th>Item</th>
                <th>Pack / Use Unit</th>
                <th class="th-r">Pack Cost</th>
                <th class="th-r">Unit Cost</th>
                <th class="th-r">In Stock</th>
                <th class="th-r">Stock Value</th>
                <th class="th-c">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($catItems as $item)
            <tr style="{{ $item->out_of_stock ? 'opacity:.6;' : ($item->low_stock ? 'background:rgba(245,158,11,.04);' : '') }}">
                <td>
                    <a href="{{ route('consumables.show', $item) }}" style="color:var(--text);font-weight:600;text-decoration:none;">{{ $item->name }}</a>
                    @if($item->description)
                    <div style="font-size:.7rem;color:#9ca3af;">{{ Str::limit($item->description,50) }}</div>
                    @endif
                </td>
                <td style="font-size:.78rem;color:#9ca3af;">
                    {{ number_format($item->units_per_pack, $item->units_per_pack==intval($item->units_per_pack)?0:2) }}
                    {{ $item->use_unit }} / {{ $item->purchase_unit }}
                </td>
                <td class="td-r">{{ $currencySymbol }}{{ number_format($item->pack_cost, 2) }}</td>
                <td class="td-r" style="color:#fcb913;font-weight:600;">
                    {{ $currencySymbol }}{{ number_format($item->unit_cost, $item->unit_cost<1?4:2) }}
                </td>
                <td class="td-r" style="font-weight:700;color:{{ $item->out_of_stock?'#ef4444':($item->low_stock?'#f59e0b':'var(--text)') }};">
                    {{ number_format($item->current_stock, $item->current_stock==intval($item->current_stock)?0:2) }}
                    <span style="font-size:.72rem;color:#9ca3af;font-weight:400;"> {{ $item->use_unit }}</span>
                </td>
                <td class="td-r" style="font-weight:700;color:#fcb913;">{{ $currencySymbol }}{{ number_format($item->stock_value, 2) }}</td>
                <td class="td-c">
                    @if($item->out_of_stock)
                        <span style="background:#fee2e2;color:#dc2626;border-radius:20px;padding:2px 9px;font-size:.7rem;font-weight:700;">OUT</span>
                    @elseif($item->low_stock)
                        <span style="background:rgba(245,158,11,.12);color:#d97706;border:1px solid #d97706;border-radius:20px;padding:2px 9px;font-size:.7rem;font-weight:700;">LOW</span>
                    @else
                        <span style="background:rgba(34,197,94,.1);color:#16a34a;border:1px solid #16a34a;border-radius:20px;padding:2px 9px;font-size:.7rem;font-weight:700;">OK</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="font-weight:700;">{{ $label }} Subtotal</td>
                <td class="td-r" style="font-weight:700;">{{ $currencySymbol }}{{ number_format($catItems->sum('stock_value'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
@endif
@endforeach

{{-- Any custom categories --}}
@php $other = $consumables->whereNotIn('category', ['blasting','chemicals','mechanical','ppe','general']); @endphp
@if($other->count())
<h2 style="font-size:.9rem;font-weight:700;color:var(--text);margin:16px 0 8px;border-left:3px solid #fcb913;padding-left:8px;">General / Other</h2>
<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table" style="min-width:640px;">
        <thead><tr><th>Item</th><th>Pack / Use Unit</th><th class="th-r">Pack Cost</th><th class="th-r">Unit Cost</th><th class="th-r">In Stock</th><th class="th-r">Stock Value</th><th class="th-c">Status</th></tr></thead>
        <tbody>
            @foreach($other as $item)
            <tr>
                <td><a href="{{ route('consumables.show', $item) }}" style="color:var(--text);font-weight:600;text-decoration:none;">{{ $item->name }}</a></td>
                <td style="font-size:.78rem;color:#9ca3af;">{{ $item->units_per_pack }} {{ $item->use_unit }} / {{ $item->purchase_unit }}</td>
                <td class="td-r">{{ $currencySymbol }}{{ number_format($item->pack_cost, 2) }}</td>
                <td class="td-r" style="color:#fcb913;font-weight:600;">{{ $currencySymbol }}{{ number_format($item->unit_cost, 4) }}</td>
                <td class="td-r" style="font-weight:700;">{{ number_format($item->current_stock, 2) }} <span style="font-size:.72rem;color:#9ca3af;">{{ $item->use_unit }}</span></td>
                <td class="td-r" style="font-weight:700;color:#fcb913;">{{ $currencySymbol }}{{ number_format($item->stock_value, 2) }}</td>
                <td class="td-c">@if($item->out_of_stock)<span style="color:#dc2626;font-weight:700;">OUT</span>@elseif($item->low_stock)<span style="color:#d97706;font-weight:700;">LOW</span>@else<span style="color:#16a34a;font-weight:700;">OK</span>@endif</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

@endsection

<h2 class="page-title" style="font-size:1.1rem;margin-bottom:12px;">Blasting Consumables</h2>
<div class="data-card" style="margin-bottom:24px;">
    <div class="tbl-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Fractures</th>
                    <th>Fuse</th>
                    <th>Carmes IEDs</th>
                    <th>Power Cords</th>
                    <th>ANFO</th>
                    <th>Oil</th>
                    <th>Drill Bits</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blasting as $b)
                <tr>
                    <td>{{ $b->date->format('d M Y') }}</td>
                    <td class="text-center">{{ $b->fractures }}</td>
                    <td class="text-center">{{ $b->fuse }}</td>
                    <td class="text-center">{{ $b->carmes_ieds }}</td>
                    <td class="text-center">{{ $b->power_cords }}</td>
                    <td class="text-center">{{ $b->anfo }}</td>
                    <td class="text-center">{{ $b->oil }}</td>
                    <td class="text-center">{{ $b->drill_bits }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:24px;color:#9ca3af;">No blasting records for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<h2 class="page-title" style="font-size:1.1rem;margin-bottom:12px;">Chemicals Usage</h2>
<div class="data-card">
    <div class="tbl-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>NaCN</th>
                    <th>Lime</th>
                    <th>Caustic Soda</th>
                    <th>Iodised Salt</th>
                    <th>Mercury</th>
                    <th>Steel Balls</th>
                    <th>H&#8322;O&#8322;</th>
                    <th>Borax</th>
                    <th>HNO&#8323;</th>
                    <th>H&#8322;SO&#8324;</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chemicals as $c)
                <tr>
                    <td>{{ $c->date->format('d M Y') }}</td>
                    <td class="text-center">{{ $c->sodium_cyanide }}</td>
                    <td class="text-center">{{ $c->lime }}</td>
                    <td class="text-center">{{ $c->caustic_soda }}</td>
                    <td class="text-center">{{ $c->iodised_salt }}</td>
                    <td class="text-center">{{ $c->mercury }}</td>
                    <td class="text-center">{{ $c->steel_balls }}</td>
                    <td class="text-center">{{ $c->hydrogen_peroxide }}</td>
                    <td class="text-center">{{ $c->borax }}</td>
                    <td class="text-center">{{ $c->nitric_acid }}</td>
                    <td class="text-center">{{ $c->sulphuric_acid }}</td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;padding:24px;color:#9ca3af;">No chemical records for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
