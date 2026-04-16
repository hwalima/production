@extends('layouts.app')
@section('page-title', 'Daily Production')
@section('content')

<div class="page-header">
    <h1 class="page-title">Daily Production Records</h1>
    @if(auth()->user()->canWrite())
    <a href="{{ route('production.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Record
    </a>
    @endif
</div>

@include('partials.date-filter', ['routeName' => 'production.index'])

<div class="data-card" style="padding:0;overflow:hidden;">
    <div class="tbl-scroll">
    <table class="data-table" style="font-size:.78rem;min-width:1200px;">
        <thead>
            <tr style="white-space:nowrap;">
                <th>Date</th>
                <th>Shift</th>
                <th>Site</th>
                <th class="th-r">Hoisted</th>
                <th class="th-r" style="background:rgba(96,165,250,.15);">Hoist Tgt</th>
                <th class="th-r" style="background:rgba(96,165,250,.15);">Hoist Var</th>
                <th class="th-r">Waste</th>
                <th class="th-r" style="background:rgba(252,185,19,.15);">Uncrush Stk</th>
                <th class="th-r">Crushed</th>
                <th class="th-r" style="background:rgba(252,185,19,.15);">Unmill Stk</th>
                <th class="th-r">Milled</th>
                <th class="th-r" style="background:rgba(96,165,250,.15);">Mill Tgt</th>
                <th class="th-r" style="background:rgba(96,165,250,.15);">Mill Var</th>
                <th class="th-r">Gold (g)</th>
                <th class="th-r">Purity</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productions as $prod)
            <tr style="white-space:nowrap;">
                <td data-sort="{{ $prod->date->format('Y-m-d') }}"><span style="font-weight:600;">{{ $prod->date->format('d M Y') }}</span></td>
                <td>{{ $prod->shift ?? '—' }}</td>
                <td>{{ $prod->mining_site ?? '—' }}</td>
                <td class="td-r">{{ number_format($prod->ore_hoisted, 1) }} t</td>
                @php $hv = $prod->ore_hoisted_target !== null ? (float)$prod->ore_hoisted_target - (float)$prod->ore_hoisted : null; @endphp
                <td class="td-r" style="color:#9ca3af;">{{ $prod->ore_hoisted_target !== null ? number_format($prod->ore_hoisted_target, 1).' t' : '—' }}</td>
                <td class="td-r" style="font-weight:600;color:{{ $hv === null ? '#6b7280' : ($hv > 0 ? '#ef4444' : '#22c55e') }}">
                    {{ $hv === null ? '—' : (($hv > 0 ? '+' : '').number_format($hv, 1).' t') }}
                </td>
                <td class="td-r">{{ number_format($prod->waste_hoisted, 1) }} t</td>
                <td class="td-r" style="color:#fcb913;font-weight:600;">{{ number_format($prod->uncrushed_stockpile, 1) }} t</td>
                <td class="td-r">{{ number_format($prod->ore_crushed, 1) }} t</td>
                <td class="td-r" style="color:#fcb913;font-weight:600;">{{ number_format($prod->unmilled_stockpile, 1) }} t</td>
                <td class="td-r">{{ number_format($prod->ore_milled, 1) }} t</td>
                @php $mv = $prod->ore_milled_target !== null ? (float)$prod->ore_milled_target - (float)$prod->ore_milled : null; @endphp
                <td class="td-r" style="color:#9ca3af;">{{ $prod->ore_milled_target !== null ? number_format($prod->ore_milled_target, 1).' t' : '—' }}</td>
                <td class="td-r" style="font-weight:600;color:{{ $mv === null ? '#6b7280' : ($mv > 0 ? '#ef4444' : '#22c55e') }}">
                    {{ $mv === null ? '—' : (($mv > 0 ? '+' : '').number_format($mv, 1).' t') }}
                </td>
                <td class="td-r">{{ number_format($prod->gold_smelted, 2) }} g</td>
                <td class="td-r">{{ $prod->purity_percentage }}%</td>
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('production.show', $prod) }}" class="act-btn act-view" title="View record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        @if(auth()->user()->canWrite())
                        <a href="{{ route('production.edit', $prod) }}" class="act-btn act-edit" title="Edit record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('production.destroy', $prod) }}" style="display:contents" onsubmit="event.preventDefault();confirmDelete('Delete this production record? This cannot be undone.',this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete record">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="12">No production records yet.</td></tr>
            @endforelse
        </tbody>
        @if($totals && ($totals->ore_hoisted || $totals->ore_milled || $totals->gold_smelted))
        @php
            $tHoistedVar = ($totals->ore_hoisted_target && $totals->ore_hoisted)
                ? round($totals->ore_hoisted_target - $totals->ore_hoisted, 2) : null;
            $tMilledVar = ($totals->ore_milled_target && $totals->ore_milled)
                ? round($totals->ore_milled_target - $totals->ore_milled, 2) : null;
        @endphp
        <tfoot>
            <tr>
                <td colspan="3" style="font-weight:700;font-size:.78rem;background:#001a4d;color:#fff;padding:7px 10px;">TOTALS</td>
                <td class="td-r" style="font-weight:700;font-size:.78rem;background:#001a4d;color:#fff;padding:7px 6px;"
                    data-export="{{ number_format($totals->ore_hoisted ?? 0, 1) }} t">{{ number_format($totals->ore_hoisted ?? 0, 1) }} t</td>
                <td class="td-r" style="font-size:.78rem;background:#001a4d;color:#93c5fd;padding:7px 6px;"
                    data-export="{{ $totals->ore_hoisted_target ? number_format($totals->ore_hoisted_target, 1).' t' : '—' }}">{{ $totals->ore_hoisted_target ? number_format($totals->ore_hoisted_target, 1).' t' : '—' }}</td>
                <td class="td-r" style="font-weight:700;font-size:.78rem;background:#001a4d;padding:7px 6px;color:{{ $tHoistedVar === null ? '#6b7280' : ($tHoistedVar > 0 ? '#fca5a5' : '#86efac') }};"
                    data-export="{{ $tHoistedVar === null ? '—' : (($tHoistedVar > 0 ? '+' : '').number_format($tHoistedVar, 1).' t') }}">{{ $tHoistedVar === null ? '—' : (($tHoistedVar > 0 ? '+' : '').number_format($tHoistedVar, 1).' t') }}</td>
                <td class="td-r" style="font-weight:700;font-size:.78rem;background:#001a4d;color:#fff;padding:7px 6px;"
                    data-export="{{ number_format($totals->waste_hoisted ?? 0, 1) }} t">{{ number_format($totals->waste_hoisted ?? 0, 1) }} t</td>
                <td class="td-r" style="font-size:.78rem;background:#001a4d;color:#6b7280;padding:7px 6px;" data-export="—">—</td>
                <td class="td-r" style="font-weight:700;font-size:.78rem;background:#001a4d;color:#fff;padding:7px 6px;"
                    data-export="{{ number_format($totals->ore_crushed ?? 0, 1) }} t">{{ number_format($totals->ore_crushed ?? 0, 1) }} t</td>
                <td class="td-r" style="font-size:.78rem;background:#001a4d;color:#6b7280;padding:7px 6px;" data-export="—">—</td>
                <td class="td-r" style="font-weight:700;font-size:.78rem;background:#001a4d;color:#fff;padding:7px 6px;"
                    data-export="{{ number_format($totals->ore_milled ?? 0, 1) }} t">{{ number_format($totals->ore_milled ?? 0, 1) }} t</td>
                <td class="td-r" style="font-size:.78rem;background:#001a4d;color:#93c5fd;padding:7px 6px;"
                    data-export="{{ $totals->ore_milled_target ? number_format($totals->ore_milled_target, 1).' t' : '—' }}">{{ $totals->ore_milled_target ? number_format($totals->ore_milled_target, 1).' t' : '—' }}</td>
                <td class="td-r" style="font-weight:700;font-size:.78rem;background:#001a4d;padding:7px 6px;color:{{ $tMilledVar === null ? '#6b7280' : ($tMilledVar > 0 ? '#fca5a5' : '#86efac') }};"
                    data-export="{{ $tMilledVar === null ? '—' : (($tMilledVar > 0 ? '+' : '').number_format($tMilledVar, 1).' t') }}">{{ $tMilledVar === null ? '—' : (($tMilledVar > 0 ? '+' : '').number_format($tMilledVar, 1).' t') }}</td>
                <td class="td-r" style="font-weight:700;font-size:.78rem;background:#001a4d;color:#fcd34d;padding:7px 6px;"
                    data-export="{{ number_format($totals->gold_smelted ?? 0, 2) }} g">{{ number_format($totals->gold_smelted ?? 0, 2) }} g</td>
                <td class="td-r" style="font-size:.78rem;background:#001a4d;color:#d1d5db;padding:7px 6px;"
                    data-export="{{ number_format($totals->avg_purity ?? 0, 2) }}%">{{ number_format($totals->avg_purity ?? 0, 2) }}%</td>
                <td style="background:#001a4d;" class="no-export"></td>
            </tr>
        </tfoot>
        @endif
    </table>
    </div>
</div>

<div class="mt-4">{{ $productions->links() }}</div>
@endsection
