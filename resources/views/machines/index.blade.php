@extends('layouts.app')
@section('title', 'Machines')
@section('page-title', 'Machine Register')
@section('content')

<div class="space-y-5">
    <div class="page-header">
        <div>
            <h1 class="page-title">Machine Register</h1>
            <p class="text-sm" style="color:#9ca3af;">Register equipment once, then record each runtime against it.</p>
        </div>
        @if($canManageMachines)
            <a href="{{ route('machines.create') }}" class="btn-add">Register Machine</a>
        @endif
    </div>

    <div class="data-card">
        <div class="tbl-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="th-r">Service Interval</th>
                        <th class="th-r">Hours Since Service</th>
                        <th class="th-r">Hours Remaining</th>
                        <th>Status</th>
                        <th class="th-c">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($machines as $machine)
                        @php
                            $hoursSinceService = $machine->hoursSinceLastService();
                            $serviceDue = $machine->isServiceDue($hoursSinceService);
                            $dueSoon = !$serviceDue && $machine->isServiceDueSoon($hoursSinceService);
                        @endphp
                        <tr class="border-t" style="border-color:var(--topbar-border);">
                            <td class="px-4 py-3 font-mono font-semibold">{{ $machine->machine_code }}</td>
                            <td class="px-4 py-3">{{ $machine->description }}</td>
                            <td class="px-4 py-3 td-r">{{ number_format($machine->service_interval_hours) }} h</td>
                            <td class="px-4 py-3 td-r">{{ number_format($hoursSinceService, 2) }} h</td>
                            <td class="px-4 py-3 td-r">{{ number_format(max(0, $machine->service_interval_hours - $hoursSinceService), 2) }} h</td>
                            <td class="px-4 py-3">
                                @if(!$machine->is_active)
                                    <span class="text-xs font-semibold" style="color:#6b7280;">Inactive</span>
                                @elseif(!$machine->latestRuntime)
                                    <span class="text-xs font-semibold" style="color:#6b7280;">Awaiting Runtime</span>
                                @elseif($serviceDue)
                                    <span class="text-xs font-semibold" style="color:#ef4444;">Service Due</span>
                                @elseif($dueSoon)
                                    <span class="text-xs font-semibold" style="color:#d97706;">Due Soon</span>
                                @else
                                    <span class="text-xs font-semibold" style="color:#16a34a;">OK</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="act-group">
                                    <a href="{{ route('machines.show', $machine) }}" class="act-btn act-view" title="View machine and runtimes">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    @if($canManageMachines)
                                        <a href="{{ route('machines.edit', $machine) }}" class="act-btn act-edit" title="Edit machine">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">No machines registered yet. Register a machine to start recording runtimes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $machines->links() }}</div>
</div>
@endsection
