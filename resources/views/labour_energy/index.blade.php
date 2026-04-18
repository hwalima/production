@extends('layouts.app')
@section('page-title', 'Labour & Energy')
@section('content')

<div class="page-header">
    <h1 class="page-title">Labour &amp; Energy Records</h1>
    @if(auth()->user()->canWrite())
    <div style="display:flex;align-items:center;gap:8px;">
    <a href="{{ route('import.labour-energy') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:.8rem;font-weight:700;border-radius:10px;border:1px solid var(--topbar-border);background:var(--card);color:var(--text);text-decoration:none;transition:background .15s,color .15s,border-color .15s;"
       onmouseover="this.style.background='#fcb913';this.style.color='#001a4d';this.style.borderColor='#fcb913'"
       onmouseout="this.style.background='';this.style.color='';this.style.borderColor=''">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Import
    </a>
    <a href="{{ route('labour-energy.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Record
    </a>
    </div>
    @endif
</div>

<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th class="th-r">ZESA Cost ({{ $currencySymbol }})</th>
                <th class="th-r">Diesel Cost ({{ $currencySymbol }})</th>
                <th class="th-r">Labour Cost ({{ $currencySymbol }})</th>
                <th class="th-r">Total ({{ $currencySymbol }})</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $rec)
            @php $total = ($rec->zesa_cost ?? 0) + ($rec->diesel_cost ?? 0) + ($rec->labour_cost ?? 0); @endphp
            <tr>
                <td data-sort="{{ $rec->date->format('Y-m-d') }}"><span style="font-weight:600;">{{ $rec->date->format('d M Y') }}</span></td>
                <td class="td-r">{{ $currencySymbol }}{{ number_format($rec->zesa_cost, 2) }}</td>
                <td class="td-r">{{ $currencySymbol }}{{ number_format($rec->diesel_cost, 2) }}</td>
                <td class="td-r">{{ $currencySymbol }}{{ number_format($rec->labour_cost, 2) }}
                    @if($rec->deptCosts->isNotEmpty())
                    <div style="margin-top:4px;font-size:0.72rem;color:#9ca3af;line-height:1.5;">
                        @foreach($rec->deptCosts as $dc)
                        <span style="display:block;">{{ $dc->department->name ?? '—' }}: {{ $currencySymbol }}{{ number_format($dc->labour_cost, 2) }}</span>
                        @endforeach
                    </div>
                    @endif
                </td>
                <td class="td-r" style="font-weight:600;color:#fcc104;">{{ $currencySymbol }}{{ number_format($total, 2) }}</td>
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('labour-energy.show', $rec) }}" class="act-btn act-view" title="View record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        @if(auth()->user()->canWrite())
                        <a href="{{ route('labour-energy.edit', $rec) }}" class="act-btn act-edit" title="Edit record">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('labour-energy.destroy', $rec) }}" style="display:contents" onsubmit="event.preventDefault();confirmDelete('Delete this labour &amp; energy record? This cannot be undone.',this)">
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
            <tr class="empty-row"><td colspan="6">No labour &amp; energy records yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-4">{{ $records->links() }}</div>
@endsection
