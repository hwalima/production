@extends('layouts.app')
@section('title', $consumable->name . ' — Stores')
@section('page-title', 'Stores')
@section('content')

{{-- ── Header card ── --}}
<div style="background:var(--card);border-radius:14px;border:1px solid var(--topbar-border);padding:22px 24px;margin-bottom:20px;">
    <div style="display:flex;flex-wrap:wrap;align-items:flex-start;gap:16px;justify-content:space-between;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <h1 style="font-size:1.35rem;font-weight:800;color:var(--text);margin:0;">{{ $consumable->name }}</h1>
                <span style="background:{{ \App\Models\Consumable::categoryBg($consumable->category) }};
                             color:{{ \App\Models\Consumable::categoryColor($consumable->category) }};
                             border:1px solid {{ \App\Models\Consumable::categoryColor($consumable->category) }};
                             border-radius:20px;padding:2px 12px;font-size:.72rem;font-weight:700;">
                    {{ \App\Models\Consumable::categoryLabel($consumable->category) }}
                </span>
            </div>
            @if($consumable->description)
            <p style="font-size:.82rem;color:#9ca3af;margin:6px 0 0;">{{ $consumable->description }}</p>
            @endif
            <p style="font-size:.75rem;color:#9ca3af;margin:4px 0 0;">
                Sold as: {{ $consumable->purchase_unit }}
                &nbsp;|&nbsp; {{ number_format($consumable->units_per_pack, $consumable->units_per_pack==intval($consumable->units_per_pack)?0:2) }} {{ $consumable->use_unit }}(s)/pack
                &nbsp;|&nbsp; Pack cost: {{ $currencySymbol }}{{ number_format($consumable->pack_cost, 2) }}
                &nbsp;|&nbsp; Unit cost: <strong style="color:#fcb913;">{{ $currencySymbol }}{{ number_format($unitCost, 4) }}</strong>/{{ $consumable->use_unit }}
                @if($consumable->reorder_level > 0)
                &nbsp;|&nbsp; Reorder at: {{ number_format($consumable->reorder_level, 0) }} {{ $consumable->use_unit }}s
                @endif
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('consumables.receive.form', $consumable) }}"
               style="padding:8px 16px;border-radius:8px;background:rgba(34,197,94,.12);color:#16a34a;font-weight:700;font-size:.8rem;text-decoration:none;border:1px solid #16a34a;display:flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Receive Stock
            </a>
            <a href="{{ route('consumables.use.form', $consumable) }}"
               style="padding:8px 16px;border-radius:8px;background:rgba(245,158,11,.12);color:#d97706;font-weight:700;font-size:.8rem;text-decoration:none;border:1px solid #d97706;display:flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Issue / Use
            </a>
            <a href="{{ route('consumables.edit', $consumable) }}" class="act-btn act-edit" title="Edit" style="padding:8px 12px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
            <a href="{{ route('consumables.index') }}" class="btn-cancel" style="padding:8px 14px;">&larr; Catalog</a>
        </div>
    </div>
</div>

{{-- ── Stock & value metrics ── --}}
@php
    $outOfStock = $stock <= 0;
    $lowStock   = !$outOfStock && $consumable->reorder_level > 0 && $stock <= $consumable->reorder_level;
    $stockValue = max(0,$stock) * $unitCost;
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px;">
    <div style="background:var(--card);border-radius:12px;padding:16px 18px;
                border:2px solid {{ $outOfStock ? '#ef4444' : ($lowStock ? '#f59e0b' : '#22c55e') }};
                background:{{ $outOfStock ? 'rgba(239,68,68,.06)' : ($lowStock ? 'rgba(245,158,11,.06)' : 'rgba(34,197,94,.04)') }}">
        <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
                    color:{{ $outOfStock ? '#dc2626' : ($lowStock ? '#d97706' : '#16a34a') }};margin-bottom:4px;">
            Current Stock
        </div>
        <div style="font-size:1.9rem;font-weight:800;color:{{ $outOfStock ? '#dc2626' : ($lowStock ? '#d97706' : 'var(--text)') }};">
            {{ number_format($stock, $stock==intval($stock)?0:2) }}
        </div>
        <div style="font-size:.72rem;color:#9ca3af;">{{ $consumable->use_unit }}s</div>
    </div>
    <div style="background:var(--card);border-radius:12px;padding:16px 18px;border:1px solid var(--topbar-border);">
        <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Total Received</div>
        <div style="font-size:1.9rem;font-weight:800;color:var(--text);">{{ number_format($stockIn, $stockIn==intval($stockIn)?0:2) }}</div>
        <div style="font-size:.72rem;color:#9ca3af;">{{ $consumable->use_unit }}s in</div>
    </div>
    <div style="background:var(--card);border-radius:12px;padding:16px 18px;border:1px solid var(--topbar-border);">
        <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Total Issued</div>
        <div style="font-size:1.9rem;font-weight:800;color:var(--text);">{{ number_format($stockOut, $stockOut==intval($stockOut)?0:2) }}</div>
        <div style="font-size:.72rem;color:#9ca3af;">{{ $consumable->use_unit }}s out</div>
    </div>
    <div style="background:var(--card);border-radius:12px;padding:16px 18px;border:1px solid var(--topbar-border);">
        <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:4px;">Value in Stock</div>
        <div style="font-size:1.5rem;font-weight:800;color:#fcb913;">{{ $currencySymbol }}{{ number_format($stockValue, 2) }}</div>
        <div style="font-size:.72rem;color:#9ca3af;">{{ $currencySymbol }}{{ number_format($totalSpent, 2) }} total purchased</div>
    </div>
</div>

{{-- ── Movements table ── --}}
<div class="page-header" style="margin-bottom:12px;">
    <h2 style="font-size:1rem;font-weight:700;color:var(--text);margin:0;">Stock Movements</h2>
</div>
<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table" style="min-width:700px;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th class="th-c">Dir</th>
                <th class="th-r">Qty ({{ $consumable->use_unit }})</th>
                <th class="th-r">Unit Cost</th>
                <th class="th-r">Total Cost</th>
                <th>Reference</th>
                <th>By</th>
                <th class="th-c">Del</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $mv)
            @php
                $isIn  = $mv->direction === 'in';
                $badge = match($mv->type) {
                    'purchase'   => ['Purchase',   '#16a34a','rgba(34,197,94,.1)'],
                    'usage'      => ['Usage',       '#d97706','rgba(245,158,11,.1)'],
                    'adjustment' => ['Adjustment',  '#6b7280','rgba(107,114,128,.1)'],
                    'return'     => ['Return',      '#2563eb','rgba(37,99,235,.1)'],
                    default      => [ucfirst($mv->type),'#9ca3af','rgba(156,163,175,.1)'],
                };
            @endphp
            <tr>
                <td style="font-size:.8rem;">{{ \Carbon\Carbon::parse($mv->movement_date)->format('d M Y') }}</td>
                <td>
                    <span style="background:{{ $badge[2] }};color:{{ $badge[1] }};border-radius:20px;
                                 padding:2px 10px;font-size:.7rem;font-weight:700;">
                        {{ $badge[0] }}
                    </span>
                </td>
                <td class="td-c">
                    <span style="font-weight:800;font-size:1rem;color:{{ $isIn ? '#16a34a' : '#d97706' }};">
                        {{ $isIn ? '↓' : '↑' }}
                    </span>
                </td>
                <td class="td-r" style="font-weight:700;color:{{ $isIn ? '#16a34a' : 'var(--text)' }};">
                    {{ $isIn ? '+' : '−' }}{{ number_format($mv->quantity, $mv->quantity==intval($mv->quantity)?0:2) }}
                </td>
                <td class="td-r" style="font-size:.8rem;">{{ $currencySymbol }}{{ number_format($mv->unit_cost, 4) }}</td>
                <td class="td-r" style="font-weight:600;color:#fcb913;">{{ $currencySymbol }}{{ number_format($mv->total_cost, 2) }}</td>
                <td style="font-size:.78rem;color:#9ca3af;max-width:160px;word-break:break-word;">{{ $mv->reference ?: '—' }}</td>
                <td style="font-size:.75rem;color:#9ca3af;">{{ $mv->user?->name ?? '—' }}</td>
                <td class="td-c">
                    <form method="POST"
                          action="{{ route('consumables.movements.destroy', [$consumable, $mv]) }}"
                          onsubmit="event.preventDefault();confirmDelete('Delete this movement record?',this)">
                        @csrf @method('DELETE')
                        <button type="submit" class="act-btn act-delete" title="Delete movement">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="9">No stock movements yet. <a href="{{ route('consumables.receive.form', $consumable) }}" style="color:#fcb913;">Receive first delivery →</a></td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($movements->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--topbar-border);">{{ $movements->links() }}</div>
    @endif
</div>
@endsection
