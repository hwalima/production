@extends('layouts.app')
@section('title', 'Import Consumables')
@section('page-title', 'Bulk Import')
@section('content')

<div style="max-width:760px;">
    <div class="page-header">
        <h1 class="page-title">Import Consumables Catalog</h1>
        <a href="{{ route('import.index') }}" class="btn-cancel">&larr; Back</a>
    </div>

    {{-- ── Result banner ─────────────────────────────────────────────── --}}
    @if(session('import_result'))
    @php $r = session('import_result'); @endphp
    <div style="background:var(--card);border-radius:14px;border:1px solid {{ $r['errors'] ? '#f59e0b' : '#22c55e' }};padding:20px 22px;margin-bottom:20px;">
        <div style="font-weight:800;font-size:.95rem;color:{{ $r['errors'] ? '#f59e0b' : '#22c55e' }};margin-bottom:8px;">
            Import Complete
        </div>
        <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:.85rem;">
            <span>✅ <strong>{{ $r['inserted'] }}</strong> inserted</span>
            <span>🔄 <strong>{{ $r['updated'] }}</strong> updated</span>
            @if($r['errors'])
            <span style="color:#f59e0b;">⚠️ <strong>{{ count($r['errors']) }}</strong> skipped</span>
            @endif
        </div>
        @if($r['errors'])
        <div style="margin-top:12px;border-top:1px solid var(--topbar-border);padding-top:12px;">
            <div style="font-size:.8rem;font-weight:700;color:#9ca3af;margin-bottom:6px;">SKIPPED ROWS</div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                @foreach($r['errors'] as $err)
                <div style="font-size:.8rem;color:#f59e0b;">Row {{ $err['row'] }}: {{ $err['message'] }}</div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($errors->any())
    <div style="background:rgba(239,68,68,.08);border:1px solid #ef4444;border-radius:12px;padding:16px 18px;margin-bottom:18px;color:#ef4444;font-size:.85rem;">
        {{ $errors->first() }}
    </div>
    @endif

    {{-- ── Upload card ───────────────────────────────────────────────── --}}
    <div class="form-card" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
            <div>
                <div style="font-weight:800;font-size:.95rem;color:var(--text);">Upload File</div>
                <div style="font-size:.78rem;color:#9ca3af;margin-top:2px;">CSV or Excel (.xlsx) · Max 10 MB</div>
            </div>
            <a href="{{ route('import.template', 'consumables') }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:.8rem;font-weight:700;border-radius:10px;border:1px solid var(--topbar-border);background:var(--card);color:var(--text);text-decoration:none;"
               onmouseover="this.style.background='#fcb913';this.style.color='#001a4d';this.style.borderColor='#fcb913'"
               onmouseout="this.style.background='';this.style.color='';this.style.borderColor=''">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Template
            </a>
        </div>

        <form action="{{ route('import.consumables.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="border:2px dashed var(--topbar-border);border-radius:12px;padding:32px 24px;text-align:center;background:var(--input-bg);margin-bottom:16px;" id="dropzone">
                <svg viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" width="36" height="36" style="margin-bottom:10px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                <div style="font-size:.85rem;color:#9ca3af;margin-bottom:10px;">Drag &amp; drop your file here, or click to select</div>
                <label for="file" style="cursor:pointer;display:inline-block;padding:8px 18px;background:#fcb913;color:#001a4d;font-weight:700;font-size:.8rem;border-radius:8px;">Choose File</label>
                <input type="file" id="file" name="file" accept=".csv,.xlsx,.xls" style="display:none;" onchange="document.getElementById('filename').textContent=this.files[0]?.name??''">
                <div id="filename" style="margin-top:8px;font-size:.78rem;color:#fcb913;font-weight:600;"></div>
            </div>

            <div style="display:flex;align-items:center;gap:8px;">
                <button type="submit" class="btn-add">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import Consumables
                </button>
                <span style="font-size:.75rem;color:#9ca3af;">Existing items (same name) will be updated.</span>
            </div>
        </form>
    </div>

    {{-- ── Column reference ─────────────────────────────────────────── --}}
    <div class="form-card">
        <div style="font-weight:800;font-size:.88rem;color:var(--text);margin-bottom:14px;">Column Reference</div>
        <div class="tbl-scroll">
        <table class="data-table" style="font-size:.78rem;">
            <thead>
                <tr>
                    <th>Column</th><th>Required</th><th>Format / Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>name</code></td><td style="color:#22c55e;">Yes</td><td>Unique item name. Used to match existing records.</td></tr>
                <tr><td><code>category</code></td><td style="color:#22c55e;">Yes</td><td><code>blasting</code> / <code>chemicals</code> / <code>mechanical</code> / <code>ppe</code> / <code>general</code></td></tr>
                <tr><td><code>description</code></td><td style="color:#9ca3af;">No</td><td>Free text description</td></tr>
                <tr><td><code>purchase_unit</code></td><td style="color:#22c55e;">Yes</td><td>e.g. box, bag, litre, drum</td></tr>
                <tr><td><code>use_unit</code></td><td style="color:#22c55e;">Yes</td><td>e.g. each, ml, kg, metre</td></tr>
                <tr><td><code>units_per_pack</code></td><td style="color:#22c55e;">Yes</td><td>Number of use_units per purchase_unit</td></tr>
                <tr><td><code>pack_cost</code></td><td style="color:#22c55e;">Yes</td><td>Cost per purchase_unit (decimal)</td></tr>
                <tr><td><code>reorder_level</code></td><td style="color:#9ca3af;">No</td><td>Reorder threshold in use_units (default 0)</td></tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
(function(){
    const dz = document.getElementById('dropzone');
    const inp = document.getElementById('file');
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='#fcb913'; });
    dz.addEventListener('dragleave', () => { dz.style.borderColor=''; });
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.style.borderColor='';
        if (e.dataTransfer.files.length) {
            inp.files = e.dataTransfer.files;
            document.getElementById('filename').textContent = e.dataTransfer.files[0].name;
        }
    });
})();
</script>
@endsection
