@extends('layouts.app')
@section('page-title', 'Drilling')
@section('content')

<div class="page-header">
    <h1 class="page-title">Drilling Records</h1>
    <a href="{{ route('drilling.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Record
    </a>
</div>

{{-- Date filter --}}
@include('partials.date-filter', ['routeName' => 'drilling.index'])

<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>End / Level</th>
                <th class="th-r">Hole Count</th>
                <th class="th-r">Steel Length (m)</th>
                <th class="th-r">Advance (m)</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $rec)
            <tr>
                <td><span style="font-weight:600;">{{ $rec->date->format('d M Y') }}</span></td>
                <td>{{ $rec->end_name }}</td>
                <td class="td-r">{{ $rec->hole_count }}</td>
                <td class="td-r">{{ $rec->drill_steel_length }}</td>
                <td class="td-r">{{ $rec->advance }}</td>
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('drilling.show', $rec) }}" class="act-btn act-view" title="View record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('drilling.edit', $rec) }}" class="act-btn act-edit" title="Edit record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('drilling.destroy', $rec) }}" style="display:contents" onsubmit="return confirm('Delete this record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete record">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="6">No drilling records yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-4">{{ $records->links() }}</div>
@endsection
