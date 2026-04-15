@extends('layouts.app')
@section('title', 'Edit Machine Runtime')
@section('page-title', 'Machine Runtimes')
@section('content')

<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('machines.index') }}" class="text-sm" style="color:#fcb913;">&larr; Back</a>
        <h1 class="text-2xl font-bold">Edit — {{ $machine->machine_code }}</h1>
    </div>

    <div class="rounded-xl shadow p-6" style="background:var(--card);">
        <form method="POST" action="{{ route('machines.update', $machine) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Machine Code <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="machine_code" value="{{ old('machine_code', $machine->machine_code) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm @error('machine_code') border-red-400 @enderror"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('machine_code')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description', $machine->description) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('description')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Start Time <span style="color:#ef4444;">*</span></label>
                    <input type="datetime-local" name="start_time"
                           value="{{ old('start_time', $machine->start_time?->format('Y-m-d\TH:i')) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm @error('start_time') border-red-400 @enderror"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('start_time')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">End Time <span style="color:#ef4444;">*</span></label>
                    <input type="datetime-local" name="end_time"
                           value="{{ old('end_time', $machine->end_time?->format('Y-m-d\TH:i')) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm @error('end_time') border-red-400 @enderror"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('end_time')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="max-w-xs">
                <label class="block text-sm font-medium mb-1">Service Interval (days) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="service_after_hours"
                       value="{{ old('service_after_hours', $machine->service_after_hours) }}"
                       min="1" step="1"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('service_after_hours') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                <p class="text-xs mt-1" style="color:#9ca3af;">Next service = end time + this many days</p>
                @error('service_after_hours')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">
                    Update Record
                </button>
                <a href="{{ route('machines.show', $machine) }}" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:var(--input-bg);color:var(--text);">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
