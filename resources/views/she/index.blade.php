@extends('layouts.app')
@section('page-title', 'SHE')
@section('content')

<div class="page-header">
    <h1 class="page-title">SHE Indicators</h1>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if(auth()->user()->canWrite())
        <a href="{{ route('she.indicators.create') }}" class="btn-add">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Record
        </a>
        @endif
        <a href="{{ route('she.requirements.edit') }}"
           style="padding:7px 14px;font-size:.8rem;background:rgba(34,197,94,.1);color:#22c55e;border:1px solid rgba(34,197,94,.4);border-radius:8px;text-decoration:none;font-weight:600;">
            Requirements
        </a>
    </div>
</div>

@include('partials.date-filter', ['routeName' => 'she.index'])

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid #22c55e;color:#22c55e;border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:.82rem;">
    {{ session('success') }}
</div>
@endif

<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Department</th>
                <th class="th-r">Med. Injury</th>
                <th class="th-r">Fatal</th>
                <th class="th-r">LTI</th>
                <th class="th-r">NLTI</th>
                <th class="th-r">Leave</th>
                <th class="th-r">Offdays</th>
                <th class="th-r">Sick</th>
                <th class="th-r">IOD</th>
                <th class="th-r">AWOL</th>
                <th class="th-r">Terminations</th>
                @if(auth()->user()->canWrite())
                <th class="th-c">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($records as $rec)
            <tr>
                <td data-sort="{{ $rec->date->format('Y-m-d') }}">
                    <span style="font-weight:600;">{{ $rec->date->format('d M Y') }}</span>
                </td>
                <td>{{ $rec->department?->name ?? '—' }}</td>
                <td class="td-r">{{ $rec->medical_injury_case > 0 ? number_format($rec->medical_injury_case, 0) : '-' }}</td>
                <td class="td-r" style="{{ $rec->fatal_incident > 0 ? 'color:#ef4444;font-weight:700;' : '' }}">
                    {{ $rec->fatal_incident > 0 ? number_format($rec->fatal_incident, 0) : '-' }}
                </td>
                <td class="td-r" style="{{ $rec->lti > 0 ? 'color:#fcb913;font-weight:700;' : '' }}">
                    {{ $rec->lti > 0 ? number_format($rec->lti, 0) : '-' }}
                </td>
                <td class="td-r">{{ $rec->nlti > 0 ? number_format($rec->nlti, 0) : '-' }}</td>
                <td class="td-r">{{ $rec->leave > 0 ? number_format($rec->leave, 0) : '-' }}</td>
                <td class="td-r">{{ $rec->offdays > 0 ? number_format($rec->offdays, 0) : '-' }}</td>
                <td class="td-r">{{ $rec->sick > 0 ? number_format($rec->sick, 0) : '-' }}</td>
                <td class="td-r">{{ $rec->iod > 0 ? number_format($rec->iod, 0) : '-' }}</td>
                <td class="td-r">{{ $rec->awol > 0 ? number_format($rec->awol, 0) : '-' }}</td>
                <td class="td-r">{{ $rec->terminations > 0 ? number_format($rec->terminations, 0) : '-' }}</td>
                @if(auth()->user()->canWrite())
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('she.indicators.edit', $rec) }}" class="act-btn act-edit" title="Edit record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('she.indicators.destroy', $rec) }}" style="display:contents"
                              onsubmit="event.preventDefault();confirmDelete('Delete this SHE record? This cannot be undone.',this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete record">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
                @endif
            </tr>
            @empty
            <tr class="empty-row">
                <td colspan="{{ auth()->user()->canWrite() ? 13 : 12 }}">No SHE indicator records for this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-4">{{ $records->links() }}</div>
@endsection