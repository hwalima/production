@extends('layouts.app')
@section('title', 'Edit Runtime')
@section('page-title', 'Machine Runtime')
@section('content')

<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('machines.show', $machineRuntime->machine_id) }}" class="text-sm" style="color:#fcb913;">&larr; Back to {{ $machineRuntime->machine_code }}</a>
        <h1 class="text-2xl font-bold">Edit Runtime</h1>
    </div>
    <div class="rounded-xl shadow p-6" style="background:var(--card);">
        <form method="POST" action="{{ route('machine-runtimes.update', $machineRuntime) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="font-semibold">{{ $machineRuntime->machine_code }} — {{ $machineRuntime->description }}</div>
            <div>
                <label class="block text-sm font-medium mb-1">Start Time <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time', $machineRuntime->start_time->format('Y-m-d\TH:i')) }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('start_time') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                @error('start_time')<p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">End Time <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="end_time" value="{{ old('end_time', $machineRuntime->end_time->format('Y-m-d\TH:i')) }}" required
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('end_time') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                @error('end_time')<p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">Save Changes</button>
                <a href="{{ route('machines.show', $machineRuntime->machine_id) }}" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:var(--input-bg);color:var(--text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
