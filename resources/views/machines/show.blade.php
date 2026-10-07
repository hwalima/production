@extends('layouts.app')
@section('title', 'Machine — ' . $machine->machine_code)
@section('page-title', 'Machine Register')
@section('content')

@php
    $hoursSinceService = $machine->hoursSinceLastService();
    $serviceDue = $machine->isServiceDue($hoursSinceService);
    $dueSoon = !$serviceDue && $machine->isServiceDueSoon($hoursSinceService);
@endphp

<div class="space-y-5">
    <div class="page-header">
        <div class="flex items-center gap-3">
            <a href="{{ route('machines.index') }}" class="text-sm" style="color:#fcb913;">&larr; Machines</a>
            <div>
                <h1 class="page-title">{{ $machine->machine_code }}</h1>
                <p class="text-sm" style="color:#9ca3af;">{{ $machine->description }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($canManageMachines && $machine->is_active)
                <a href="{{ route('machines.runtimes.create', $machine) }}" class="btn-add">Record Runtime</a>
            @endif
            @if($canManageMachines)
                <a href="{{ route('machines.edit', $machine) }}" class="px-4 py-2 rounded-lg font-semibold text-sm" style="background:var(--input-bg);color:var(--text);">Edit Machine</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl shadow p-5" style="background:var(--card);">
            <div class="text-xs uppercase" style="color:#9ca3af;">Service Interval</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($machine->service_interval_hours) }} h</div>
        </div>
        <div class="rounded-xl shadow p-5" style="background:var(--card);">
            <div class="text-xs uppercase" style="color:#9ca3af;">Operating Hours Since Service</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($hoursSinceService, 2) }} h</div>
        </div>
        <div class="rounded-xl shadow p-5" style="background:var(--card);">
            <div class="text-xs uppercase" style="color:#9ca3af;">Service Status</div>
            @if(!$machine->is_active)
                <div class="text-2xl font-bold mt-1" style="color:#6b7280;">Inactive</div>
            @elseif(!$machine->latestRuntime)
                <div class="text-2xl font-bold mt-1" style="color:#6b7280;">Awaiting Runtime</div>
            @elseif($serviceDue)
                <div class="text-2xl font-bold mt-1" style="color:#ef4444;">Due</div>
            @elseif($dueSoon)
                <div class="text-2xl font-bold mt-1" style="color:#d97706;">Due Soon</div>
            @else
                <div class="text-2xl font-bold mt-1" style="color:#16a34a;">{{ number_format($machine->serviceHoursRemaining(), 2) }} h left</div>
            @endif
        </div>
    </div>
    <p class="text-xs" style="color:#9ca3af;">
        @if($machine->latestService)
            Counter starts from the latest recorded service on {{ $machine->latestService->serviced_at->format('d M Y H:i') }}.
        @elseif($machine->service_tracking_started_at)
            Counter starts from {{ $machine->service_tracking_started_at->format('d M Y H:i') }}, when existing runtime history was migrated. Historical service events were not available.
        @else
            Counter starts with the first runtime. No service event has been recorded yet.
        @endif
    </p>

    @if($canManageMachines && $machine->is_active)
        <div class="rounded-xl shadow p-6" style="background:var(--card);">
            <h2 class="text-lg font-semibold mb-3">Record Completed Service</h2>
            <form method="POST" action="{{ route('machines.services.store', $machine) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Service Date and Time</label>
                    <input type="datetime-local" name="serviced_at" value="{{ old('serviced_at', now()->format('Y-m-d\TH:i')) }}" required
                           class="w-full border rounded-lg px-3 py-2 text-sm" style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('serviced_at')<p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Notes (optional)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" maxlength="2000" placeholder="Service work completed"
                           class="w-full border rounded-lg px-3 py-2 text-sm" style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('notes')<p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">Record Service</button>
            </form>
            <p class="text-xs mt-2" style="color:#9ca3af;">The operating-hour counter restarts from this service date and time.</p>
        </div>
    @endif

    <div class="data-card">
        <div class="px-4 py-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Runtime History</h2>
            <span class="text-sm" style="color:#9ca3af;">{{ $machine->runtimes()->count() }} record(s)</span>
        </div>
        <div class="tbl-scroll">
            <table class="data-table">
                <thead><tr><th>Start</th><th>End</th><th class="th-r">Hours Run</th>@if($canManageMachines)<th class="th-c">Actions</th>@endif</tr></thead>
                <tbody>
                    @forelse($runtimes as $runtime)
                        <tr class="border-t" style="border-color:var(--topbar-border);">
                            <td class="px-4 py-3">{{ $runtime->start_time->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $runtime->end_time->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 td-r font-semibold">{{ number_format($runtime->hours_run, 2) }} h</td>
                            @if($canManageMachines)
                                <td class="px-4 py-3 text-center">
                                    <div class="act-group">
                                        <a href="{{ route('machine-runtimes.edit', $runtime) }}" class="act-btn act-edit" title="Edit runtime">Edit</a>
                                        <form method="POST" action="{{ route('machine-runtimes.destroy', $runtime) }}" onsubmit="event.preventDefault();confirmDelete('Delete this runtime record?',this)">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="act-btn act-delete" title="Delete runtime">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canManageMachines ? 4 : 3 }}" class="px-4 py-8 text-center text-gray-400">No runtime records for this machine yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $runtimes->links() }}</div>
    </div>

    <div class="data-card">
        <div class="px-4 py-3"><h2 class="text-lg font-semibold">Service History</h2></div>
        <div class="tbl-scroll">
            <table class="data-table">
                <thead><tr><th>Serviced At</th><th>Notes</th></tr></thead>
                <tbody>
                    @forelse($services as $service)
                        <tr class="border-t" style="border-color:var(--topbar-border);">
                            <td class="px-4 py-3">{{ $service->serviced_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $service->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-6 text-center text-gray-400">No service events recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
