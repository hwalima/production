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

<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Shift</th>
                <th>Mining Site</th>
                <th>Ore Hoisted</th>
                <th>Waste Hoisted</th>
                <th style="background:rgba(0,0,0,.18);">&#x3A3; Uncrushed Stk</th>
                <th>Ore Crushed</th>
                <th style="background:rgba(0,0,0,.18);">&#x3A3; Unmilled Stk</th>
                <th>Ore Milled</th>
                <th>Gold Smelted</th>
                <th>Purity %</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productions as $prod)
            <tr>
                <td data-sort="{{ $prod->date->format('Y-m-d') }}"><span style="font-weight:600;">{{ $prod->date->format('d M Y') }}</span></td>
                <td>{{ $prod->shift ?? '—' }}</td>
                <td>{{ $prod->mining_site ?? '—' }}</td>
                <td>{{ number_format($prod->ore_hoisted, 1) }} t</td>
                <td>{{ number_format($prod->waste_hoisted, 1) }} t</td>
                <td style="color:#fcb913;font-weight:600;">{{ number_format($prod->uncrushed_stockpile, 1) }} t</td>
                <td>{{ number_format($prod->ore_crushed, 1) }} t</td>
                <td style="color:#fcb913;font-weight:600;">{{ number_format($prod->unmilled_stockpile, 1) }} t</td>
                <td>{{ number_format($prod->ore_milled, 1) }} t</td>
                <td>{{ number_format($prod->gold_smelted, 2) }} g</td>
                <td>{{ $prod->purity_percentage }}%</td>
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
    </table>
    </div>
</div>

<div class="mt-4">{{ $productions->links() }}</div>
@endsection
