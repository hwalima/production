@extends('layouts.app')
@section('title', 'Edit Machine')
@section('page-title', 'Machine Register')
@section('content')

<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('machines.show', $machine) }}" class="text-sm" style="color:#fcb913;">&larr; Back</a>
        <h1 class="text-2xl font-bold">Edit — {{ $machine->machine_code }}</h1>
    </div>
    <div class="rounded-xl shadow p-6" style="background:var(--card);">
        <form method="POST" action="{{ route('machines.update', $machine) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Machine Code <span style="color:#ef4444;">*</span></label>
                <input type="text" name="machine_code" value="{{ old('machine_code', $machine->machine_code) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('machine_code') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                @error('machine_code')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description <span style="color:#ef4444;">*</span></label>
                <input type="text" name="description" value="{{ old('description', $machine->description) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('description') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                @error('description')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Service Interval (operating hours) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="service_interval_hours" value="{{ old('service_interval_hours', $machine->service_interval_hours) }}" min="1" step="1"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('service_interval_hours') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                @error('service_interval_hours')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="is_active">Machine Status</label>
                <select id="is_active" name="is_active"
                        class="w-full border rounded-lg px-3 py-2 text-sm"
                        style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    <option value="1" @selected((string) old('is_active', (int) $machine->is_active) === '1')>Active — available for runtime records</option>
                    <option value="0" @selected((string) old('is_active', (int) $machine->is_active) === '0')>Inactive — retain history, prevent new runtime records</option>
                </select>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">Save Changes</button>
                <a href="{{ route('machines.show', $machine) }}" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:var(--input-bg);color:var(--text);">Cancel</a>
            </div>
        </form>
        <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t" style="border-color:var(--topbar-border);">
            @if($machine->runtimes_count === 0 && $machine->services_count === 0)
                <form method="POST" action="{{ route('machines.destroy', $machine) }}" onsubmit="event.preventDefault();confirmDelete('Permanently delete this machine?',this)">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:#fee2e2;color:#991b1b;">Delete Machine</button>
                </form>
            @else
                <span class="text-xs text-right" style="color:#9ca3af;">Delete unavailable while history exists. Set status to inactive to retire it.</span>
            @endif
        </div>
    </div>
</div>
@endsection
