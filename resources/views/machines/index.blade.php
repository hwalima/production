@extends('layouts.app')
@section('title', 'Machines')
@section('page-title', 'Machine Register')
@section('content')
@push('styles')
<style>
    @media (max-width: 640px) {
        .machine-register-table,
        .machine-register-table tbody,
        .machine-register-table tr,
        .machine-register-table td {
            display: block;
            width: 100%;
            min-width: 0;
        }
        .machine-register-table { min-width: 0; }
        .machine-register-table thead { display: none; }
        .machine-register-table tbody { padding: 10px; }
        .machine-register-table tbody tr {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 12px;
            padding: 8px 12px;
            margin-bottom: 10px;
            border: 1px solid var(--topbar-border);
            border-radius: 12px;
            background: var(--card);
        }
        .machine-register-table tbody td {
            display: flex;
            width: auto;
            min-width: 0;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            padding: 9px 0;
            white-space: normal;
            text-align: right;
            border-bottom: 1px solid var(--topbar-border);
        }
        .machine-register-table tbody td::before {
            content: attr(data-label);
            flex: 0 0 auto;
            color: #9ca3af;
            font-size: .68rem;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
        }
        .machine-register-table tbody td:nth-child(1),
        .machine-register-table tbody td:nth-child(2),
        .machine-register-table tbody td:nth-child(6),
        .machine-register-table tbody td:nth-child(7) { grid-column: 1 / -1; }
        .machine-register-table tbody td:last-child { border-bottom: 0; }
        .machine-register-table tbody td[data-label="Actions"] { justify-content: flex-end; }
        .machine-register-table tbody td[data-label="Actions"]::before { margin-right: auto; }
    }
</style>
@endpush

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
            <table class="data-table machine-register-table">
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
                            <td class="px-4 py-3 font-mono font-semibold" data-label="Code">{{ $machine->machine_code }}</td>
                            <td class="px-4 py-3" data-label="Description">{{ $machine->description }}</td>
                            <td class="px-4 py-3 td-r" data-label="Service Interval">{{ number_format($machine->service_interval_hours) }} h</td>
                            <td class="px-4 py-3 td-r" data-label="Hours Since Service">{{ number_format($hoursSinceService, 2) }} h</td>
                            <td class="px-4 py-3 td-r" data-label="Hours Remaining">{{ number_format(max(0, $machine->service_interval_hours - $hoursSinceService), 2) }} h</td>
                            <td class="px-4 py-3" data-label="Status">
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
                            <td class="px-4 py-3 text-center" data-label="Actions">
                                <div class="act-group">
                                    <a href="{{ route('machines.show', $machine) }}" class="act-btn act-view" title="View machine and runtimes">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    @if($canManageMachines)
                                        <a href="{{ route('machines.edit', $machine) }}" class="act-btn act-edit" title="Edit machine">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        @if($machine->runtimes_count === 0 && $machine->services_count === 0)
                                            <form method="POST" action="{{ route('machines.destroy', $machine) }}" onsubmit="event.preventDefault();confirmDelete('Permanently delete this machine?',this)">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="act-btn act-delete" title="Delete machine">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                </button>
                                            </form>
                                        @endif
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
