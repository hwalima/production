@extends('layouts.app')
@section('title', 'Edit SHE Indicators')
@section('page-title', 'SHE')
@section('content')

<div style="max-width:960px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit SHE Indicators</h1>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:2px;">
                <a href="{{ route('she.index', ['period' => $period]) }}" style="color:#fcb913;">SHE Report</a>
                &rsaquo; Edit Indicators
            </p>
        </div>
        <a href="{{ route('she.index', ['period' => $period]) }}" class="btn-cancel">&larr; Back</a>
    </div>

    @if($departments->isEmpty())
    <div style="padding:32px;text-align:center;color:#6b7280;font-size:.85rem;background:var(--card);border-radius:12px;">
        No active departments found. Add departments in
        <a href="{{ route('mining-departments.index') }}" style="color:#fcb913;">Settings → Departments</a>.
    </div>
    @else
    <div class="form-card">
        <form method="POST" action="{{ route('she.indicators.store') }}">
            @csrf

            <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,.07);">
                <label class="fc-label" style="margin:0;white-space:nowrap;">Reporting Period:</label>
                <input type="month" name="period" value="{{ old('period', $period) }}"
                       class="fc-input" style="width:180px;" required>
                <span style="font-size:.75rem;color:#6b7280;">Changes affect the selected month only</span>
            </div>

            <div class="tbl-scroll">
            <table style="width:100%;border-collapse:collapse;min-width:480px;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:8px 12px;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;border-bottom:2px solid rgba(255,255,255,.08);min-width:160px;">
                            INDICATOR
                        </th>
                        @foreach($departments as $dept)
                        <th style="text-align:center;padding:8px 10px;font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#fcb913;border-bottom:2px solid rgba(252,185,19,.3);white-space:nowrap;">
                            {{ $dept->name }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($indicatorLabels as $field => $label)
                    <tr style="{{ $loop->even ? 'background:rgba(255,255,255,.02);' : '' }}">
                        <td style="padding:8px 12px;font-size:.82rem;font-weight:600;color:var(--text);">{{ $label }}</td>
                        @foreach($departments as $dept)
                        @php
                            $raw    = $indicators[$dept->id]?->{$field} ?? 0;
                            $val    = (float)$raw > 0 ? $raw : '';
                            $oldVal = old("indicators.{$dept->id}.{$field}", $val);
                        @endphp
                        <td style="padding:6px 8px;text-align:center;">
                            <input type="number"
                                   name="indicators[{{ $dept->id }}][{{ $field }}]"
                                   value="{{ $oldVal }}"
                                   min="0" step="0.01" placeholder="—"
                                   style="width:90px;background:var(--input-bg);border:1px solid var(--topbar-border);border-radius:6px;
                                          padding:6px 8px;color:var(--text);font-size:.82rem;text-align:center;"
                                   onfocus="this.style.borderColor='#fcb913'"
                                   onblur="this.style.borderColor='var(--topbar-border)'">
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn-submit">Save Indicators</button>
                <a href="{{ route('she.index', ['period' => $period]) }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
