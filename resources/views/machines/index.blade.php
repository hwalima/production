@extends('layouts.app')
@section('title', 'Machines')
@section('page-title', 'Machine Runtimes')
@section('content')

@php $today = \Carbon\Carbon::today(); @endphp

<div class="space-y-5">
    <div class="page-header">
        <h1 class="page-title">Machine Runtimes</h1>
        <a href="{{ route('machines.create') }}" class="btn-add">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Record
        </a>
    </div>

    @include('partials.date-filter', ['routeName' => 'machines.index'])

    {{-- Overdue alert --}}
    @php
        $overdueCount = $machines->getCollection()->filter(fn($m) => $m->next_service_date && $m->next_service_date->lt($today))->count();
    @endphp
    @if($overdueCount > 0)
        <div class="p-3 rounded-lg text-sm font-medium flex items-center gap-2"
             style="background:#fef3c7;border:1px solid #fcd34d;color:#92400e;">
            ⚠️ {{ $overdueCount }} machine{{ $overdueCount > 1 ? 's' : '' }} overdue for service.
        </div>
    @endif

    <div class="data-card">
        <div class="tbl-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th class="th-r">Hours Run</th>
                    <th>Next Service</th>
                    <th>Status</th>
                    <th class="th-c">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($machines as $m)
                    @php
                        $hours = $m->start_time && $m->end_time
                            ? round($m->start_time->diffInMinutes($m->end_time) / 60, 1)
                            : 0;
                        $overdue = $m->next_service_date && $m->next_service_date->lt($today);
                        $dueSoon = !$overdue && $m->next_service_date && $m->next_service_date->diffInDays($today) <= 7;
                    @endphp
                    <tr class="border-t" style="border-color:var(--topbar-border);">
                        <td class="px-4 py-3" style="font-family:monospace;font-weight:600;">{{ $m->machine_code }}</td>
                        <td class="px-4 py-3">{{ $m->description }}</td>
                        <td class="px-4 py-3 td-muted" data-sort="{{ $m->start_time?->format('Y-m-d H:i:s') ?? '0000-00-00' }}" style="font-size:.8rem;">{{ $m->start_time?->format('d M Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 td-muted" data-sort="{{ $m->end_time?->format('Y-m-d H:i:s') ?? '0000-00-00' }}" style="font-size:.8rem;">{{ $m->end_time?->format('d M Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 td-r" style="font-weight:600;">{{ $hours }} h</td>
                        <td class="px-4 py-3 td-muted" data-sort="{{ $m->next_service_date?->format('Y-m-d') ?? '0000-00-00' }}" style="font-size:.8rem;">{{ $m->next_service_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($overdue)
                                <span style="display:inline-block;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;background:#fee2e2;color:#991b1b;">Overdue</span>
                            @elseif($dueSoon)
                                <span style="display:inline-block;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;background:#fef3c7;color:#92400e;">Due Soon</span>
                            @else
                                <span style="display:inline-block;padding:2px 10px;border-radius:99px;font-size:.75rem;font-weight:700;background:#dcfce7;color:#166534;">OK</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="act-group">
                                <a href="{{ route('machines.show', $m) }}" class="act-btn act-view" title="View record">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('machines.edit', $m) }}" class="act-btn act-edit" title="Edit record">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('machines.destroy', $m) }}" style="display:contents" onsubmit="event.preventDefault();confirmDelete('Delete this machine? This cannot be undone.',this)">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn act-delete" title="Delete record">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">No machine runtime records found. <a href="{{ route('machines.create') }}" style="color:#fcc104;">Add one →</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div>{{ $machines->links() }}</div>
</div>
@endsection
