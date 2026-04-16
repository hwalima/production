@extends('layouts.app')
@section('page-title', 'Action Items')
@section('content')

@php
$priorityBg    = ['high'=>'#ef4444','medium'=>'#f97316','low'=>'#eab308'];
$priorityLight = ['high'=>'rgba(239,68,68,.12)','medium'=>'rgba(249,115,22,.12)','low'=>'rgba(234,179,8,.12)'];
$statusColor   = ['not_started'=>'#9ca3af','in_progress'=>'#3b82f6','pending'=>'#f97316','completed'=>'#22c55e'];
$statusLabel   = ['not_started'=>'Not Started','in_progress'=>'In Progress','pending'=>'Pending','completed'=>'Completed'];
@endphp

<div class="page-header">
    <h1 class="page-title">Action Items</h1>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if($overdueCount > 0)
        <span style="background:rgba(239,68,68,.12);color:#ef4444;border:1px solid rgba(239,68,68,.3);
                     border-radius:8px;padding:5px 12px;font-size:.75rem;font-weight:700;">
            &#9888; {{ $overdueCount }} overdue
        </span>
        @endif
        @if(auth()->user()->canWrite())
        <a href="{{ route('action-items.create') }}" class="btn-add">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item
        </a>
        @endif
        <a href="{{ route('action-items.pdf', ['from'=>$filterFrom,'to'=>$filterTo]) }}"
           target="_blank"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#1e3a5f;color:#fff;
                  border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none;">
            &#128196; Export PDF
        </a>
    </div>
</div>

@include('partials.date-filter', ['routeName' => 'action-items.index'])

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid #22c55e;color:#22c55e;border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:.82rem;">
    {{ session('success') }}
</div>
@endif

@if($departments->isEmpty())
<div style="padding:32px;text-align:center;color:#6b7280;font-size:.85rem;background:var(--card);border-radius:12px;">
    No active departments configured.
    <a href="{{ route('mining-departments.index') }}" style="color:#fcb913;">Go to Settings → Departments</a>
</div>
@else

{{-- ══════ REPORT TABLE (matches image layout) ══════ --}}
<div class="data-card" style="overflow:hidden;padding:0;">
    <table style="width:100%;border-collapse:collapse;">
        <colgroup>
            <col style="width:auto;">
            <col style="width:110px;">
            <col style="width:120px;">
            <col style="width:110px;">
            @if(auth()->user()->canWrite())<col style="width:80px;">@endif
        </colgroup>

        @forelse($departments as $dept)
        @php $deptItems = $items[$dept->id] ?? collect(); @endphp

        {{-- Department header row (green like the image) --}}
        <thead>
            <tr style="background:#2d6a2d;">
                <th style="padding:7px 14px;text-align:left;font-size:.78rem;font-weight:700;color:#fff;letter-spacing:.04em;">
                    {{ $dept->name }} Comments
                </th>
                <th style="padding:7px 14px;text-align:center;font-size:.72rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;">Priority</th>
                <th style="padding:7px 14px;text-align:center;font-size:.72rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;">Status</th>
                <th style="padding:7px 14px;text-align:center;font-size:.72rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;">Due</th>
                @if(auth()->user()->canWrite())
                <th style="padding:7px 14px;"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($deptItems as $item)
            @php
                $overdue  = $item->isOverdue();
                $rowBg    = $loop->even ? 'rgba(255,255,255,.02)' : 'transparent';
                $pBg      = $priorityLight[$item->priority] ?? 'transparent';
                $pColor   = $priorityBg[$item->priority] ?? '#9ca3af';
                $sColor   = $overdue ? '#ef4444' : ($statusColor[$item->status] ?? '#9ca3af');
                $sLabel   = $overdue ? 'Over Due' : ($statusLabel[$item->status] ?? $item->status);
                $dueLabel = $item->dueLabel();
                $dueColor = ($dueLabel === 'Over Due') ? '#ef4444' : '#9ca3af';
            @endphp
            <tr style="background:{{ $rowBg }};border-bottom:1px solid rgba(255,255,255,.04);">
                <td style="padding:9px 14px;font-size:.82rem;color:var(--text);">{{ $item->comment }}</td>
                <td style="padding:9px 10px;text-align:center;">
                    <span style="display:inline-block;padding:3px 10px;border-radius:4px;
                                 background:{{ $pBg }};color:{{ $pColor }};
                                 font-size:.72rem;font-weight:700;border:1px solid {{ $pColor }}33;">
                        {{ \App\Models\ActionItem::priorityLabel($item->priority) }}
                    </span>
                </td>
                <td style="padding:9px 10px;text-align:center;font-size:.75rem;font-weight:600;color:{{ $sColor }};">
                    {{ $sLabel }}
                </td>
                <td style="padding:9px 10px;text-align:center;font-size:.75rem;font-weight:600;color:{{ $dueColor }};">
                    {{ $dueLabel }}
                </td>
                @if(auth()->user()->canWrite())
                <td style="padding:9px 8px;text-align:center;">
                    <div class="act-group" style="justify-content:center;">
                        <a href="{{ route('action-items.edit', $item) }}" class="act-btn act-edit" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('action-items.destroy', $item) }}" style="display:contents"
                              onsubmit="event.preventDefault();confirmDelete('Delete this action item?',this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
                @endif
            </tr>
            @empty
            <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                <td colspan="{{ auth()->user()->canWrite() ? 5 : 4 }}"
                    style="padding:10px 14px;font-size:.78rem;color:#6b7280;font-style:italic;">
                    No items for this period.
                    @if(auth()->user()->canWrite())
                    <a href="{{ route('action-items.create') }}" style="color:#fcb913;">Add one →</a>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>

        @empty
        {{-- no departments --}}
        @endforelse

    </table>
</div>

@php
$allItems = $items->flatten();
$hasItems = $allItems->isNotEmpty();
@endphp

@if($hasItems)
<p style="font-size:.72rem;color:#6b7280;margin-top:12px;text-align:right;">
    NB: All outstanding issues need to be actioned to reduce the number of burning issues.
</p>
@endif

@endif
@endsection
