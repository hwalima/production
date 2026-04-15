@extends('layouts.app')
@section('title', 'Assay Results')
@section('page-title', 'Assay Results')
@section('content')

<div class="page-header">
    <h1 class="page-title">Assay Results</h1>
    @can('create', App\Models\AssayResult::class)
    @endcan
    @if(auth()->user()->canWrite())
    <a href="{{ route('assay.create') }}" class="btn-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Result
    </a>
    @endif
</div>

@php
$tabs = [
    'fire'   => ['label' => 'Fire Assay',       'records' => $fire,   'color' => '#ef4444'],
    'goc'    => ['label' => 'Gold on Carbon',    'records' => $goc,    'color' => '#fcb913'],
    'bottle' => ['label' => 'Bottle Roll',       'records' => $bottle, 'color' => '#3b82f6'],
];
$activeTab = request('tab', 'fire');
if (!array_key_exists($activeTab, $tabs)) $activeTab = 'fire';
@endphp

{{-- Tab bar --}}
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid rgba(255,255,255,.08);padding-bottom:0;">
    @foreach($tabs as $key => $tab)
    <a href="{{ route('assay.index', ['tab' => $key] + request()->except('tab')) }}"
       style="padding:8px 18px;font-size:.82rem;font-weight:600;border-radius:6px 6px 0 0;text-decoration:none;
              {{ $activeTab === $key
                  ? 'background:'.$tab['color'].';color:#fff;'
                  : 'color:#9ca3af;background:transparent;' }}">
        {{ $tab['label'] }}
        <span style="font-size:.7rem;margin-left:5px;opacity:.8;">({{ $tabs[$key]['records']->total() }})</span>
    </a>
    @endforeach
</div>

@foreach($tabs as $key => $tab)
@if($activeTab === $key)
<div class="data-card">
    <div class="tbl-scroll">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="th-r" style="background:rgba(0,0,0,.18);">Assay Value (g/t)</th>
                <th class="th-c">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tab['records'] as $rec)
            <tr>
                <td><span style="font-weight:600;">{{ $rec->date->format('d M Y') }}</span></td>
                <td style="color:#9ca3af;font-size:.82rem;">{{ $rec->description ?: '—' }}</td>
                <td class="td-r">
                    <span style="font-weight:700;color:{{ $tab['color'] }};">
                        {{ number_format($rec->assay_value, 4) }}
                    </span>
                </td>
                <td class="td-c">
                    <div class="act-group">
                        <a href="{{ route('assay.show', $rec) }}" class="act-btn act-view" title="View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        @if(auth()->user()->canWrite())
                        <a href="{{ route('assay.edit', $rec) }}" class="act-btn act-edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('assay.destroy', $rec) }}" style="display:contents" onsubmit="event.preventDefault();confirmDelete('Delete this assay result? This cannot be undone.',this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="4">No {{ strtolower($tab['label']) }} results yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $tab['records']->appends(['tab' => $key])->links() }}</div>
@endif
@endforeach

@endsection
