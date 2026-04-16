@extends('pdf.layout')

@section('report-title', 'Action Items Report')
@section('report-subtitle', 'Period: ' . \Carbon\Carbon::parse($filterFrom)->format('d M Y') . ' – ' . \Carbon\Carbon::parse($filterTo)->format('d M Y'))

@section('content')

@php
$priorityLabel = ['high'=>'High','medium'=>'Medium','low'=>'Low'];
$priorityColor = ['high'=>'#b91c1c','medium'=>'#c2410c','low'=>'#854d0e'];
$statusLabel   = ['not_started'=>'Not Started','in_progress'=>'In Progress','pending'=>'Pending','completed'=>'Completed'];
$statusColor   = ['not_started'=>'#6b7280','in_progress'=>'#1d4ed8','pending'=>'#c2410c','completed'=>'#15803d'];

$totalItems    = 0;
$totalOverdue  = 0;
$totalDone     = 0;
foreach($departments as $dept) {
    $deptItems = $items->get($dept->id, collect());
    $totalItems += $deptItems->count();
    foreach($deptItems as $ai) {
        if($ai->status === 'completed') $totalDone++;
        if($ai->isOverdue()) $totalOverdue++;
    }
}
@endphp

{{-- ── Summary tiles ── --}}
<table class="summary-grid" style="width:80%;">
    <tr>
        <td>
            <div class="tile-label">Total Items</div>
            <div class="tile-value">{{ $totalItems }}</div>
        </td>
        <td>
            <div class="tile-label">Overdue</div>
            <div class="tile-value {{ $totalOverdue ? '' : 'green' }}" style="{{ $totalOverdue ? 'color:#b91c1c;' : '' }}">
                {{ $totalOverdue }}
            </div>
        </td>
        <td>
            <div class="tile-label">Completed</div>
            <div class="tile-value green">{{ $totalDone }}</div>
        </td>
    </tr>
</table>

{{-- ── Department sections ── --}}
@foreach($departments as $dept)
@php $deptItems = $items->get($dept->id, collect()); @endphp

<div class="section-heading">{{ strtoupper($dept->name) }}</div>
<table class="data-table" style="margin-bottom:14px;">
    <thead>
        <tr>
            <th>Comment / Issue</th>
            <th class="th-c" style="width:65px;">Priority</th>
            <th class="th-c" style="width:75px;">Status</th>
            <th class="th-c" style="width:70px;">Reported</th>
            <th class="th-c" style="width:70px;">Due</th>
        </tr>
    </thead>
    <tbody>
        @forelse($deptItems as $ai)
        @php
            $overdue = $ai->isOverdue();
            $pColor  = $priorityColor[$ai->priority] ?? '#374151';
            $sColor  = $overdue ? '#b91c1c' : ($statusColor[$ai->status] ?? '#6b7280');
            $sText   = $overdue ? 'Over Due' : ($statusLabel[$ai->status] ?? $ai->status);
        @endphp
        <tr>
            <td>{{ $ai->comment }}</td>
            <td class="td-c" style="color:{{ $pColor }};font-weight:700;">
                {{ $priorityLabel[$ai->priority] ?? $ai->priority }}
            </td>
            <td class="td-c" style="color:{{ $sColor }};font-weight:{{ $overdue ? '700' : '400' }};">
                {{ $sText }}
            </td>
            <td class="td-c">{{ $ai->reported_date->format('d M Y') }}</td>
            <td class="td-c">{{ $ai->due_date ? $ai->due_date->format('d M Y') : '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center;color:#9ca3af;font-style:italic;">
                No items for this period
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endforeach

<p style="font-size:7.5px;color:#6b7280;margin-top:18px;border-top:1px solid #e5e7eb;padding-top:6px;">
    <strong>NB:</strong> All outstanding issues need to be actioned before the next weekly meeting.
</p>

@endsection
