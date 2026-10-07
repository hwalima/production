@extends('layouts.app')
@section('title', 'Record Runtime')
@section('page-title', 'Machine Runtime')
@section('content')

<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('machines.show', $machine) }}" class="text-sm" style="color:#fcb913;">&larr; Back to {{ $machine->machine_code }}</a>
        <h1 class="text-2xl font-bold">Record Runtime</h1>
    </div>
    <div class="rounded-xl shadow p-6" style="background:var(--card);">
        <div class="mb-5">
            <div class="font-semibold">{{ $machine->machine_code }} — {{ $machine->description }}</div>
            <div class="text-sm mt-1" style="color:#9ca3af;">Service interval: {{ number_format($machine->service_interval_hours) }} operating hours</div>
        </div>
        <form method="POST" action="{{ route('machines.runtimes.store', $machine) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Start Time <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('start_time') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                @error('start_time')<p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">End Time <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('end_time') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                @error('end_time')<p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
            </div>
            <p class="text-xs" style="color:#9ca3af;">Operating hours are calculated from the start and end times.</p>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">Save Runtime</button>
                <a href="{{ route('machines.show', $machine) }}" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:var(--input-bg);color:var(--text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
