@extends('layouts.app')
@section('title', 'Work Shifts')
@section('page-title', 'Settings')
@section('content')

<div style="max-width:720px;">

    <div class="page-header">
        <div>
            <h1 class="page-title">Work Shifts</h1>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:2px;">
                <a href="{{ route('settings.index') }}" style="color:#fcb913;">Settings</a>
                &rsaquo; Work Shifts
            </p>
        </div>
        <a href="{{ route('settings.index') }}" class="btn-cancel">&larr; Settings</a>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,.1);border:1px solid #22c55e;color:#22c55e;padding:10px 14px;border-radius:8px;font-size:.82rem;margin-bottom:16px;">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:rgba(239,68,68,.1);border:1px solid #ef4444;color:#ef4444;padding:10px 14px;border-radius:8px;font-size:.82rem;margin-bottom:16px;">
        {{ $errors->first() }}
    </div>
    @endif

    {{-- ── Add / Edit form ── --}}
    @isset($shift)
    {{-- Edit mode --}}
    <div class="form-card" style="margin-bottom:20px;">
        <p style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#fcb913;margin-bottom:12px;">● Editing Shift</p>
        <form method="POST" action="{{ route('shifts.update', $shift) }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            @csrf @method('PUT')
            <div style="flex:1;min-width:160px;">
                <label class="fc-label">Shift Name</label>
                <input type="text" name="name" value="{{ old('name', $shift->name) }}"
                       class="fc-input" placeholder="e.g. Day, Night, Afternoon" required>
            </div>
            <div style="display:flex;align-items:center;gap:6px;padding-bottom:2px;">
                <input type="checkbox" name="is_active" value="1" id="ea"
                       {{ $shift->is_active ? 'checked' : '' }}>
                <label for="ea" class="fc-label" style="margin:0;">Active</label>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-add" style="padding:9px 18px;font-size:.82rem;">Save</button>
                <a href="{{ route('shifts.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    @else
    {{-- Create mode --}}
    <div class="form-card" style="margin-bottom:20px;">
        <p style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#fcb913;margin-bottom:12px;">● Add New Shift</p>
        <form method="POST" action="{{ route('shifts.store') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            @csrf
            <div style="flex:1;min-width:160px;">
                <label class="fc-label">Shift Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="fc-input" placeholder="e.g. Day, Night, Afternoon" required>
            </div>
            <div>
                <button type="submit" class="btn-add" style="padding:9px 18px;font-size:.82rem;">+ Add Shift</button>
            </div>
        </form>
    </div>
    @endisset

    {{-- ── Table ── --}}
    <div class="data-card">
        <div class="tbl-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="th-c">Status</th>
                        <th class="th-c">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $s)
                    <tr>
                        <td style="color:#9ca3af;font-size:.78rem;">{{ $loop->iteration }}</td>
                        <td style="font-weight:600;">{{ $s->name }}</td>
                        <td class="td-c">
                            <form method="POST" action="{{ route('shifts.toggle', $s) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;">
                                    @if($s->is_active)
                                    <span style="background:rgba(34,197,94,.15);color:#22c55e;border:1px solid #22c55e;border-radius:20px;padding:3px 12px;font-size:.72rem;font-weight:600;white-space:nowrap;">&#9679; Active</span>
                                    @else
                                    <span style="background:rgba(156,163,175,.12);color:#9ca3af;border:1px solid #6b7280;border-radius:20px;padding:3px 12px;font-size:.72rem;font-weight:600;white-space:nowrap;">&#9679; Inactive</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="td-c">
                            <div class="act-group">
                                <a href="{{ route('shifts.edit', $s) }}" class="act-btn act-edit" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('shifts.destroy', $s) }}" style="display:contents" onsubmit="return confirm('Delete shift: {{ $s->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn act-delete" title="Delete">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row"><td colspan="4">No shifts configured yet. Add your first shift above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
