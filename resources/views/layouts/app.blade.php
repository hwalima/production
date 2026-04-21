<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->check() && (auth()->user()->theme_preference ?? 'light') === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $appSettings  = \Illuminate\Support\Facades\Cache::remember('app_settings', 600, fn() => \App\Models\Setting::all()->pluck('value', 'key'));
            $companyName  = $appSettings['company_name'] ?? config('app.name', 'My Mine');
            $logoPath     = $appSettings['logo_path'] ?? '';
            $logoUrl      = $logoPath ? asset('storage/' . $logoPath) : null;
        @endphp
        <title>@yield('title', $companyName) — {{ $companyName }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32.png') }}?v=2">
        {{-- PWA --}}
        <link rel="manifest" href="/manifest.json">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $companyName }}">
        <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
        <meta name="theme-color" content="#fcb913">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            /* ── Dark mode ── */
            :root { --bg:#f5f5f5; --text:#001a4d; --card:#fff; --topbar:#fff; --topbar-border:#e5e7eb; --input-bg:#f3f4f6; }
            html.dark { --bg:#111827; --text:#f3f4f6; --card:#1f2937; --topbar:#1f2937; --topbar-border:#374151; --input-bg:#374151; }
            body { background-color:var(--bg); color:var(--text); transition:background .2s,color .2s; }

            /* ── Sidebar ── */
            .sidebar { background:linear-gradient(170deg,#001f5c 0%,#000f30 100%); color:#fff; width:240px; transition:width .26s ease; overflow:hidden; z-index:10; border-right:1px solid rgba(255,255,255,.06); }
            .sidebar.collapsed { width:64px; }
            .sidebar a { display:flex; align-items:center; gap:10px; padding:8px 10px; margin:1px 8px; border-radius:8px; color:rgba(255,255,255,.62); text-decoration:none; font-size:.84rem; transition:background .15s,color .15s,border-color .15s; overflow:hidden; position:relative; border-left:3px solid transparent; }
            .sidebar.collapsed a { padding:10px 0; justify-content:center; gap:0; margin:1px 4px; border-left:none; }
            .sidebar a:hover { background:rgba(255,255,255,.07); color:rgba(255,255,255,.95); border-left-color:rgba(252,185,19,.35); }
            .sidebar a.active { background:rgba(252,185,19,.13); color:#fcb913; font-weight:600; border-left-color:#fcb913; box-shadow:inset 0 0 0 1px rgba(252,185,19,.18); }
            .sidebar.collapsed a.active { border-left:none; box-shadow:none; background:rgba(252,185,19,.22); }
            .sidebar hr { border-color:rgba(255,255,255,.06); margin:4px 8px; }
            .sidebar .nav-icon { width:20px; text-align:center; flex-shrink:0; font-size:1rem; }
            /* ── Section labels ── */
            .nav-section-label { display:flex; align-items:center; gap:8px; padding:16px 14px 5px; overflow:hidden; }
            .nav-section-label .nsl-text { font-size:.58rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color:rgba(252,185,19,.65); white-space:nowrap; flex-shrink:0; max-width:120px; overflow:hidden; opacity:1; transition:max-width .22s,opacity .15s; }
            .nav-section-label .nsl-line { flex:1; height:1px; background:linear-gradient(to right,rgba(252,185,19,.28),transparent); min-width:0; opacity:1; transition:opacity .15s; }
            .sidebar.collapsed .nav-section-label .nsl-text { max-width:0; opacity:0; }
            .sidebar.collapsed .nav-section-label .nsl-line { opacity:0; }
            .sidebar.collapsed .nav-section-label { padding:10px 0; justify-content:center; }
            .nav-group-items { overflow:visible; }
            .sidebar a.sub { }
            .nav-text { overflow:hidden; white-space:nowrap; max-width:160px; opacity:1; transition:max-width .22s ease,opacity .15s; }
            .sidebar.collapsed .nav-text { max-width:0; opacity:0; }
            .brand-text { overflow:hidden; white-space:nowrap; max-width:130px; opacity:1; transition:max-width .22s ease,opacity .15s; }
            .sidebar.collapsed .brand-text { max-width:0; opacity:0; }
            .sb-footer { transition:opacity .2s; overflow:hidden; white-space:normal; word-break:break-word; }
            .sidebar.collapsed .sb-footer { opacity:0; pointer-events:none; }
            .sb-toggle { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; background:transparent; border:none; cursor:pointer; color:#9ca3af; font-size:1rem; transition:background .15s,color .15s; flex-shrink:0; padding:0; line-height:1; }
            .sb-toggle:hover { background:#202e65; color:#fcb913; }
            /* ── Custom thin scrollbars (Webkit + Firefox) ── */
            ::-webkit-scrollbar { width:5px; height:5px; }
            ::-webkit-scrollbar-track { background:transparent; }
            ::-webkit-scrollbar-thumb { background:rgba(156,163,175,.35); border-radius:99px; }
            ::-webkit-scrollbar-thumb:hover { background:rgba(252,185,19,.55); }
            /* sidebar scrollbar — lighter on dark bg */
            #sidebarNav::-webkit-scrollbar-thumb { background:rgba(255,255,255,.15); }
            #sidebarNav::-webkit-scrollbar-thumb:hover { background:rgba(252,185,19,.5); }
            * { scrollbar-width:thin; scrollbar-color:rgba(156,163,175,.35) transparent; }
            #sidebarNav { scrollbar-color:rgba(255,255,255,.15) transparent; }
            /* Mobile sidebar overlay */
            .sb-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:30; }
            .sb-overlay.visible { display:block; }
            @media(max-width:1023px){
                .sidebar { transform:translateX(-100%); width:240px !important; z-index:40;
                           transition:transform .28s ease; }
                /* prevent the collapsed (64px) class from taking effect on small screens */
                .sidebar.collapsed { width:240px !important; transform:translateX(-100%); }
                .sidebar.mobile-open { transform:translateX(0) !important; }
                .topbar { left:0 !important; }
                .main-area { margin-left:0 !important; }
            }

            /* ── Topbar ── */
            .topbar { background-color:var(--topbar); border-bottom:1px solid var(--topbar-border); height:60px; position:fixed; top:0; left:240px; right:0; z-index:20; display:flex; align-items:center; padding:0 20px; gap:10px; transition:background .2s,left .26s ease; }
            .topbar-logo { display:flex; align-items:center; gap:8px; text-decoration:none; flex-shrink:0; overflow:hidden; }
            .topbar-logo .tl-icon { width:28px; height:28px; border-radius:8px; flex-shrink:0; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#fcb913; color:#001a4d; font-size:.9rem; }
            .topbar-logo .tl-icon img { width:100%; height:100%; object-fit:contain; }
            .topbar-logo .tl-name { font-size:.75rem; font-weight:700; color:var(--text); white-space:nowrap; }
            .topbar input[type=search] { background:var(--input-bg); color:var(--text); border:none; border-radius:8px; padding:7px 14px 7px 36px; font-size:0.875rem; width:240px; outline:none; transition:background .2s; }
            .topbar .search-wrap { position:relative; }
            .topbar .search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#9ca3af; pointer-events:none; }

            /* icon buttons */
            .topbar .icon-btn { position:relative; display:flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:none; cursor:pointer; background:var(--input-bg); color:var(--text); transition:background .15s; outline:none; -webkit-tap-highlight-color:transparent; touch-action:manipulation; }
            .topbar .icon-btn:hover { background:#fcb913; color:#001a4d; }
            .topbar .icon-btn:focus-visible { box-shadow:0 0 0 2px #fcb913; }
            .topbar .badge { position:absolute; top:4px; right:4px; width:8px; height:8px; border-radius:50%; background:#ef4444; border:2px solid var(--topbar); }

            /* profile button */
            .topbar .profile-btn { display:flex; align-items:center; gap:8px; padding:5px 10px 5px 5px; border-radius:50px; border:none; cursor:pointer; background:var(--input-bg); color:var(--text); font-size:0.8rem; font-weight:600; transition:background .15s; }
            .topbar .profile-btn:hover { background:#fcb913; color:#001a4d; }
            .topbar .avatar { width:28px; height:28px; border-radius:50%; background:#fcb913; color:#001a4d; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem; flex-shrink:0; }

            /* dropdown */
            .dropdown { position:relative; }
            .dropdown-menu { display:none; position:absolute; right:0; top:calc(100% + 8px); min-width:180px; background:var(--card); border:1px solid var(--topbar-border); border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:6px; z-index:100; }
            .dropdown-menu.open { display:block; }
            .dropdown-menu a, .dropdown-menu button { display:flex; align-items:center; gap:8px; width:100%; padding:8px 12px; border-radius:6px; font-size:0.85rem; color:var(--text); text-decoration:none; background:none; border:none; cursor:pointer; transition:background .12s; }
            .dropdown-menu a:hover, .dropdown-menu button:hover { background:#fcb913; color:#001a4d; }
            .dropdown-menu hr { border-color:var(--topbar-border); margin:4px 0; }

            /* notif panel */
            .notif-panel { min-width:300px; right:0; }
            .notif-panel .notif-item { padding:10px 12px; border-radius:6px; font-size:0.82rem; color:var(--text); transition:background .12s; }
            .notif-panel .notif-item:hover { background:var(--input-bg); }
            .notif-panel .notif-dot { width:8px; height:8px; border-radius:50%; background:#fcb913; flex-shrink:0; }

            /* ── Page header ── */
            .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:12px; flex-wrap:wrap; }
            .page-title  { font-size:1.5rem; font-weight:700; color:var(--text); }
            .btn-add { display:inline-flex; align-items:center; gap:6px; background:#fcb913; color:#001a4d; padding:8px 18px; border-radius:8px; font-weight:600; font-size:.875rem; text-decoration:none; transition:opacity .15s; border:none; cursor:pointer; white-space:nowrap; flex-shrink:0; }
            .btn-add:hover { opacity:.85; color:#001a4d; }

            /* ── Form card (create/edit/show) ── */
            .form-card { background:var(--card); border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.08); padding:24px; border:1px solid var(--topbar-border); }
            .form-card h2.fc-section { font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#9ca3af; padding-bottom:8px; border-bottom:1px solid var(--topbar-border); margin-bottom:16px; }
            .fc-label { display:block; font-size:.8rem; font-weight:600; color:var(--text); margin-bottom:5px; }
            .fc-input { width:100%; background:var(--input-bg); color:var(--text); border:1.5px solid var(--topbar-border); border-radius:10px; padding:9px 13px; font-size:.875rem; outline:none; transition:border-color .15s,box-shadow .15s; }
            .fc-input:focus { border-color:#fcb913; box-shadow:0 0 0 3px rgba(252,185,19,.15); }
            .fc-frozen { background:var(--topbar-border) !important; color:#9ca3af !important; border-style:dashed !important; cursor:default; }
            .fc-auto-label { font-size:.68rem; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:#fcb913; padding:10px 0 8px; border-top:1px dashed var(--topbar-border); margin-top:8px; display:block; }
            .fc-error { font-size:.75rem; color:#ef4444; margin-top:4px; }
            .fc-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
            @media(max-width:599px){ .fc-grid { grid-template-columns:1fr; } }
            .form-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; padding-top:8px; }
            .btn-submit { display:inline-flex; align-items:center; gap:7px; background:#fcb913; color:#001a4d; border:none; border-radius:10px; padding:10px 24px; font-size:.875rem; font-weight:700; cursor:pointer; transition:filter .15s,transform .1s; }
            .btn-submit:hover { filter:brightness(1.08); transform:translateY(-1px); }
            .btn-cancel { display:inline-flex; align-items:center; gap:5px; color:#9ca3af; font-size:.875rem; text-decoration:none; padding:10px 6px; }
            .btn-cancel:hover { color:var(--text); }

            /* ── Detail card (show views) ── */
            .detail-card { background:var(--card); border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.08); padding:24px; border:1px solid var(--topbar-border); }
            .detail-row { display:flex; justify-content:space-between; align-items:baseline; gap:8px; padding:9px 0; border-bottom:1px solid var(--topbar-border); font-size:.875rem; }
            .detail-row:last-of-type { border-bottom:none; }
            .detail-row .dr-label { color:#9ca3af; font-size:.78rem; font-weight:600; flex-shrink:0; }
            .detail-row .dr-value { color:var(--text); font-weight:700; text-align:right; }

            /* ── Data table card ── */
            .data-card { background:var(--card); border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.1); }
            .tbl-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
            .data-table { width:100%; border-collapse:collapse; min-width:520px; }
            .data-table thead tr { background:#fcb913; color:#001a4d; }
            .data-table thead th { padding:11px 14px; text-align:left; font-size:.75rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; white-space:nowrap; }
            .data-table thead th.th-c { text-align:center; }
            .data-table thead th.th-r { text-align:right; }
            .data-table tbody tr { border-bottom:1px solid var(--topbar-border); transition:background .12s; }
            .data-table tbody tr:last-child { border-bottom:none; }
            .data-table tbody tr:hover { background:rgba(252,185,19,.07); }
            .data-table tbody td { padding:10px 14px; font-size:.85rem; color:var(--text); white-space:nowrap; }
            .data-table tbody td.td-c { text-align:center; }
            .data-table tbody td.td-r { text-align:right; }
            .data-table tbody td.td-muted { color:#9ca3af; font-size:.8rem; }
            .data-table .empty-row td { text-align:center; padding:48px; color:#9ca3af; font-size:.9rem; }

            /* ── CRUD icon buttons ── */
            .act-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:none; cursor:pointer; transition:opacity .15s,transform .15s; flex-shrink:0; padding:0; text-decoration:none; -webkit-tap-highlight-color:transparent; }
            .act-btn:hover { opacity:.8; transform:scale(1.1); }
            .act-btn svg { width:15px; height:15px; pointer-events:none; }
            .act-view   { background:#dbeafe; color:#1e40af; }
            .act-edit   { background:#fef3c7; color:#92400e; }
            .act-delete { background:#fee2e2; color:#991b1b; }
            html.dark .act-view   { background:#1e3a5f; color:#93c5fd; }
            html.dark .act-edit   { background:#3d2400; color:#fde68a; }
            html.dark .act-delete { background:#3d0a0a; color:#fca5a5; }
            .act-group { display:flex; align-items:center; justify-content:center; gap:5px; }

            /* ── DataTable toolbar ── */
            .dt-toolbar { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; gap:8px; flex-wrap:wrap; border-bottom:1px solid var(--topbar-border); }
            .dt-toolbar-left { display:flex; align-items:center; gap:8px; }
            .dt-toolbar-right { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
            .dt-search-wrap { position:relative; }
            .dt-search-wrap svg { position:absolute; left:9px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#9ca3af; pointer-events:none; }
            .dt-search { background:var(--input-bg); color:var(--text); border:1px solid var(--topbar-border); border-radius:8px; padding:7px 12px 7px 30px; font-size:.85rem; width:200px; outline:none; transition:border-color .15s; }
            .dt-search:focus { border-color:#fcb913; }
            .dt-btn { display:inline-flex; align-items:center; gap:5px; padding:7px 12px; border-radius:8px; border:none; cursor:pointer; font-size:.8rem; font-weight:600; transition:opacity .15s,transform .12s; white-space:nowrap; -webkit-tap-highlight-color:transparent; }
            .dt-btn:hover { opacity:.85; transform:translateY(-1px); }
            .dt-btn svg { width:14px; height:14px; flex-shrink:0; }
            .dt-btn-pdf      { background:#fee2e2; color:#991b1b; }
            .dt-btn-whatsapp { background:#dcfce7; color:#166534; }
            .dt-btn-email    { background:#dbeafe; color:#1e40af; }
            html.dark .dt-btn-pdf      { background:#3d0a0a; color:#fca5a5; }
            html.dark .dt-btn-whatsapp { background:#052e16; color:#86efac; }
            html.dark .dt-btn-email    { background:#0a1d3d; color:#93c5fd; }
            .data-table thead th.dt-sortable { cursor:pointer; user-select:none; }
            .data-table thead th.dt-sortable:hover { opacity:.85; }
            .data-table thead th.dt-sortable::after  { content:' ↕'; opacity:.3;  font-size:.7em; font-weight:400; }
            .data-table thead th.dt-sort-asc::after  { content:' ↑'; opacity:.7;  font-size:.7em; font-weight:400; }
            .data-table thead th.dt-sort-desc::after { content:' ↓'; opacity:.7;  font-size:.7em; font-weight:400; }
            tr.dt-hidden { display:none !important; }
            .dt-no-match { text-align:center; padding:40px 16px !important; color:#9ca3af; font-size:.9rem; }

            /* ── Date range filter bar (shared across all index pages) ── */
            .fbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; background:var(--card); border:1px solid var(--topbar-border); border-radius:16px; padding:11px 16px; margin-bottom:18px; }
            html.dark .fbar { background:rgba(255,255,255,.04); border-color:rgba(255,255,255,.08); }
            .fbar-label { font-size:.63rem; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:#9ca3af; white-space:nowrap; }
            .fbar-ctrl { display:flex; align-items:center; gap:8px; flex-wrap:nowrap; }
            .fbar input[type=date] { background:var(--input-bg,var(--bg)); border:1px solid var(--topbar-border); border-radius:9px; padding:6px 10px; font-size:.78rem; color:var(--text); outline:none; transition:border-color .15s; color-scheme:dark; }
            .fbar input[type=date]:focus { border-color:#db9f01; }
            html:not(.dark) .fbar input[type=date] { color-scheme:light; }
            .fbar-sep { color:#6b7280; font-size:.75rem; }
            .fbar-apply { padding:6px 16px; border-radius:9px; font-size:.75rem; font-weight:700; background:linear-gradient(135deg,#db9f01,#fcb913); color:#001a4d; border:none; cursor:pointer; transition:filter .15s,transform .15s; white-space:nowrap; }
            .fbar-apply:hover { filter:brightness(1.1); transform:translateY(-1px); }
            .fbar-presets { display:flex; gap:5px; flex-wrap:wrap; margin-left:auto; }
            .fbar-preset { padding:5px 11px; border-radius:8px; font-size:.67rem; font-weight:700; background:transparent; border:1px solid var(--topbar-border); color:#9ca3af; cursor:pointer; text-decoration:none; transition:background .12s,color .12s; white-space:nowrap; }
            .fbar-preset:hover, .fbar-preset.active { background:rgba(219,159,1,.12); border-color:rgba(219,159,1,.4); color:#db9f01; }
            .fbar-active { display:inline-flex; align-items:center; gap:5px; font-size:.67rem; color:#db9f01; font-weight:600; background:rgba(219,159,1,.08); border:1px solid rgba(219,159,1,.2); border-radius:8px; padding:4px 9px; }

            /* ── Mobile overrides ── */
            @media(max-width:767px){
                .fbar { flex-direction:column; align-items:stretch; gap:9px; }
                .fbar-ctrl { flex-wrap:wrap; }
                .fbar-ctrl input[type=date] { flex:1; min-width:110px; }
                .fbar-ctrl .fbar-apply { flex:1; text-align:center; justify-content:center; }
                .fbar-presets { margin-left:0; }
                .fbar-active { justify-content:center; }
                .dt-toolbar { flex-direction:column; align-items:stretch; }
                .dt-toolbar-left, .dt-toolbar-right { width:100%; }
                .dt-search { width:100%; }
                .dt-toolbar-right { justify-content:flex-start; }
                .page-title { font-size:1.2rem; }
                .notif-panel { left:16px; right:16px; min-width:unset; }
                #topSearch { width:140px; }
            }
            @media(max-width:479px){
                #topSearch { display:none; }
                .topbar .search-wrap { display:none; }
            }

            /* ── Toast notifications ── */
            #toast-container{position:fixed;inset:0;z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:48px;pointer-events:none;}
            .toast-item{position:relative;background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.16),0 4px 16px rgba(0,0,0,.08);padding:44px 28px 20px;width:280px;text-align:center;overflow:visible;pointer-events:all;opacity:0;transform:scale(.84) translateY(-18px);transition:opacity .32s cubic-bezier(.34,1.56,.64,1),transform .32s cubic-bezier(.34,1.56,.64,1);}
            html.dark .toast-item{background:#1e2a3a;box-shadow:0 24px 64px rgba(0,0,0,.55);}
            .toast-item.toast-show{opacity:1;transform:scale(1) translateY(0);}
            .toast-item.toast-hide{opacity:0;transform:scale(.84) translateY(-18px);transition:opacity .22s ease,transform .22s ease;}
            .toast-success{border-left:5px solid #22c55e;}
            .toast-error  {border-left:5px solid #ef4444;}
            .toast-warning{border-left:5px solid #f59e0b;}
            .toast-info   {border-left:5px solid #3b82f6;}
            .toast-icon-wrap{width:68px;height:68px;border-radius:50%;display:flex;align-items:center;justify-content:center;position:absolute;top:-34px;left:50%;transform:translateX(-50%);border:5px solid var(--bg,#f5f5f5);}
            html.dark .toast-icon-wrap{border-color:#111827;}
            .toast-success .toast-icon-wrap{background:#22c55e;color:#fff;}
            .toast-error   .toast-icon-wrap{background:#ef4444;color:#fff;}
            .toast-warning .toast-icon-wrap{background:#f59e0b;color:#fff;}
            .toast-info    .toast-icon-wrap{background:#3b82f6;color:#fff;}
            .toast-label{font-size:1.1rem;font-weight:800;margin-bottom:6px;margin-top:6px;}
            .toast-success .toast-label{color:#16a34a;}
            .toast-error   .toast-label{color:#dc2626;}
            .toast-warning .toast-label{color:#d97706;}
            .toast-info    .toast-label{color:#2563eb;}
            html.dark .toast-label{filter:brightness(1.25);}
            .toast-text{font-size:.83rem;color:#64748b;line-height:1.5;margin-bottom:4px;}
            html.dark .toast-text{color:#94a3b8;}
            .toast-x{position:absolute;top:10px;right:10px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.06);border:none;cursor:pointer;color:#94a3b8;border-radius:50%;padding:0;transition:background .12s,color .12s;}
            .toast-x:hover{background:rgba(0,0,0,.12);color:#64748b;}
            html.dark .toast-x{background:rgba(255,255,255,.08);}
            html.dark .toast-x:hover{background:rgba(255,255,255,.15);}
            .toast-track{position:absolute;bottom:0;left:0;right:0;height:5px;background:rgba(0,0,0,.06);border-radius:0 0 20px 20px;overflow:hidden;}
            html.dark .toast-track{background:rgba(255,255,255,.07);}
            .toast-bar{height:100%;width:100%;}
            .toast-success .toast-bar{background:#22c55e;}
            .toast-error   .toast-bar{background:#ef4444;}
            .toast-warning .toast-bar{background:#f59e0b;}
            .toast-info    .toast-bar{background:#3b82f6;}

            /* ── Confirm dialog ── */
            #confirm-overlay{position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.45);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .22s;}
            #confirm-overlay.cd-show{opacity:1;pointer-events:all;}
            #confirm-box{background:#fff;border-radius:20px;box-shadow:0 32px 80px rgba(0,0,0,.22);width:340px;max-width:calc(100vw - 40px);transform:scale(.88) translateY(14px);transition:transform .28s cubic-bezier(.34,1.56,.64,1);}
            html.dark #confirm-box{background:#1e2a3a;}
            #confirm-overlay.cd-show #confirm-box{transform:scale(1) translateY(0);}
            #confirm-icon-ring{width:68px;height:68px;border-radius:50%;background:#fee2e2;color:#ef4444;display:flex;align-items:center;justify-content:center;margin:-34px auto 0;border:5px solid #fff;position:relative;z-index:1;}
            html.dark #confirm-icon-ring{border-color:#1e2a3a;}
            #confirm-body{padding:44px 28px 28px;text-align:center;}
            #confirm-title{font-size:1.1rem;font-weight:800;color:#dc2626;margin-bottom:8px;}
            html.dark #confirm-title{color:#f87171;}
            #confirm-msg{font-size:.88rem;color:#64748b;line-height:1.55;margin-bottom:24px;}
            html.dark #confirm-msg{color:#94a3b8;}
            #confirm-btns{display:flex;gap:10px;}
            #confirm-btns button{flex:1;padding:11px;border-radius:12px;font-size:.88rem;font-weight:700;border:none;cursor:pointer;transition:filter .15s,transform .12s;}
            #confirm-btns button:hover{filter:brightness(.92);transform:translateY(-1px);}
            #confirm-btn-ok{background:#ef4444;color:#fff;}
            #confirm-btn-cancel{background:#f1f5f9;color:#475569;}
            html.dark #confirm-btn-cancel{background:#2d3748;color:#94a3b8;}
        </style>
        @stack('styles')
    </head>
    <body class="font-sans antialiased min-h-screen">
        <div class="flex min-h-screen">

            <!-- ═══════════════ SIDEBAR ═══════════════ -->
            <aside class="sidebar flex flex-col py-5 px-3 h-screen fixed left-0 top-0" id="sidebar" style="background-color:#001a4d;">
                <div class="mb-5 px-2">
                    @if($logoUrl)
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2" style="text-decoration:none;">
                            <span style="
                                display:inline-flex;
                                align-items:center;
                                justify-content:center;
                                width:36px; height:36px;
                                border-radius:8px;
                                background:#fff;
                                flex-shrink:0;
                                overflow:hidden;
                                padding:3px;
                            ">
                                <img src="{{ $logoUrl }}" alt="{{ $companyName }}"
                                     style="width:30px;height:30px;object-fit:contain;">
                            </span>
                            <span style="color:#fcb913;font-size:.8rem;font-weight:700;line-height:1.25;max-width:110px;">{{ $companyName }}</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2" style="text-decoration:none;">
                            <span style="
                                display:inline-flex;align-items:center;justify-content:center;
                                width:36px;height:36px;border-radius:8px;
                                background:#fcb913;color:#001a4d;font-size:1.1rem;flex-shrink:0;
                            ">&#9968;</span>
                            <span style="color:#fcb913;font-size:.8rem;font-weight:700;line-height:1.25;max-width:110px;">{{ $companyName }}</span>
                        </a>
                    @endif
                </div>
                <nav class="flex-1 flex flex-col gap-0.5 overflow-y-auto" id="sidebarNav">
                    {{-- ── Overview group ── --}}
                    <div class="nav-section-label"><span class="nsl-text">Overview</span><span class="nsl-line"></span></div>
                    <div class="nav-group-items" id="ng-overview">
                        <a href="{{ route('dashboard') }}" class="sub {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard"><span class="nav-icon">&#128200;</span><span class="nav-text">&nbsp;Dashboard</span></a>
                        <a href="{{ route('analytics.index') }}" class="sub {{ request()->routeIs('analytics.*') ? 'active' : '' }}" title="Analytics"><span class="nav-icon">&#127775;</span><span class="nav-text">&nbsp;Analytics</span></a>
                    </div>

                    {{-- ── Operations group ── --}}
                    <div class="nav-section-label"><span class="nsl-text">Operations</span><span class="nsl-line"></span></div>
                    <div class="nav-group-items" id="ng-ops">
                        <a href="{{ route('production.index') }}" class="sub {{ request()->routeIs('production.*') && !request()->routeIs('production.targets') ? 'active' : '' }}" title="Daily Production"><span class="nav-icon">&#129760;</span><span class="nav-text">&nbsp;Daily Production</span></a>
                        <a href="{{ route('production.targets') }}" class="sub {{ request()->routeIs('production.targets') ? 'active' : '' }}" title="Targets vs Actuals"><span class="nav-icon">&#127919;</span><span class="nav-text">&nbsp;Targets vs Actuals</span></a>
                        <a href="{{ route('drilling.index') }}" class="sub {{ request()->routeIs('drilling.*') ? 'active' : '' }}" title="Drilling"><span class="nav-icon">&#128296;</span><span class="nav-text">&nbsp;Drilling</span></a>
                        <a href="{{ route('blasting.index') }}" class="sub {{ request()->routeIs('blasting.*') ? 'active' : '' }}" title="Blasting"><span class="nav-icon">&#128165;</span><span class="nav-text">&nbsp;Blasting</span></a>
                        <a href="{{ route('chemicals.index') }}" class="sub {{ request()->routeIs('chemicals.*') ? 'active' : '' }}" title="Chemicals"><span class="nav-icon">&#9879;</span><span class="nav-text">&nbsp;Chemicals</span></a>
                        <a href="{{ route('consumables.index') }}" class="sub {{ request()->routeIs('consumables.*') ? 'active' : '' }}" title="Stores"><span class="nav-icon">&#128230;</span><span class="nav-text">&nbsp;Stores</span></a>
                        <a href="{{ route('labour-energy.index') }}" class="sub {{ request()->routeIs('labour-energy.*') ? 'active' : '' }}" title="Labour &amp; Energy"><span class="nav-icon">&#9889;</span><span class="nav-text">&nbsp;Labour &amp; Energy</span></a>
                        <a href="{{ route('machines.index') }}" class="sub {{ request()->routeIs('machines.*') ? 'active' : '' }}" title="Machines"><span class="nav-icon">&#9881;</span><span class="nav-text">&nbsp;Machines</span></a>
                        <a href="{{ route('assay.index') }}" class="sub {{ request()->routeIs('assay.*') ? 'active' : '' }}" title="Assay Results"><span class="nav-icon">&#128300;</span><span class="nav-text">&nbsp;Assay Results</span></a>
                        <a href="{{ route('she.index') }}" class="sub {{ request()->routeIs('she.*') ? 'active' : '' }}" title="SHE"><span class="nav-icon">&#9888;</span><span class="nav-text">&nbsp;SHE</span></a>
                        @php $aiOverdue = \Illuminate\Support\Facades\Cache::remember('ai_overdue_count', 300, fn() => \App\Models\ActionItem::overdueCount()); @endphp
                        <a href="{{ route('action-items.index') }}" class="sub {{ request()->routeIs('action-items.*') ? 'active' : '' }}" title="Action Items" style="position:relative;">
                            <span class="nav-icon">&#128204;</span>
                            <span class="nav-text">&nbsp;Action Items</span>
                            @if($aiOverdue > 0)
                            <span style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:#ef4444;color:#fff;font-size:.58rem;font-weight:800;padding:1px 6px;border-radius:99px;line-height:1.6;">{{ $aiOverdue }}</span>
                            @endif
                        </a>
                    </div>

                    {{-- ── Reports group ── --}}
                    <div class="nav-section-label"><span class="nsl-text">Reports</span><span class="nsl-line"></span></div>
                    <div class="nav-group-items" id="ng-reports">
                        <a href="{{ route('reports.production') }}" class="sub {{ request()->routeIs('reports.production') ? 'active' : '' }}" title="Production Report"><span class="nav-icon">&#128202;</span><span class="nav-text">&nbsp;Production Report</span></a>
                        <a href="{{ route('reports.consumables') }}" class="sub {{ request()->routeIs('reports.consumables') ? 'active' : '' }}" title="Consumables Report"><span class="nav-icon">&#128203;</span><span class="nav-text">&nbsp;Consumables Report</span></a>
                        <a href="{{ route('reports.accounts') }}" class="sub {{ request()->routeIs('reports.accounts') ? 'active' : '' }}" title="Accounts Report"><span class="nav-icon">&#128200;</span><span class="nav-text">&nbsp;Accounts Report</span></a>
                    </div>

                    {{-- ── Help ── --}}
                    <div class="nav-section-label"><span class="nsl-text">Help</span><span class="nsl-line"></span></div>
                    <div class="nav-group-items" id="ng-help">
                        <a href="{{ route('kb.index') }}" class="sub {{ request()->routeIs('kb.*') ? 'active' : '' }}" title="Knowledge Base"><span class="nav-icon">&#128218;</span><span class="nav-text">&nbsp;Help / Docs</span></a>
                        <a href="{{ route('docs.index') }}" class="{{ request()->routeIs('docs.*') ? 'active' : '' }}" title="App Documentation"><span class="nav-icon">&#128196;</span><span class="nav-text">&nbsp;App Docs / PDF</span></a>
                    </div>

                    {{-- ── Admin group ── --}}
                    @if(auth()->user()?->isAdminOrAbove())
                    <div class="nav-section-label"><span class="nsl-text">Admin</span><span class="nsl-line"></span></div>
                    <div class="nav-group-items" id="ng-admin">
                        <a href="{{ route('users.index') }}" class="sub {{ request()->routeIs('users.*') ? 'active' : '' }}" title="User Management"><span class="nav-icon">&#128100;</span><span class="nav-text">&nbsp;Users</span></a>
                        <a href="{{ route('settings.index') }}" class="sub {{ request()->routeIs('settings.*') && !request()->routeIs('mining-departments.*') ? 'active' : '' }}" title="Settings"><span class="nav-icon">&#9881;</span><span class="nav-text">&nbsp;Settings</span></a>
                        <a href="{{ route('mining-departments.index') }}" class="sub {{ request()->routeIs('mining-departments.*') ? 'active' : '' }}" title="Mining Departments"><span class="nav-icon">&#127970;</span><span class="nav-text">&nbsp;Departments</span></a>
                        @if(auth()->user()?->isSuperAdmin())
                        <a href="{{ route('roles.index') }}" class="sub {{ request()->routeIs('roles.*') ? 'active' : '' }}" title="Roles &amp; Permissions"><span class="nav-icon">&#128737;</span><span class="nav-text">&nbsp;Roles</span></a>
                        @endif
                        <a href="{{ route('maintenance.index') }}" class="sub {{ request()->routeIs('maintenance.*') ? 'active' : '' }}" title="Maintenance"><span class="nav-icon">&#128296;</span><span class="nav-text">&nbsp;Maintenance</span></a>
                    </div>
                    @endif
                </nav>
                <div class="sb-footer mt-4 pt-3 px-2 text-xs" style="border-top:1px solid rgba(255,255,255,.07); color:rgba(255,255,255,.3);">
                    {{ $companyName }} &copy; {{ date('Y') }}
                </div>
            </aside>

            <!-- Mobile sidebar overlay -->
            <div class="sb-overlay" id="sbOverlay"></div>

            <!-- ═══════════════ TOPBAR ═══════════════ -->
            <header class="topbar" id="topbar">
                <!-- Hamburger (mobile + desktop) -->
                <button id="sidebarToggleBtn" class="icon-btn" title="Toggle sidebar" style="flex-shrink:0;font-size:1.05rem;">&#9776;</button>

                <!-- Page title slot -->
                <span class="font-semibold text-sm flex-shrink-0 hidden sm:block" style="color:var(--text);">
                    @yield('page-title', config('app.name', 'My Mine'))
                </span>

                <!-- Search -->
                <div class="search-wrap flex-1 max-w-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                    <input type="search" placeholder="Search..." id="topSearch">
                </div>

                <div class="flex items-center gap-2 ml-auto">

                    <!-- Dark mode toggle -->
                    <button class="icon-btn" id="darkToggle" title="Toggle dark mode">
                        <svg id="iconSun" xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>
                        <svg id="iconMoon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"/></svg>
                    </button>

                    <!-- PWA Install button (hidden until browser fires beforeinstallprompt) -->
                    <button class="icon-btn" id="pwaInstallBtn" title="Install App" style="display:none;" aria-label="Install App">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </button>

                    <!-- Notifications -->
                    @php
                        $userNotifs   = auth()->user()->notifications()->latest()->take(15)->get();
                        $unreadCount  = $userNotifs->whereNull('read_at')->count();
                    @endphp
                    <div class="dropdown">
                        <button class="icon-btn" id="notifBtn" title="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 0 0-5-5.917V5a1 1 0 0 0-2 0v.083A6 6 0 0 0 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/></svg>
                            <span class="badge" id="notifBadge" style="{{ $unreadCount ? '' : 'display:none;' }}">{{ $unreadCount ?: '' }}</span>
                        </button>
                        <div class="dropdown-menu notif-panel" id="notifMenu">
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px 6px;">
                                <span style="font-weight:600;font-size:.85rem;color:var(--text);">Notifications</span>
                                @if($unreadCount)
                                    <button id="markAllReadBtn" onclick="notifMarkAll(event)" style="background:none;border:none;font-size:.75rem;color:#fcb913;cursor:pointer;padding:0;">Mark all read</button>
                                @endif
                            </div>
                            <hr style="border-color:var(--topbar-border);margin:4px 0 6px;">
                            @forelse($userNotifs as $notif)
                                @php
                                    $nd   = $notif->data;
                                    $read = $notif->read_at !== null;
                                    $dotColor = match($nd['type'] ?? 'info') {
                                        'warning' => '#fcb913',
                                        'danger'  => '#ef4444',
                                        'success' => '#34d399',
                                        default   => '#3b82f6',
                                    };
                                    if ($read) $dotColor = '#6b7280';
                                @endphp
                                <div class="notif-item {{ $read ? 'notif-read' : '' }}"
                                     data-notif-id="{{ $notif->id }}"
                                     style="display:flex;gap:10px;align-items:flex-start;cursor:{{ $nd['url'] ?? '' ? 'pointer' : ($read ? 'default' : 'pointer') }};"
                                     onclick="notifClick(event, this, '{{ $notif->id }}', {{ json_encode($nd['url'] ?? null) }})">
                                    <span class="notif-dot" id="dot-{{ $notif->id }}" style="background:{{ $dotColor }};margin-top:5px;flex-shrink:0;"></span>
                                    <div>
                                        <strong style="{{ $read ? 'opacity:.65;' : '' }}">{{ $nd['title'] ?? 'Notification' }}</strong><br>
                                        <span style="color:#9ca3af;font-size:.8rem;">{{ $nd['body'] ?? '' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div style="padding:18px 14px;text-align:center;color:#9ca3af;font-size:.82rem;">No notifications</div>
                            @endforelse
                            <hr style="border-color:var(--topbar-border);margin:6px 0 4px;">
                            <a href="{{ route('reports.production') }}" style="text-align:center;justify-content:center;font-size:.8rem;color:#fcb913;">View all reports →</a>
                        </div>
                    </div>

                    <!-- Profile dropdown -->
                    <div class="dropdown">
                        <button class="profile-btn" id="profileBtn">
                            @if(auth()->user()->avatar_path)
                                <img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="avatar"
                                     style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid #fcb913;">
                            @else
                                <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                            @endif
                            <span class="hidden sm:inline">{{ auth()->user()->name ?? 'User' }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="dropdown-menu" id="profileMenu">
                            <div style="padding:8px 12px 4px;">
                                <div style="font-size:.78rem;color:#9ca3af;">{{ auth()->user()->email ?? '' }}</div>
                                @php
                                    $roleColour = match(auth()->user()?->role) {
                                        'super_admin' => '#7c3aed',
                                        'admin'       => '#ef4444',
                                        'manager'     => '#fcb913',
                                        default       => '#9ca3af',
                                    };
                                @endphp
                                <span style="font-size:.68rem;font-weight:700;text-transform:uppercase;color:{{ $roleColour }};">{{ auth()->user()?->role }}</span>
                            </div>
                            <hr>
                            <a href="{{ route('profile.edit') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                                My Profile
                            </a>
                            @if(auth()->user()?->isAdminOrAbove())
                            <a href="{{ route('settings.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0..."/></svg>
                                Settings
                            </a>
                            @endif
                            <hr>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1"/></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </header>

            <!-- ═══════════════ MAIN CONTENT ═══════════════ -->
            <main class="flex-1 min-h-screen main-area" id="mainContent" style="margin-left:240px; margin-top:60px; padding:20px 16px; transition:margin-left .26s ease;">
                <style>
                    @media(min-width:640px){ #mainContent { padding:24px 24px !important; } }
                    @media(min-width:1024px){ #mainContent { padding:28px 32px !important; } }
                </style>
                @yield('content')

                {{-- ── App Footer ── --}}
                <footer style="margin-top:48px; padding:18px 0 8px; border-top:1px solid var(--topbar-border); text-align:center; font-size:.75rem; color:var(--muted,#9ca3af); line-height:2;">
                    {{-- Row 1: Company details from settings --}}
                    <div>
                        <span style="color:#fcb913; font-weight:600;">&copy; {{ date('Y') }} {{ $companyName }}</span>
                        @if(!empty($appSettings['company_email']))
                            <span style="margin:0 8px; opacity:.4;">|</span>
                            <a href="mailto:{{ $appSettings['company_email'] }}" style="color:inherit; text-decoration:none;">{{ $appSettings['company_email'] }}</a>
                        @endif
                        @if(!empty($appSettings['company_phone']))
                            <span style="margin:0 8px; opacity:.4;">|</span>
                            <a href="tel:{{ preg_replace('/\s+/', '', $appSettings['company_phone']) }}" style="color:inherit; text-decoration:none;">{{ $appSettings['company_phone'] }}</a>
                        @endif
                    </div>
                    {{-- Row 2: Hwalima Digital (hardcoded developer credit) --}}
                    <div style="margin-top:2px;">
                        <span style="opacity:.6;">Developed by</span>
                        <a href="https://www.hwalima.digital/" target="_blank" rel="noopener" style="color:#fcb913; text-decoration:none; font-weight:600; margin-left:4px;">Hwalima Digital</a>
                        <span style="margin:0 8px; opacity:.4;">|</span>
                        <a href="mailto:info@hwalima.digital" style="color:inherit; text-decoration:none;">info@hwalima.digital</a>
                        <span style="margin:0 8px; opacity:.4;">|</span>
                        <a href="tel:+27785425978" style="color:inherit; text-decoration:none;">+27 78 542 5978</a>
                    </div>
                </footer>
            </main>
        </div>

        {{-- ── Confirm Dialog ── --}}
        <div id="confirm-overlay" role="dialog" aria-modal="true">
            <div id="confirm-box">
                <div id="confirm-icon-ring">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </div>
                <div id="confirm-body">
                    <div id="confirm-title">Are you sure?</div>
                    <div id="confirm-msg"></div>
                    <div id="confirm-btns">
                        <button id="confirm-btn-cancel" type="button">Cancel</button>
                        <button id="confirm-btn-ok" type="button">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Global Toast Notifications ── --}}
        <div id="toast-container" aria-live="polite" aria-atomic="false"></div>
        <script>
        (function () {
            var D = 3000;
            var SVG = {
                success: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
                error:   '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
                warning: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                info:    '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                close:   '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            };
            var LBL = { success:'Success', error:'Failed', warning:'Warning', info:'Info' };

            function showToast(type, msg) {
                var c = document.getElementById('toast-container');
                if (!c) return;
                type = SVG[type] ? type : 'info';
                var el = document.createElement('div');
                el.className = 'toast-item toast-' + type;
                el.innerHTML =
                    '<div class="toast-icon-wrap">' + SVG[type] + '</div>' +
                    '<div class="toast-label">' + LBL[type] + '</div>' +
                    '<div class="toast-text">' + esc(msg) + '</div>' +
                    '<button class="toast-x" aria-label="Dismiss">' + SVG.close + '</button>' +
                    '<div class="toast-track"><div class="toast-bar"></div></div>';
                c.appendChild(el);
                requestAnimationFrame(function(){ requestAnimationFrame(function(){ el.classList.add('toast-show'); }); });

                var bar = el.querySelector('.toast-bar');
                var rem = D, lastTs = null, rafId = null, gone = false;
                function tick(ts) {
                    if (gone) return;
                    if (lastTs !== null) rem -= (ts - lastTs);
                    lastTs = ts;
                    bar.style.width = Math.max(0, rem / D * 100) + '%';
                    if (rem > 0) rafId = requestAnimationFrame(tick); else dismiss();
                }
                function pause()  { cancelAnimationFrame(rafId); lastTs = null; }
                function resume() { if (!gone) rafId = requestAnimationFrame(tick); }
                function dismiss() {
                    gone = true; cancelAnimationFrame(rafId);
                    el.classList.remove('toast-show'); el.classList.add('toast-hide');
                    setTimeout(function(){ el.parentNode && el.parentNode.removeChild(el); }, 300);
                }
                el.addEventListener('mouseenter', pause);
                el.addEventListener('mouseleave', resume);
                el.querySelector('.toast-x').addEventListener('click', dismiss);
                rafId = requestAnimationFrame(tick);
            }

            function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
            window.showToast = showToast;

            /* ── Custom confirm dialog ── */
            window.confirmDelete = function(msg, formEl) {
                var ov  = document.getElementById('confirm-overlay');
                var box = document.getElementById('confirm-box');
                document.getElementById('confirm-msg').textContent = msg;
                ov.classList.add('cd-show');
                var ok  = document.getElementById('confirm-btn-ok');
                var can = document.getElementById('confirm-btn-cancel');
                function cleanup(){ ov.classList.remove('cd-show'); ok.replaceWith(ok.cloneNode(true)); can.replaceWith(can.cloneNode(true)); ok = document.getElementById('confirm-btn-ok'); can = document.getElementById('confirm-btn-cancel'); }
                ok  = document.getElementById('confirm-btn-ok');
                can = document.getElementById('confirm-btn-cancel');
                ok.addEventListener('click', function(){ cleanup(); formEl.submit(); });
                can.addEventListener('click', cleanup);
                ov.addEventListener('click', function(e){ if(e.target===ov) cleanup(); }, {once:true});
                document.addEventListener('keydown', function(e){ if(e.key==='Escape') cleanup(); }, {once:true});
            };

            document.addEventListener('DOMContentLoaded', function () {
                @if(session('success'))       showToast('success', @json(session('success')));             @endif
                @if(session('error'))         showToast('error',   @json(session('error')));               @endif
                @if(session('warning'))       showToast('warning', @json(session('warning')));             @endif
                @if(session('info'))          showToast('info',    @json(session('info')));                @endif
                @if(session('email_success')) showToast('success', @json(session('email_success')));       @endif
                @if(session('email_error'))   showToast('error',   @json(session('email_error')));         @endif
                @if($errors->any())           showToast('error',   @json($errors->first()));               @endif
            });
        }());
        </script>

        @stack('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
        <script>
        /* ── Company info for PDF / share ── */
        window.companyInfo = {
            name:       @json($companyName),
            location:   @json($appSettings['company_location'] ?? ''),
            address:    @json($appSettings['company_address']  ?? ''),
            phone:      @json($appSettings['company_phone']    ?? ''),
            email:      @json($appSettings['company_email']    ?? ''),
            logoUrl:    @json($logoUrl),
            exportedBy: @json(auth()->user()->name ?? 'Unknown'),
        };

        /* Pre-load logo so exportPDF can draw it synchronously */
        window.__pdfLogoDataUrl = null;
        (function () {
            var url = (window.companyInfo || {}).logoUrl;
            if (!url) return;
            var img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function () {
                try {
                    var c = document.createElement('canvas');
                    c.width  = img.naturalWidth  || 200;
                    c.height = img.naturalHeight || 200;
                    c.getContext('2d').drawImage(img, 0, 0);
                    window.__pdfLogoDataUrl = c.toDataURL('image/png');
                } catch (e) { /* tainted canvas — skip */ }
            };
            img.src = url;
        }());

        /* ════════════════════════════════════════════════════════════════════
           DataTable — filter, sort, PDF export, WhatsApp & Email share
        ════════════════════════════════════════════════════════════════════ */
        (function () {
            'use strict';

            var ICONS = {
                search:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
                pdf:      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
                whatsapp: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/><path d="M12 2C6.477 2 2 6.479 2 12.009a9.96 9.96 0 0 0 1.385 5.07L2 22l5.085-1.352A9.96 9.96 0 0 0 12 22c5.523 0 10-4.479 10-9.991C22 6.479 17.523 2 12 2zm0 18.16a8.2 8.2 0 0 1-4.186-1.148l-.3-.178-3.118.828.831-3.059-.196-.314A8.19 8.19 0 0 1 3.84 12.01C3.84 7.497 7.49 3.84 12 3.84s8.162 3.657 8.162 8.169c0 4.513-3.65 8.151-8.162 8.151z"/></svg>',
                email:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
            };

            /* ── Helpers ── */
            function pageTitle() {
                var el = document.querySelector('.page-title');
                return el ? el.textContent.trim() : document.title;
            }

            function actionsColIdx(table) {
                var ths = table.querySelectorAll('thead th');
                for (var i = 0; i < ths.length; i++) {
                    if (ths[i].textContent.trim() === 'Actions') return i;
                }
                return -1;
            }

            function getHeaders(table) {
                var skip = actionsColIdx(table);
                return Array.from(table.querySelectorAll('thead th'))
                    .filter(function (_, i) { return i !== skip; })
                    .map(function (th) { return th.textContent.trim(); });
            }

            function visibleRows(table) {
                return Array.from(table.querySelectorAll('tbody tr')).filter(function (r) {
                    return !r.classList.contains('dt-hidden') &&
                           !r.classList.contains('empty-row') &&
                           !r.classList.contains('dt-no-match-row');
                });
            }

            function getFootData(table) {
                var skip = actionsColIdx(table);
                return Array.from(table.querySelectorAll('tfoot tr')).map(function (row) {
                    var result = [];
                    var colPos = 0;
                    Array.from(row.cells).forEach(function (td) {
                        var span = td.colSpan || 1;
                        if (colPos === skip) { colPos += span; return; }
                        if (td.classList.contains('no-export')) { colPos += span; return; }
                        var val;
                        if (td.hasAttribute('data-export')) {
                            val = td.getAttribute('data-export');
                        } else {
                            var clone = td.cloneNode(true);
                            clone.querySelectorAll('.no-export').forEach(function (el) { el.remove(); });
                            val = clone.textContent.trim();
                        }
                        result.push(val);
                        for (var c = 1; c < span; c++) result.push('');
                        colPos += span;
                    });
                    return result;
                });
            }

            function getRowData(table) {
                var skip = actionsColIdx(table);
                return visibleRows(table).map(function (row) {
                    return Array.from(row.cells)
                        .filter(function (_, i) { return i !== skip; })
                        .map(function (td) {
                            /* Explicit export value wins */
                            if (td.hasAttribute('data-export')) return td.getAttribute('data-export');
                            /* Clone, strip .no-export elements, read text */
                            var clone = td.cloneNode(true);
                            clone.querySelectorAll('.no-export').forEach(function (el) { el.remove(); });
                            return clone.textContent.trim();
                        });
                });
            }

            function tableToText(table, title) {
                var ci      = window.companyInfo || {};
                var headers = getHeaders(table);
                var rows    = getRowData(table);
                var date    = new Date().toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});
                var narrow  = headers.length <= 4;

                var lines = [
                    '🏭 *' + (ci.name || 'My Mine') + '*',
                    (ci.location ? '📍 ' + ci.location : ''),
                    '',
                    '📋 *' + title + '*',
                    '_' + date + '  ·  ' + rows.length + ' records_',
                    '',
                ].filter(function (l) { return l !== ''; });

                if (narrow) {
                    // Compact columnar format for tables with few columns
                    rows.forEach(function (r) {
                        var parts = headers.map(function (h, i) { return '*' + h + ':* ' + (r[i] || '—'); });
                        lines.push(parts.join('  ·  '));
                    });
                } else {
                    // Card-per-row format for wide tables
                    rows.forEach(function (r, ri) {
                        var card = headers.map(function (h, i) {
                            var val = (r[i] || '').toString().trim();
                            if (!val || val === '—') return null;
                            return '  *' + h + ':* ' + val;
                        }).filter(Boolean).join('\n');
                        lines.push(card);
                        if (ri < rows.length - 1) lines.push('');
                    });
                }

                lines.push('', '─────────────────', '_Total: ' + rows.length + ' records_');
                return lines.join('\n');
            }

            /* ── PDF Export ── */
            function exportPDF(table, title) {
                if (!window.jspdf || !window.jspdf.jsPDF) {
                    alert('PDF library is still loading — please try again in a moment.');
                    return;
                }
                var ci  = window.companyInfo || {};
                var doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                var W   = doc.internal.pageSize.getWidth();
                var rows = getRowData(table);

                /* Header — white background with gold accent bar */
                var HDR = 34;
                doc.setFillColor(255, 255, 255);
                doc.rect(0, 0, W, HDR, 'F');
                doc.setFillColor(252, 193, 4);
                doc.rect(0, HDR, W, 2, 'F');

                /* Logo — left-aligned, vertically centred in header */
                var textX = 14;
                if (window.__pdfLogoDataUrl) {
                    try {
                        var props  = doc.getImageProperties(window.__pdfLogoDataUrl);
                        var logoH  = 24;
                        var logoW  = Math.round(logoH * props.width / props.height);
                        doc.addImage(window.__pdfLogoDataUrl, 'PNG', 14, 5, logoW, logoH);
                        textX = 14 + logoW + 6;
                    } catch (e) { /* skip if image fails */ }
                }

                /* Company name */
                doc.setTextColor(0, 26, 77);
                doc.setFontSize(15); doc.setFont('helvetica', 'bold');
                doc.text(ci.name || 'My Mine', textX, 13);

                /* Location / address */
                doc.setFontSize(8); doc.setFont('helvetica', 'normal');
                doc.setTextColor(107, 114, 128);
                var sub = [ci.location, ci.address].filter(Boolean).join('   ·   ');
                if (sub) doc.text(sub, textX, 21);

                /* Phone / email */
                if (ci.phone || ci.email) {
                    doc.text([ci.phone, ci.email].filter(Boolean).join('   ·   '), textX, 28);
                }

                /* Record count — top-right */
                doc.setFontSize(8); doc.setFont('helvetica', 'normal');
                doc.setTextColor(107, 114, 128);
                doc.text(rows.length + ' records', W - 14, 13, { align: 'right' });

                /* Report title — below accent bar */
                doc.setTextColor(0, 26, 77);
                doc.setFontSize(12); doc.setFont('helvetica', 'bold');
                doc.text(title, 14, HDR + 10);

                /* Table */
                var footRows = getFootData(table);
                doc.autoTable({
                    head: [getHeaders(table)],
                    body: rows,
                    foot: footRows,
                    showFoot: 'lastPage',
                    startY: HDR + 15,
                    styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak', textColor: [30, 30, 30] },
                    headStyles: { fillColor: [252, 193, 4], textColor: [26, 26, 26], fontStyle: 'bold' },
                    footStyles: { fillColor: [0, 26, 77], textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [249, 250, 251] },
                    tableLineColor: [229, 231, 235], tableLineWidth: 0.1,
                    margin: { left: 14, right: 14 },
                    didDrawPage: function () {
                        var pH = doc.internal.pageSize.getHeight();
                        var pageNum = doc.getCurrentPageInfo().pageNumber;
                        var totalPages = doc.internal.getNumberOfPages();

                        /* Footer rule */
                        doc.setDrawColor(209, 213, 219);
                        doc.setLineWidth(0.3);
                        doc.line(14, pH - 14, W - 14, pH - 14);

                        doc.setFontSize(7); doc.setFont('helvetica', 'normal');

                        /* Left: company name — Confidential */
                        doc.setTextColor(107, 114, 128);
                        doc.text((ci.name || '') + '  —  Confidential', 14, pH - 10);

                        /* Centre: exported by */
                        doc.setTextColor(107, 114, 128);
                        doc.text('Exported by: ' + (ci.exportedBy || 'Unknown'), W / 2, pH - 10, { align: 'center' });

                        /* Right: page x of y */
                        doc.setTextColor(107, 114, 128);
                        doc.text('Page ' + pageNum + ' of ' + totalPages, W - 14, pH - 10, { align: 'right' });

                        /* Second line — generated datetime */
                        doc.setTextColor(156, 163, 175);
                        doc.text('Generated: ' + new Date().toLocaleString(), W - 14, pH - 6, { align: 'right' });
                    }
                });

                doc.save(title.replace(/[^a-z0-9]/gi, '_') + '_' + new Date().toISOString().slice(0, 10) + '.pdf');
            }

            /* ── WhatsApp Share ── */
            function shareWhatsApp(table, title) {
                var text = tableToText(table, title);
                window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
            }

            /* ── Email Share ── */
            function shareEmail(table, title) {
                var ci = window.companyInfo || {};
                var subject = (ci.name ? ci.name + ' — ' : '') + title;
                window.location.href = 'mailto:?subject=' + encodeURIComponent(subject) +
                    '&body=' + encodeURIComponent(tableToText(table, title));
            }

            /* ── Filter ── */
            function applyFilter(table, q) {
                var hasVisible = false;
                table.querySelectorAll('tbody tr:not(.dt-no-match-row):not(.empty-row)').forEach(function (row) {
                    var match = q === '' || row.textContent.toLowerCase().includes(q);
                    row.classList.toggle('dt-hidden', !match);
                    if (match) hasVisible = true;
                });
                var nmr = table.querySelector('.dt-no-match-row');
                if (!nmr) {
                    nmr = document.createElement('tr');
                    nmr.className = 'dt-no-match-row dt-hidden';
                    var td = document.createElement('td');
                    td.className = 'dt-no-match';
                    td.colSpan = table.querySelectorAll('thead th').length;
                    td.textContent = 'No matching records.';
                    nmr.appendChild(td);
                    table.querySelector('tbody').appendChild(nmr);
                }
                nmr.classList.toggle('dt-hidden', hasVisible || q === '');
            }

            /* ── Sort ── */
            function initSort(table) {
                table.querySelectorAll('thead th').forEach(function (th, idx) {
                    if (th.textContent.trim() === 'Actions') return;
                    th.classList.add('dt-sortable');
                    var dir = 1;
                    th.addEventListener('click', function () {
                        var tbody  = table.querySelector('tbody');
                        var rows   = Array.from(tbody.querySelectorAll('tr:not(.empty-row):not(.dt-no-match-row)'));
                        rows.sort(function (a, b) {
                            var ac = a.cells[idx], bc = b.cells[idx];
                            /* prefer data-sort attribute for reliable date/value sorting */
                            var aVal = ac ? (ac.dataset.sort !== undefined ? ac.dataset.sort : ac.textContent.trim()) : '';
                            var bVal = bc ? (bc.dataset.sort !== undefined ? bc.dataset.sort : bc.textContent.trim()) : '';
                            /* numeric (strip currency symbols, commas; parseFloat handles trailing units) */
                            var aN = parseFloat(aVal.replace(/[^0-9.\-]/g, ''));
                            var bN = parseFloat(bVal.replace(/[^0-9.\-]/g, ''));
                            if (!isNaN(aN) && !isNaN(bN)) return (aN - bN) * dir;
                            /* ISO date string (YYYY-MM-DD or YYYY-MM-DD HH:mm:ss) — compare as string */
                            if (/^\d{4}-\d{2}-\d{2}/.test(aVal) && /^\d{4}-\d{2}-\d{2}/.test(bVal)) {
                                return aVal.localeCompare(bVal) * dir;
                            }
                            return aVal.localeCompare(bVal) * dir;
                        });
                        rows.forEach(function (r) { tbody.appendChild(r); });
                        var nmr = tbody.querySelector('.dt-no-match-row');
                        if (nmr) tbody.appendChild(nmr);
                        table.querySelectorAll('thead th').forEach(function (t) {
                            t.classList.remove('dt-sort-asc', 'dt-sort-desc');
                        });
                        th.classList.add(dir === 1 ? 'dt-sort-asc' : 'dt-sort-desc');
                        dir *= -1;
                    });
                });
            }

            /* ── Per-card init ── */
            function initCard(card) {
                var table = card.querySelector('.data-table');
                if (!table) return;
                var title = pageTitle();

                var toolbar = document.createElement('div');
                toolbar.className = 'dt-toolbar';
                toolbar.innerHTML =
                    '<div class="dt-toolbar-left">' +
                    '  <div class="dt-search-wrap">' + ICONS.search +
                    '    <input type="search" class="dt-search" placeholder="Search table…">' +
                    '  </div>' +
                    '</div>' +
                    '<div class="dt-toolbar-right">' +
                    '  <button class="dt-btn dt-btn-pdf"      title="Export as PDF">' + ICONS.pdf      + ' PDF</button>' +
                    '  <button class="dt-btn dt-btn-whatsapp" title="Share via WhatsApp">' + ICONS.whatsapp + ' WhatsApp</button>' +
                    '  <button class="dt-btn dt-btn-email"    title="Share via Email">' + ICONS.email    + ' Email</button>' +
                    '</div>';

                card.insertBefore(toolbar, card.firstChild);

                toolbar.querySelector('.dt-search').addEventListener('input', function () {
                    applyFilter(table, this.value.trim().toLowerCase());
                });
                initSort(table);
                toolbar.querySelector('.dt-btn-pdf').addEventListener('click',      function () { exportPDF(table, title); });
                toolbar.querySelector('.dt-btn-whatsapp').addEventListener('click', function () { shareWhatsApp(table, title); });
                toolbar.querySelector('.dt-btn-email').addEventListener('click',    function () { shareEmail(table, title); });
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.data-card').forEach(initCard);
            });
        })();
        </script>
        <script>
        // ── Sidebar collapse ──
        (function(){
            var sidebar   = document.getElementById('sidebar');
            var topbar    = document.getElementById('topbar');
            var mainEl    = document.getElementById('mainContent');
            var overlay   = document.getElementById('sbOverlay');
            var WIDE = 240, SLIM = 64;

            function isMobile(){ return window.innerWidth < 1024; }

            function applyDesktop(collapsed){
                sidebar.classList.toggle('collapsed', collapsed);
                var w = collapsed ? SLIM : WIDE;
                topbar.style.left     = w + 'px';
                mainEl.style.marginLeft = w + 'px';
                localStorage.setItem('sbCollapsed', collapsed ? '1' : '0');
            }

            // Restore desktop state on load; on mobile ensure clean state
            if(isMobile()){
                sidebar.classList.remove('collapsed');
                topbar.style.left     = '';
                mainEl.style.marginLeft = '';
            } else if(localStorage.getItem('sbCollapsed') === '1'){
                sidebar.classList.add('collapsed');
                topbar.style.left     = SLIM + 'px';
                mainEl.style.marginLeft = SLIM + 'px';
            }

            // Nav groups are always expanded — no toggle behaviour needed

            // Topbar hamburger (mobile + desktop)
            document.getElementById('sidebarToggleBtn').addEventListener('click', function(e){
                e.stopPropagation();
                if(isMobile()){
                    var open = sidebar.classList.toggle('mobile-open');
                    overlay.classList.toggle('visible', open);
                } else {
                    applyDesktop(!sidebar.classList.contains('collapsed'));
                }
            });

            // Sidebar collapse button (desktop ◀ inside sidebar)
            var colBtn = document.getElementById('sidebarCollapseBtn');
            if(colBtn){
                colBtn.addEventListener('click', function(e){
                    e.stopPropagation();
                    if(!isMobile()) applyDesktop(!sidebar.classList.contains('collapsed'));
                });
            }

            // Close mobile sidebar on overlay click
            overlay.addEventListener('click', function(){
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('visible');
            });

            // Handle resize
            window.addEventListener('resize', function(){
                if(!isMobile()){
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('visible');
                    var c = localStorage.getItem('sbCollapsed') === '1';
                    sidebar.classList.toggle('collapsed', c);
                    topbar.style.left     = (c ? SLIM : WIDE) + 'px';
                    mainEl.style.marginLeft = (c ? SLIM : WIDE) + 'px';
                } else {
                    // On mobile: undo any desktop collapse, let CSS media query take over
                    sidebar.classList.remove('collapsed');
                    overlay.classList.remove('visible');
                    sidebar.classList.remove('mobile-open');
                    topbar.style.left     = '';
                    mainEl.style.marginLeft = '';
                }
            });
        })();

        // ── Dark mode ──
        // Theme is applied server-side (html.dark class) — no FOUC.
        // localStorage is kept in sync so the class can be re-applied
        // instantly if the layout is ever served unauthenticated.
        (function(){
            var saved = '{{ auth()->check() ? (auth()->user()->theme_preference ?? 'light') : 'light' }}';
            localStorage.setItem('theme', saved);
            if(saved==='dark'){
                document.getElementById('iconSun').style.display='none';
                document.getElementById('iconMoon').style.display='';
            }
        })();
        document.getElementById('darkToggle').addEventListener('click',function(){
            const isDark=document.documentElement.classList.toggle('dark');
            const theme=isDark?'dark':'light';
            localStorage.setItem('theme',theme);
            document.getElementById('iconSun').style.display=isDark?'none':'';
            document.getElementById('iconMoon').style.display=isDark?'':'none';
            // Persist to database (fire-and-forget, no visual feedback needed)
            fetch('{{ route("user.theme") }}',{
                method:'PATCH',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body:JSON.stringify({theme:theme})
            }).catch(function(){/* silently ignore network errors */});
        });

        // ── Dropdowns ──
        function toggleDropdown(menuId, btnId){
            const menu=document.getElementById(menuId);
            const isOpen=menu.classList.contains('open');
            document.querySelectorAll('.dropdown-menu.open').forEach(m=>m.classList.remove('open'));
            if(!isOpen) menu.classList.add('open');
        }
        document.getElementById('notifBtn').addEventListener('click',function(e){ e.stopPropagation(); toggleDropdown('notifMenu','notifBtn'); });
        document.getElementById('profileBtn').addEventListener('click',function(e){ e.stopPropagation(); toggleDropdown('profileMenu','profileBtn'); });
        document.addEventListener('click',function(){ document.querySelectorAll('.dropdown-menu.open').forEach(m=>m.classList.remove('open')); });

        // ── Notification mark-as-read ──
        const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function notifAjax(url, cb) {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken }
            }).then(r => r.json()).then(cb).catch(() => {});
        }

        function _decrementBadge(by) {
            const badge = document.getElementById('notifBadge');
            if (!badge) return;
            const cur = parseInt(badge.textContent) || 0;
            const next = Math.max(0, cur - by);
            if (next === 0) { badge.style.display = 'none'; badge.textContent = ''; }
            else { badge.textContent = next; }
        }

        function _dotToRead(id) {
            const dot = document.getElementById('dot-' + id);
            if (dot) dot.style.background = '#6b7280';
            const item = document.querySelector('[data-notif-id="' + id + '"]');
            if (item) { item.classList.add('notif-read'); item.style.cursor = 'default'; }
        }

        function notifClick(e, el, id, url) {
            e.stopPropagation();
            const wasUnread = !el.classList.contains('notif-read');
            if (wasUnread) {
                notifAjax('{{ route("notifications.read", ":id") }}'.replace(':id', id), function() {
                    _dotToRead(id);
                    _decrementBadge(1);
                    const allUnread = document.querySelectorAll('.notif-item:not(.notif-read)').length;
                    if (allUnread === 0) {
                        const btn = document.getElementById('markAllReadBtn');
                        if (btn) btn.style.display = 'none';
                    }
                });
            }
            if (url) { window.location.href = url; }
        }

        function notifMarkAll(e) {
            e.stopPropagation();
            notifAjax('{{ route("notifications.read-all") }}', function() {
                document.querySelectorAll('.notif-item').forEach(function(el) {
                    const id = el.dataset.notifId;
                    _dotToRead(id);
                    el.style.cursor = 'default';
                });
                const badge = document.getElementById('notifBadge');
                if (badge) { badge.style.display = 'none'; badge.textContent = ''; }
                const btn = document.getElementById('markAllReadBtn');
                if (btn) btn.style.display = 'none';
            });
        }

        // ── Search (client-side navigation hint) ──
        document.getElementById('topSearch').addEventListener('keydown',function(e){
            if(e.key==='Enter' && this.value.trim()){
                const q=this.value.trim().toLowerCase();
                const routes={
                    'production':'{{ route("production.index") }}',
                    'drilling':'{{ route("drilling.index") }}',
                    'blasting':'{{ route("blasting.index") }}',
                    'chemicals':'{{ route("chemicals.index") }}',
                    'consumables':'{{ route("consumables.index") }}',
                    'stores':'{{ route("consumables.index") }}',
                    'labour':'{{ route("labour-energy.index") }}',
                    'energy':'{{ route("labour-energy.index") }}',
                    'machines':'{{ route("machines.index") }}',
                    'assay':'{{ route("assay.index") }}',
                    'settings':'{{ route("settings.index") }}',
                    'report':'{{ route("reports.production") }}',
                    'dashboard':'{{ route("dashboard") }}',
                };
                for(const[k,v] of Object.entries(routes)){
                    if(q.includes(k)){ window.location.href=v; return; }
                }
            }
        });
        </script>

        @auth
        {{-- ── Inactivity session-timeout ─────────────────────────── --}}
        <div id="idle-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
            <div style="background:var(--card);border:1px solid var(--topbar-border);border-radius:16px;padding:32px 28px;max-width:400px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.5);">
                <div style="font-size:2.4rem;margin-bottom:10px;">&#9201;</div>
                <h2 style="font-size:1.1rem;font-weight:800;color:var(--text);margin:0 0 8px;">Session Expiring</h2>
                <p style="font-size:.85rem;color:#9ca3af;margin:0 0 20px;line-height:1.5;">
                    You've been inactive for a while. You'll be logged out in <strong id="idle-countdown" style="color:#fcb913;">60</strong> second(s).
                </p>
                <button id="idle-stay-btn" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 24px;background:#fcb913;color:#001a4d;border:none;border-radius:10px;font-size:.85rem;font-weight:800;cursor:pointer;letter-spacing:.03em;transition:opacity .15s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    Stay Logged In
                </button>
                <form id="idle-logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
                    @csrf
                </form>
            </div>
        </div>
        <script>
        (function(){
            var IDLE_MINUTES   = 25;          // show warning after 25 min idle
            var WARN_SECONDS   = 60;          // countdown before auto-logout
            var idleMs         = IDLE_MINUTES * 60 * 1000;
            var warnMs         = WARN_SECONDS * 1000;
            var idleTimer      = null;
            var warnTimer      = null;
            var countdownTimer = null;
            var modal          = document.getElementById('idle-modal');
            var countdown      = document.getElementById('idle-countdown');
            var stayBtn        = document.getElementById('idle-stay-btn');
            var logoutForm     = document.getElementById('idle-logout-form');

            function doLogout(){
                clearAllTimers();
                logoutForm.submit();
            }

            function clearAllTimers(){
                clearTimeout(idleTimer);
                clearTimeout(warnTimer);
                clearInterval(countdownTimer);
            }

            function showWarning(){
                modal.style.display = 'flex';
                var secs = WARN_SECONDS;
                countdown.textContent = secs;
                countdownTimer = setInterval(function(){
                    secs--;
                    countdown.textContent = secs;
                    if(secs <= 0){ clearInterval(countdownTimer); doLogout(); }
                }, 1000);
                warnTimer = setTimeout(doLogout, warnMs);
            }

            function resetIdle(){
                if(modal.style.display === 'flex') return; // warning showing — don't reset
                clearAllTimers();
                idleTimer = setTimeout(showWarning, idleMs);
            }

            stayBtn.addEventListener('click', function(){
                clearAllTimers();
                modal.style.display = 'none';
                // Ping the server to refresh the session
                fetch('{{ url('/') }}', { method:'HEAD', credentials:'same-origin' }).catch(function(){});
                resetIdle();
            });

            ['mousemove','keydown','mousedown','touchstart','scroll','click'].forEach(function(evt){
                document.addEventListener(evt, resetIdle, { passive:true, capture:true });
            });

            resetIdle(); // kick off on page load
        })();
        </script>
        @endauth

        {{-- PWA: Service Worker registration + install prompt --}}
        <script>
        (function () {
            // Register the service worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js', { scope: '/' })
                        .catch(function (err) { console.warn('SW registration failed:', err); });
                });
            }

            // Capture the install prompt and show the install button
            var deferredPrompt = null;
            var installBtn = document.getElementById('pwaInstallBtn');

            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                deferredPrompt = e;
                if (installBtn) installBtn.style.display = '';
            });

            window.addEventListener('appinstalled', function () {
                deferredPrompt = null;
                if (installBtn) installBtn.style.display = 'none';
            });

            if (installBtn) {
                installBtn.addEventListener('click', function () {
                    if (!deferredPrompt) return;
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function () {
                        deferredPrompt = null;
                        installBtn.style.display = 'none';
                    });
                });
            }
        }());
        </script>

    </body>
</html>
