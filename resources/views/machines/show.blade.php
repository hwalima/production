@extends('layouts.app')
@section('title', 'Machine — ' . $machine->machine_code)
@section('page-title', 'Machine Runtimes')
@section('content')

@php
    $today = \Carbon\Carbon::today();
    $hours = $machine->start_time && $machine->end_time
        ? round($machine->start_time->diffInMinutes($machine->end_time) / 60, 1) : 0;
    $overdue = $machine->next_service_date && $machine->next_service_date->lt($today);
    $dueSoon = !$overdue && $machine->next_service_date && $machine->next_service_date->diffInDays($today) <= 7;
@endphp

<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('machines.index') }}" class="text-sm" style="color:#fcc104;">&larr; Back</a>
            <h1 class="text-2xl font-bold">{{ $machine->machine_code }}</h1>
            @if($overdue)
                <span class="px-2 py-1 rounded-full text-xs font-semibold" style="background:#fee2e2;color:#991b1b;">Overdue</span>
            @elseif($dueSoon)
                <span class="px-2 py-1 rounded-full text-xs font-semibold" style="background:#fef3c7;color:#92400e;">Service Due Soon</span>
            @else
                <span class="px-2 py-1 rounded-full text-xs font-semibold" style="background:#dcfce7;color:#166534;">OK</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('machines.edit', $machine) }}"
               class="px-4 py-2 rounded-lg font-semibold text-sm"
               style="background:#fcb913;color:#001a4d;">Edit</a>
            <form method="POST" action="{{ route('machines.destroy', $machine) }}" onsubmit="event.preventDefault();confirmDelete('Delete this machine? This cannot be undone.',this)">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg font-semibold text-sm" style="background:#fee2e2;color:#991b1b;">Delete</button>
            </form>
        </div>
    </div>

    <div class="rounded-xl shadow p-6" style="background:var(--card);">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <dt class="font-medium" style="color:#9ca3af;">Machine Code</dt>
                <dd class="mt-1 font-mono font-semibold text-base">{{ $machine->machine_code }}</dd>
            </div>
            <div>
                <dt class="font-medium" style="color:#9ca3af;">Description</dt>
                <dd class="mt-1">{{ $machine->description ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium" style="color:#9ca3af;">Start Time</dt>
                <dd class="mt-1">{{ $machine->start_time?->format('Y-m-d H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium" style="color:#9ca3af;">End Time</dt>
                <dd class="mt-1">{{ $machine->end_time?->format('Y-m-d H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium" style="color:#9ca3af;">Hours Run</dt>
                <dd class="mt-1 text-2xl font-bold" style="color:#fcc104;">{{ $hours }} h</dd>
            </div>
            <div>
                <dt class="font-medium" style="color:#9ca3af;">Service Interval</dt>
                <dd class="mt-1">Every {{ $machine->service_after_hours }} days</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="font-medium" style="color:#9ca3af;">Next Service Date</dt>
                <dd class="mt-1 font-semibold {{ $overdue ? 'text-red-600' : ($dueSoon ? 'text-yellow-600' : '') }}">
                    {{ $machine->next_service_date?->format('D, d M Y') ?? '—' }}
                    @if($overdue)
                        — <span style="color:#ef4444;">{{ $machine->next_service_date->diffInDays($today) }} day(s) overdue</span>
                    @elseif($dueSoon)
                        — <span style="color:#d97706;">due in {{ $today->diffInDays($machine->next_service_date) }} day(s)</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <p class="text-xs" style="color:#9ca3af;">
        Record created: {{ $machine->created_at->format('d M Y H:i') }} &nbsp;|&nbsp;
        Last updated: {{ $machine->updated_at->format('d M Y H:i') }}
    </p>
</div>
@endsection
