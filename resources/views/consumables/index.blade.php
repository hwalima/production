@extends('layouts.app')
@section('title', 'Stores & Consumables')
@section('page-title', 'Stores')
@section('content')

{{-- ── Stats row ── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px;">
    <div style="background:var(--card);border-radius:14px;padding:16px 18px;border:1px solid var(--topbar-border);">
        <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Total Items</div>
        <div style="font-size:1.8rem;font-weight:800;color:var(--text);">{{ $totalItems }}</div>
    </div>
    <div style="background:var(--card);border-radius:14px;padding:16px 18px;border:1px solid {{ $lowStockCount ? '#f59e0b' : 'var(--topbar-border)' }};{{ $lowStockCount ? 'background:rgba(245,158,11,.06);' : '' }}">
        <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:{{ $lowStockCount ? '#d97706' : '#9ca3af' }};margin-bottom:4px;">Low / Out of Stock</div>
        <div style="font-size:1.8rem;font-weight:800;color:{{ $lowStockCount ? '#d97706' : 'var(--text)' }};">{{ $lowStockCount }}</div>
    </div>
    <div style="background:var(--card);border-radius:14px;padding:16px 18px;border:1px solid var(--topbar-border);">
        <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Catalog Value</div>
        <div style="font-size:1.8rem;font-weight:800;color:#fcb913;">
            {{ $currencySymbol }}{{ number_format($consumables->sum(fn($c) => max(0, $c->current_stock) * $c->unit_cost), 2) }}
        </div>
    </div>
</div>

{{-- ── Page header + add button ── --}}
<div class="page-header">
    <h1 class="page-title">Stores &amp; Consumables</h1>
    <a href="{{ route('consumables.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Item
    </a>
</div>

{{-- ── Category filter pills ── --}}
<div style="display:flex;flex-wrap:wrap;gap:7px;margin-bottom:16px;">
    <a href="{{ route('consumables.index') }}"
       style="padding:5px 14px;border-radius:20px;font-size:.75rem;font-weight:700;text-decoration:none;
              background:{{ $category==='all'?'#fcb913':'var(--input-bg)' }};
              color:{{ $category==='all'?'#001a4d':'#9ca3af' }};
              border:1px solid {{ $category==='all'?'#fcb913':'var(--topbar-border)' }};">
        All
    </a>
    @foreach(['blasting','chemicals','mechanical','ppe','general'] as $cat)
    <a href="{{ route('consumables.index', ['category'=>$cat]) }}"
       style="padding:5px 14px;border-radius:20px;font-size:.75rem;font-weight:700;text-decoration:none;
              background:{{ $category===$cat ? \App\Models\Consumable::categoryColor($cat) : 'var(--input-bg)' }};
              color:{{ $category===$cat ? '#fff' : '#9ca3af' }};
              border:1px solid {{ $category===$cat ? \App\Models\Consumable::categoryColor($cat) : 'var(--topbar-border)' }};">
        {{ \App\Models\Consumable::categoryLabel($cat) }}
    </a>
    @endforeach
    {{-- Any custom categories --}}
    @foreach($categories->diff(['blasting','chemicals','mechanical','ppe','general']) as $cat)
    <a href="{{ route('consumables.index', ['category'=>$cat]) }}"
       style="padding:5px 14px;border-radius:20px;font-size:.75rem;font-weight:700;text-decoration:none;
              background:{{ $category===$cat?'#6b7280':'var(--input-bg)' }};
              color:{{ $category===$cat?'#fff':'#9ca3af' }};border:1px solid var(--topbar-border);">
        {{ ucfirst($cat) }}
    </a>
    @endforeach
</div>

{{-- ── Main table ── --}}
<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table" style="min-width:780px;">
        <thead>
            <tr>
                <th>Item / Description</th>
                <th>Category</th>
                <th class="th-r">Pack</th>
                <th class="th-r">Pack Cost</th>
                <th class="th-r">Unit Cost</th>
                <th class="th-r">In Stock</th>
                <th class="th-c">Status</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consumables as $item)
            <tr style="{{ $item->out_of_stock ? 'opacity:.65;' : ($item->low_stock ? 'background:rgba(245,158,11,.05);' : '') }}">
                <td>
                    <div style="font-weight:700;color:var(--text);">{{ $item->name }}</div>
                    @if($item->description)
                    <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">{{ Str::limit($item->description, 55) }}</div>
                    @endif
                </td>
                <td data-export="{{ ucfirst($item->category) }}">
                    <span style="background:{{ \App\Models\Consumable::categoryBg($item->category) }};
                                 color:{{ \App\Models\Consumable::categoryColor($item->category) }};
                                 border:1px solid {{ \App\Models\Consumable::categoryColor($item->category) }};
                                 border-radius:20px;padding:2px 10px;font-size:.72rem;font-weight:700;white-space:nowrap;">
                        {{ \App\Models\Consumable::categoryLabel($item->category) }}
                    </span>
                </td>
                <td class="td-r" style="font-size:.8rem;color:#9ca3af;">
                    {{ number_format($item->units_per_pack, $item->units_per_pack == intval($item->units_per_pack) ? 0 : 2) }}
                    {{ $item->use_unit }}
                    / {{ $item->purchase_unit }}
                </td>
                <td class="td-r">{{ $currencySymbol }}{{ number_format($item->pack_cost, 2) }}</td>
                <td class="td-r" style="color:#fcb913;font-weight:600;">
                    {{ $currencySymbol }}{{ number_format($item->unit_cost, $item->unit_cost < 1 ? 4 : 2) }}
                    <span style="font-size:.68rem;color:#9ca3af;font-weight:400;">/ {{ $item->use_unit }}</span>
                </td>
                <td class="td-r">
                    <span style="font-weight:700;color:{{ $item->out_of_stock ? '#ef4444' : ($item->low_stock ? '#f59e0b' : 'var(--text)') }};">
                        {{ number_format($item->current_stock, $item->current_stock == intval($item->current_stock) ? 0 : 2) }}
                    </span>
                    <span style="font-size:.72rem;color:#9ca3af;"> {{ $item->use_unit }}</span>
                    @if($item->reorder_level > 0)
                    <div class="no-export" style="font-size:.65rem;color:#9ca3af;">reorder ≤ {{ number_format($item->reorder_level, 0) }}</div>
                    @endif
                </td>
                <td class="td-c">
                    @if($item->out_of_stock)
                        <span style="background:#fee2e2;color:#dc2626;border-radius:20px;padding:2px 10px;font-size:.7rem;font-weight:700;">OUT</span>
                    @elseif($item->low_stock)
                        <span style="background:rgba(245,158,11,.12);color:#d97706;border:1px solid #d97706;border-radius:20px;padding:2px 10px;font-size:.7rem;font-weight:700;">LOW</span>
                    @else
                        <span style="background:rgba(34,197,94,.1);color:#16a34a;border:1px solid #16a34a;border-radius:20px;padding:2px 10px;font-size:.7rem;font-weight:700;">OK</span>
                    @endif
                </td>
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('consumables.show', $item) }}" class="act-btn act-view" title="View history">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('consumables.receive.form', $item) }}" class="act-btn" title="Receive stock"
                           style="background:rgba(34,197,94,.12);color:#16a34a;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </a>
                        <a href="{{ route('consumables.use.form', $item) }}" class="act-btn" title="Issue / use stock"
                           style="background:rgba(245,158,11,.12);color:#d97706;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </a>
                        <a href="{{ route('consumables.edit', $item) }}" class="act-btn act-edit" title="Edit item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('consumables.destroy', $item) }}" style="display:contents"
                              onsubmit="event.preventDefault();confirmDelete('Delete {{ addslashes($item->name) }} and all its stock history? This cannot be undone.',this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="8">No items in the stores catalog yet. <a href="{{ route('consumables.create') }}" style="color:#fcb913;">Add the first item →</a></td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@endsection
