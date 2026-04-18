@extends('layouts.app')
@section('page-title', 'Bulk Import')
@section('content')

<div class="page-header">
    <h1 class="page-title">Bulk Import</h1>
</div>

<p style="color:#9ca3af;margin-bottom:24px;font-size:.9rem;">
    Upload CSV or Excel (.xlsx) files to import records in bulk. Each import page includes a downloadable template with the correct column layout.
</p>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;">

    {{-- Production --}}
    <div style="background:var(--card);border-radius:16px;border:1px solid var(--topbar-border);padding:28px 24px;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(252,185,19,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">⛏️</div>
            <div>
                <div style="font-weight:800;font-size:1rem;color:var(--text);">Production Records</div>
                <div style="font-size:.75rem;color:#9ca3af;">Daily ore &amp; gold data</div>
            </div>
        </div>
        <p style="font-size:.82rem;color:#9ca3af;margin:0;line-height:1.5;">
            Import daily production records including ore hoisted, crushed, milled, gold smelted, purity, and fidelity price.
            Existing records matching the same <strong style="color:var(--text);">date + shift</strong> will be updated.
        </p>
        <a href="{{ route('import.production') }}" class="btn-add" style="text-align:center;justify-content:center;">
            Import Production
        </a>
    </div>

    {{-- Consumables --}}
    <div style="background:var(--card);border-radius:16px;border:1px solid var(--topbar-border);padding:28px 24px;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(34,197,94,.12);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">📦</div>
            <div>
                <div style="font-weight:800;font-size:1rem;color:var(--text);">Consumables Catalog</div>
                <div style="font-size:.75rem;color:#9ca3af;">Stores &amp; inventory items</div>
            </div>
        </div>
        <p style="font-size:.82rem;color:#9ca3af;margin:0;line-height:1.5;">
            Bulk-add or update consumable items in the stores catalog. Existing items matching the same <strong style="color:var(--text);">name</strong> will be updated.
            Stock movements are managed separately via Receive / Use.
        </p>
        <a href="{{ route('import.consumables') }}" class="btn-add" style="text-align:center;justify-content:center;">
            Import Consumables
        </a>
    </div>

    {{-- Labour & Energy --}}
    <div style="background:var(--card);border-radius:16px;border:1px solid var(--topbar-border);padding:28px 24px;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(99,102,241,.12);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">⚡</div>
            <div>
                <div style="font-weight:800;font-size:1rem;color:var(--text);">Labour &amp; Energy Costs</div>
                <div style="font-size:.75rem;color:#9ca3af;">ZESA, diesel &amp; labour</div>
            </div>
        </div>
        <p style="font-size:.82rem;color:#9ca3af;margin:0;line-height:1.5;">
            Import daily labour and energy cost records. Existing records matching the same <strong style="color:var(--text);">date</strong> will be updated.
        </p>
        <a href="{{ route('import.labour-energy') }}" class="btn-add" style="text-align:center;justify-content:center;">
            Import Labour &amp; Energy
        </a>
    </div>

</div>

<div style="margin-top:32px;background:var(--card);border-radius:12px;border:1px solid var(--topbar-border);padding:20px 24px;">
    <div style="font-weight:700;font-size:.85rem;color:var(--text);margin-bottom:10px;">General Import Guidelines</div>
    <ul style="font-size:.82rem;color:#9ca3af;margin:0;padding-left:18px;line-height:1.8;">
        <li>Accepted formats: <strong style="color:var(--text);">.csv</strong> and <strong style="color:var(--text);">.xlsx</strong> (Excel). Max file size: 10 MB.</li>
        <li>The <strong style="color:var(--text);">first row must be column headers</strong>. Headers are matched case-insensitively.</li>
        <li>Dates must be in <strong style="color:var(--text);">YYYY-MM-DD</strong> format (e.g. 2026-04-01).</li>
        <li>Numbers may optionally include commas as thousand separators (e.g. 1,200.50).</li>
        <li>Blank rows in the file are silently skipped.</li>
        <li>Each import shows a row-by-row error report — rows with errors are skipped, valid rows are always saved.</li>
        <li>All imports are wrapped in a database transaction — if the entire operation fails the database is unchanged.</li>
    </ul>
</div>

@endsection
