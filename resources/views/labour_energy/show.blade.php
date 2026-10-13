@extends('layouts.app')
@section('title', 'Labour & Energy Details')
@section('page-title', 'Labour & Energy')
@section('content')
<div style="max-width:640px;">
    <div class="page-header">
        <h1 class="page-title">Labour &amp; Energy Details</h1>
        <a href="{{ route('labour-energy.index') }}" class="btn-cancel">&larr; Back</a>
    </div>
    <div class="detail-card">
        <div class="detail-row"><span class="dr-label">Date</span><span class="dr-value">{{ $labour_energy->date->format('d M Y') }}</span></div>
        <div class="detail-row"><span class="dr-label">Power — ZESA</span><span class="dr-value">{{ $currencySymbol }}{{ number_format($labour_energy->zesa_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Power — Diesel / Generator</span><span class="dr-value">{{ $currencySymbol }}{{ number_format($labour_energy->diesel_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Other Mining Costs</span><span class="dr-value">{{ $currencySymbol }}{{ number_format($labour_energy->mining_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Other Milling Costs</span><span class="dr-value">{{ $currencySymbol }}{{ number_format($labour_energy->milling_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Maintenance</span><span class="dr-value">{{ $currencySymbol }}{{ number_format($labour_energy->maintenance_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Transport</span><span class="dr-value">{{ $currencySymbol }}{{ number_format($labour_energy->transport_cost, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Leave</span><span class="dr-value">{{ number_format($labour_energy->leave, 2) }} days</span></div>
        <div class="detail-row"><span class="dr-label">Offdays</span><span class="dr-value">{{ number_format($labour_energy->offdays, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Sick</span><span class="dr-value">{{ number_format($labour_energy->sick, 2) }} days</span></div>
        <div class="detail-row"><span class="dr-label">AWOL</span><span class="dr-value">{{ number_format($labour_energy->awol, 2) }}</span></div>
        <div class="detail-row"><span class="dr-label">Terminations</span><span class="dr-value">{{ number_format($labour_energy->terminations, 2) }}</span></div>
        <div class="detail-row" style="align-items:flex-start;">
            <span class="dr-label" style="padding-top:2px;">Labour Cost</span>
            <span class="dr-value">
                <span style="font-weight:600;">{{ $currencySymbol }}{{ number_format($labour_energy->labour_cost, 2) }} total</span>
                @if($labour_energy->deptCosts->isNotEmpty())
                <table style="margin-top:8px;width:100%;border-collapse:collapse;font-size:0.85rem;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="text-align:left;padding:4px 8px;color:var(--text-muted, #9ca3af);font-weight:500;">Department</th>
                            <th style="text-align:right;padding:4px 8px;color:var(--text-muted, #9ca3af);font-weight:500;">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labour_energy->deptCosts as $dc)
                        <tr style="{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">
                            <td style="padding:5px 8px;color:var(--text);">{{ $dc->department->name ?? '—' }}</td>
                            <td style="padding:5px 8px;text-align:right;color:var(--text);">{{ $currencySymbol }}{{ number_format($dc->labour_cost, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <span style="display:block;color:#9ca3af;font-size:0.8rem;margin-top:4px;">No department breakdown recorded.</span>
                @endif
            </span>
        </div>
    </div>
</div>
@endsection
