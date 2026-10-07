@extends('layouts.app')
@section('title', 'Register Machine')
@section('page-title', 'Machine Register')
@section('content')

<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('machines.index') }}" class="text-sm" style="color:#fcb913;">&larr; Back</a>
        <h1 class="text-2xl font-bold">Register Machine</h1>
    </div>

    <div class="rounded-xl shadow p-6" style="background:var(--card);">
        <form method="POST" action="{{ route('machines.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Machine Code <span style="color:#ef4444;">*</span></label>
                <input type="text" name="machine_code" value="{{ old('machine_code') }}" placeholder="e.g. MILL-01"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('machine_code') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                @error('machine_code')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description <span style="color:#ef4444;">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="e.g. Main Ball Mill"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('description') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                @error('description')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Service Interval (operating hours) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="service_interval_hours" value="{{ old('service_interval_hours') }}" min="1" step="1"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('service_interval_hours') border-red-400 @enderror"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                <p class="text-xs mt-1" style="color:#9ca3af;">Runtime hours are accumulated until a service record is added.</p>
                @error('service_interval_hours')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">Register Machine</button>
                <a href="{{ route('machines.index') }}" class="px-5 py-2 rounded-lg font-semibold text-sm" style="background:var(--input-bg);color:var(--text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
