@extends('layouts.app')
@section('page-title', 'Chemicals')
@section('content')

<div class="page-header">
    <h1 class="page-title">Chemicals Inventory</h1>
    <a href="{{ route('chemicals.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Record
    </a>
</div>

@include('partials.date-filter', ['routeName' => 'chemicals.index'])

<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th class="th-r">NaCN (kg)</th>
                <th class="th-r">Lime (kg)</th>
                <th class="th-r">Caustic (kg)</th>
                <th class="th-r">Iod. Salt (kg)</th>
                <th class="th-r">Mercury (g)</th>
                <th class="th-r">Steel Balls (kg)</th>
                <th class="th-r">H₂O₂ (L)</th>
                <th class="th-r">Borax (kg)</th>
                <th class="th-r">HNO₃ (L)</th>
                <th class="th-r">H₂SO₄ (L)</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chemicals as $chem)
            <tr>
                <td data-sort="{{ $chem->date->format('Y-m-d') }}"><span style="font-weight:600;">{{ $chem->date->format('d M Y') }}</span></td>
                <td class="td-r">{{ $chem->sodium_cyanide }}</td>
                <td class="td-r">{{ $chem->lime }}</td>
                <td class="td-r">{{ $chem->caustic_soda }}</td>
                <td class="td-r">{{ $chem->iodised_salt }}</td>
                <td class="td-r">{{ $chem->mercury }}</td>
                <td class="td-r">{{ $chem->steel_balls }}</td>
                <td class="td-r">{{ $chem->hydrogen_peroxide }}</td>
                <td class="td-r">{{ $chem->borax }}</td>
                <td class="td-r">{{ $chem->nitric_acid }}</td>
                <td class="td-r">{{ $chem->sulphuric_acid }}</td>
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('chemicals.show', $chem) }}" class="act-btn act-view" title="View record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('chemicals.edit', $chem) }}" class="act-btn act-edit" title="Edit record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('chemicals.destroy', $chem) }}" style="display:contents" onsubmit="event.preventDefault();confirmDelete('Delete this chemical record? This cannot be undone.',this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete record">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="12">No chemical records yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-4">{{ $chemicals->links() }}</div>
@endsection
