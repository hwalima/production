<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $settings    = \Illuminate\Support\Facades\Cache::remember('app_settings', 600, fn() => \App\Models\Setting::all()->pluck('value', 'key'));
        $companyName = $settings['company_name'] ?? config('app.name', 'My Mine');
        $logoPath    = $settings['logo_path'] ?? '';
        $logoUrl     = $logoPath ? asset('storage/' . $logoPath) : null;
    @endphp
    <title>{{ $companyName }} — Application Documentation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Screen layout ── */
        :root {
            --gold:   #c8970b;
            --navy:   #001a4d;
            --navy2:  #002266;
            --light:  #f8f9fb;
            --border: #dde1ea;
            --text:   #1a2340;
            --muted:  #6b7280;
            --code:   #f1f5f9;
        }

        body {
            font-family: 'Figtree', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.7;
            color: var(--text);
            background: #fff;
        }

        /* ── Screen-only toolbar ── */
        .toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: var(--navy);
            color: #fff;
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .toolbar-left { display: flex; align-items: center; gap: 12px; }
        .toolbar-logo { height: 32px; }
        .toolbar-title { font-size: 1rem; font-weight: 600; color: #fff; }
        .toolbar-sub   { font-size: .75rem; color: rgba(255,255,255,.55); margin-top: 1px; }
        .btn-print {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--gold); color: #fff; font-weight: 700;
            border: none; border-radius: 8px; padding: 9px 20px;
            font-size: .85rem; cursor: pointer; text-decoration: none;
            transition: opacity .15s;
        }
        .btn-print:hover { opacity: .88; }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            color: rgba(255,255,255,.7); text-decoration: none;
            font-size: .82rem; transition: color .15s;
        }
        .btn-back:hover { color: #fff; }

        /* ── Page wrapper ── */
        .page-wrap {
            max-width: 960px;
            margin: 0 auto;
            padding: 100px 40px 80px;
        }

        /* ── Cover ── */
        .cover {
            text-align: center;
            padding: 60px 0 48px;
            border-bottom: 2px solid var(--border);
            margin-bottom: 56px;
        }
        .cover-logo { height: 72px; margin-bottom: 24px; }
        .cover-logo-placeholder {
            width: 72px; height: 72px;
            background: var(--navy);
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 2.2rem; margin-bottom: 24px;
        }
        .cover h1 {
            font-size: 2.2rem; font-weight: 700;
            color: var(--navy); line-height: 1.2; margin-bottom: 8px;
        }
        .cover h2 { font-size: 1.1rem; font-weight: 500; color: var(--muted); margin-bottom: 20px; }
        .cover-meta {
            display: inline-flex; gap: 24px; flex-wrap: wrap; justify-content: center;
            font-size: .82rem; color: var(--muted);
        }
        .cover-meta span { display: flex; align-items: center; gap: 5px; }

        /* ── TOC ── */
        .toc { background: var(--light); border: 1px solid var(--border); border-radius: 12px; padding: 28px 32px; margin-bottom: 56px; }
        .toc h2 { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 16px; letter-spacing: .04em; text-transform: uppercase; }
        .toc-grid { columns: 2; column-gap: 32px; }
        .toc-grid a { display: block; color: var(--navy2); text-decoration: none; font-size: .87rem; padding: 3px 0; }
        .toc-grid a:hover { color: var(--gold); }
        .toc-section { font-weight: 700; color: var(--muted); font-size: .72rem; text-transform: uppercase; letter-spacing: .07em; margin: 12px 0 4px; break-inside: avoid; }
        .toc-grid a.sub { padding-left: 14px; color: var(--muted); }

        /* ── Sections ── */
        section { margin-bottom: 56px; }
        .section-header {
            display: flex; align-items: center; gap: 12px;
            border-bottom: 2px solid var(--navy);
            padding-bottom: 10px; margin-bottom: 24px;
        }
        .section-icon {
            width: 36px; height: 36px;
            background: var(--navy); color: #fff;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .section-header h2 { font-size: 1.3rem; font-weight: 700; color: var(--navy); }
        .section-num { font-size: .82rem; color: var(--muted); margin-left: auto; }

        h3 { font-size: 1rem; font-weight: 700; color: var(--navy2); margin: 20px 0 8px; }
        h4 { font-size: .88rem; font-weight: 700; color: var(--text); margin: 14px 0 6px; }
        p  { margin-bottom: 10px; color: var(--text); }
        ul, ol { padding-left: 20px; margin-bottom: 10px; }
        li { margin-bottom: 4px; }
        strong { font-weight: 600; }

        /* ── Tables ── */
        .doc-table { width: 100%; border-collapse: collapse; font-size: .84rem; margin: 12px 0 20px; }
        .doc-table th { background: var(--navy); color: #fff; padding: 8px 12px; text-align: left; font-weight: 600; font-size: .78rem; letter-spacing: .03em; }
        .doc-table td { padding: 7px 12px; border-bottom: 1px solid var(--border); vertical-align: top; }
        .doc-table tr:nth-child(even) td { background: var(--light); }
        .doc-table td code { background: var(--code); padding: 1px 5px; border-radius: 3px; font-size: .82em; font-family: 'Courier New', monospace; }

        /* ── Callouts ── */
        .callout {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 12px 0 18px;
            font-size: .86rem;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .callout-icon { font-size: 1rem; flex-shrink: 0; line-height: 1.6; }
        .callout.info    { background: #eff6ff; border-left: 3px solid #3b82f6; }
        .callout.warn    { background: #fffbeb; border-left: 3px solid #f59e0b; }
        .callout.success { background: #f0fdf4; border-left: 3px solid #22c55e; }
        .callout.danger  { background: #fef2f2; border-left: 3px solid #ef4444; }

        /* ── Role badges ── */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: .74rem; font-weight: 700; letter-spacing: .04em; vertical-align: middle; }
        .badge-sa  { background: #7c3aed; color: #fff; }
        .badge-adm { background: var(--navy); color: #fff; }
        .badge-mgr { background: #0284c7; color: #fff; }
        .badge-vw  { background: #6b7280; color: #fff; }

        /* ── Code block ── */
        pre {
            background: #0f172a; color: #e2e8f0;
            padding: 16px 20px; border-radius: 8px;
            font-size: .8rem; font-family: 'Courier New', monospace;
            overflow-x: auto; margin: 10px 0 18px;
            line-height: 1.6;
        }
        code { font-family: 'Courier New', monospace; }
        .url { color: #93c5fd; }
        .method { color: #86efac; font-weight: 700; }
        .comment { color: #64748b; }

        /* ── Two-col layout ── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px; }
        .info-card { background: var(--light); border: 1px solid var(--border); border-radius: 10px; padding: 16px 18px; }
        .info-card h4 { margin-top: 0; color: var(--navy); }
        .info-card ul { margin-bottom: 0; }

        /* ── Field list ── */
        .field-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 6px; margin: 8px 0 16px; }
        .field-pill { background: var(--code); border: 1px solid var(--border); border-radius: 5px; padding: 4px 10px; font-size: .79rem; font-family: 'Courier New', monospace; }

        /* ── Footer ── */
        .doc-footer { border-top: 1px solid var(--border); padding-top: 24px; color: var(--muted); font-size: .78rem; display: flex; justify-content: space-between; align-items: center; }

        /* ── Print styles ── */
        @media print {
            .toolbar, .btn-print, .btn-back { display: none !important; }
            .page-wrap { padding: 0; max-width: 100%; }
            body { font-size: 11pt; }
            a { color: inherit; text-decoration: none; }
            .cover { padding: 40px 0 32px; }
            .cover h1 { font-size: 24pt; }
            section { page-break-inside: avoid; }
            .section-header { page-break-after: avoid; }
            h3 { page-break-after: avoid; }
            .doc-table { page-break-inside: auto; }
            .toc { page-break-after: always; }
            pre { white-space: pre-wrap; word-break: break-all; }
            .two-col { grid-template-columns: 1fr 1fr; }
        }
        @page { margin: 20mm 18mm; }
    </style>
</head>
<body>

{{-- ── Toolbar (screen only) ─────────────────────────────────────────────── --}}
<div class="toolbar">
    <div class="toolbar-left">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="toolbar-logo">
        @else
            <span style="font-size:1.4rem;">⛏️</span>
        @endif
        <div>
            <div class="toolbar-title">{{ $companyName }}</div>
            <div class="toolbar-sub">Application Documentation</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:16px;">
        <a href="{{ route('dashboard') }}" class="btn-back">← Back to App</a>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2m2 4h6a2 2 0 0 0 2-2v-4H7v4a2 2 0 0 0 2 2zm1-11V5a2 2 0 0 1 2-2h2l3 3v3"/></svg>
            Export as PDF
        </button>
    </div>
</div>

<div class="page-wrap">

    {{-- ── Cover ────────────────────────────────────────────────────────────── --}}
    <div class="cover">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="cover-logo">
        @else
            <div class="cover-logo-placeholder">⛏️</div>
        @endif
        <h1>{{ $companyName }}</h1>
        <h2>Mining Operations Management System — User & Technical Documentation</h2>
        <div class="cover-meta">
            <span>📅 Generated: {{ now()->format('d F Y') }}</span>
            <span>🏷️ Version: Laravel 11 / PHP 8.2</span>
            <span>🌐 {{ config('app.url') }}</span>
        </div>
    </div>

    {{-- ── Table of Contents ──────────────────────────────────────────────────── --}}
    <div class="toc" id="toc">
        <h2>Table of Contents</h2>
        <div class="toc-grid">
            <div class="toc-section">Overview</div>
            <a href="#intro">1. Introduction</a>
            <a href="#roles">2. Roles &amp; Permissions</a>
            <a href="#auth">3. Authentication</a>

            <div class="toc-section">Core Modules</div>
            <a href="#dashboard">4. Dashboard</a>
            <a href="#production">5. Daily Production</a>
            <a href="#drilling">6. Drilling</a>
            <a href="#blasting">7. Blasting</a>
            <a href="#assay">8. Assay Results</a>
            <a href="#consumables">9. Consumables &amp; Stock</a>
            <a href="#machines">10. Machine Runtime</a>
            <a href="#labour">11. Labour &amp; Energy Costs</a>
            <a href="#she">12. Safety (SHE)</a>
            <a href="#actions">13. Action Items</a>

            <div class="toc-section">Reporting &amp; Data</div>
            <a href="#reports">14. Reports &amp; PDF Export</a>
            <a href="#import">15. Bulk Import (CSV / Excel)</a>
            <a href="#api">16. REST API</a>

            <div class="toc-section">Administration</div>
            <a href="#users">17. User Management</a>
            <a href="#settings">18. System Settings</a>
            <a href="#shifts">19. Shifts</a>
            <a href="#sites">20. Mining Sites &amp; Departments</a>
            <a href="#notifications">21. Notifications</a>
            <a href="#kb">22. Knowledge Base</a>
            <a href="#maintenance">23. Maintenance Tools</a>
        </div>
    </div>

    {{-- ── 1. Introduction ────────────────────────────────────────────────────── --}}
    <section id="intro">
        <div class="section-header">
            <div class="section-icon">📋</div>
            <h2>1. Introduction</h2>
            <span class="section-num">§1</span>
        </div>
        <p>
            <strong>{{ $companyName }}</strong> is a web-based mining operations management system
            built on <strong>Laravel 11</strong> and <strong>PHP 8.2</strong>. It centralises daily
            production tracking, equipment management, safety reporting, cost accounting, and
            administrative workflows for a gold-mining operation.
        </p>
        <h3>System Architecture</h3>
        <div class="two-col">
            <div class="info-card">
                <h4>Technology Stack</h4>
                <ul>
                    <li>Backend: Laravel 11.x, PHP 8.2</li>
                    <li>Database: MySQL (production), SQLite (tests)</li>
                    <li>Frontend: Blade templates, Vite, Chart.js</li>
                    <li>Authentication: Laravel Breeze + 2FA</li>
                    <li>API Auth: Laravel Sanctum (token-based)</li>
                    <li>PDF: Browser print / DomPDF (reports)</li>
                    <li>Spreadsheet: PhpOffice/PhpSpreadsheet</li>
                </ul>
            </div>
            <div class="info-card">
                <h4>Deployment</h4>
                <ul>
                    <li>Hosting: cPanel (shared, PHP 8.2)</li>
                    <li>Server: wp16.domains.co.za</li>
                    <li>Deploy: GitHub webhook → <code>deploy.php</code></li>
                    <li>SSL: Cloudflare (HTTPS enforced)</li>
                    <li>Storage: cPanel file system, <code>storage/app/public</code></li>
                </ul>
            </div>
        </div>
        <h3>Module Map</h3>
        <p>The system is composed of 19 functional modules accessed via the sidebar navigation. Each module has create / read / update / delete (CRUD) operations gated by the user's role.</p>
    </section>

    {{-- ── 2. Roles & Permissions ─────────────────────────────────────────────── --}}
    <section id="roles">
        <div class="section-header">
            <div class="section-icon">🔒</div>
            <h2>2. Roles &amp; Permissions</h2>
            <span class="section-num">§2</span>
        </div>
        <p>Every user is assigned exactly one role. Roles are enforced via middleware on every route and at the controller level.</p>
        <table class="doc-table">
            <thead>
                <tr><th>Role</th><th>Badge</th><th>Create / Edit / Delete</th><th>Admin Screens</th><th>API</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>super_admin</strong></td>
                    <td><span class="badge badge-sa">Super Admin</span></td>
                    <td>✅ All modules</td>
                    <td>✅ Full access</td>
                    <td>✅</td>
                    <td>Can manage users, roles, settings, KB, maintenance</td>
                </tr>
                <tr>
                    <td><strong>admin</strong></td>
                    <td><span class="badge badge-adm">Admin</span></td>
                    <td>✅ All modules</td>
                    <td>✅ Full access</td>
                    <td>✅</td>
                    <td>Same as super_admin except cannot promote to super_admin</td>
                </tr>
                <tr>
                    <td><strong>manager</strong></td>
                    <td><span class="badge badge-mgr">Manager</span></td>
                    <td>✅ All modules</td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>Full data entry; no user/settings/KB admin screens</td>
                </tr>
                <tr>
                    <td><strong>viewer</strong></td>
                    <td><span class="badge badge-vw">Viewer</span></td>
                    <td>❌ Read-only</td>
                    <td>❌</td>
                    <td>✅</td>
                    <td>Can view all data and export reports; cannot create/edit/delete</td>
                </tr>
            </tbody>
        </table>
        <div class="callout warn">
            <span class="callout-icon">⚠️</span>
            <span>Attempting a write action as a viewer or non-admin attempting an admin action returns HTTP <strong>403 Forbidden</strong>. The middleware is applied at the route level — it cannot be bypassed from the UI.</span>
        </div>
        <h3>Middleware Reference</h3>
        <table class="doc-table">
            <thead><tr><th>Middleware</th><th>Allowed Roles</th><th>Applied To</th></tr></thead>
            <tbody>
                <tr><td><code>role:super_admin,admin</code></td><td>super_admin, admin</td><td>User management, settings, KB admin, maintenance, roles</td></tr>
                <tr><td><code>canWrite</code></td><td>super_admin, admin, manager</td><td>All create / store / update / destroy routes</td></tr>
                <tr><td><code>auth</code></td><td>Any authenticated user</td><td>All non-public routes</td></tr>
                <tr><td><code>auth:sanctum</code></td><td>Token holders (any role)</td><td>All <code>/api/v1/*</code> routes</td></tr>
            </tbody>
        </table>
    </section>

    {{-- ── 3. Authentication ───────────────────────────────────────────────────── --}}
    <section id="auth">
        <div class="section-header">
            <div class="section-icon">🔑</div>
            <h2>3. Authentication</h2>
            <span class="section-num">§3</span>
        </div>
        <h3>Login</h3>
        <p>Navigate to <code>/login</code>. Enter your email and password. On success you are redirected to <code>/dashboard</code>.</p>
        <div class="callout info">
            <span class="callout-icon">ℹ️</span>
            <span>Accounts can be deactivated by an admin. Deactivated accounts receive an error at login and cannot access the system even with a correct password.</span>
        </div>
        <h3>Two-Factor Authentication (2FA)</h3>
        <p>2FA is optional and TOTP-based (compatible with Google Authenticator, Authy, etc.).</p>
        <ol>
            <li>Go to <strong>Profile → Security</strong> to enable 2FA.</li>
            <li>Scan the QR code with your authenticator app.</li>
            <li>Confirm the 6-digit code to activate.</li>
            <li>Store the recovery codes in a safe place — they allow login if the authenticator is lost.</li>
            <li>After enabling, every login will require the 6-digit TOTP code after the password step.</li>
        </ol>
        <h3>Password Reset</h3>
        <p>Use <strong>Forgot Password</strong> on the login page. An email link is sent; it expires after 60 minutes. The link opens <code>/reset-password/{token}</code>.</p>
        <h3>Forced Password Change</h3>
        <p>An admin can flag a user's account with <em>force password change</em>. The user is redirected to <code>/password/change</code> on every login until they update their password.</p>
        <h3>Session & Security</h3>
        <ul>
            <li>Sessions are file-based, stored in <code>storage/framework/sessions</code>.</li>
            <li>CSRF tokens are embedded in every form — the server rejects tampered requests (HTTP 419).</li>
            <li>Idle users are automatically logged out after the configured session lifetime.</li>
        </ul>
        <h3>API Tokens</h3>
        <p>Go to <strong>Profile → API Tokens</strong> to generate a personal access token (Sanctum). Tokens are shown only once at creation. Include it in every API request as:</p>
        <pre><span class="method">Authorization</span>: Bearer &lt;your-token&gt;</pre>
    </section>

    {{-- ── 4. Dashboard ────────────────────────────────────────────────────────── --}}
    <section id="dashboard">
        <div class="section-header">
            <div class="section-icon">📊</div>
            <h2>4. Dashboard</h2>
            <span class="section-num">§4</span>
        </div>
        <p>Route: <code>GET /dashboard</code>. The dashboard aggregates all key metrics for the selected date range (defaults to the current calendar month).</p>
        <h3>KPI Cards</h3>
        <table class="doc-table">
            <thead><tr><th>Card</th><th>Metric</th><th>Formula / Source</th></tr></thead>
            <tbody>
                <tr><td>Ore Hoisted</td><td>Tonnes hoisted in range</td><td><code>SUM(ore_hoisted)</code> from daily_productions</td></tr>
                <tr><td>Ore Milled</td><td>Tonnes milled in range</td><td><code>SUM(ore_milled)</code></td></tr>
                <tr><td>Gold Smelted</td><td>Grams of gold produced</td><td><code>SUM(gold_smelted)</code></td></tr>
                <tr><td>Gold vs Target</td><td>% of monthly target achieved</td><td><code>(gold_smelted / gold_monthly_target) × 100</code></td></tr>
                <tr><td>Stripping Ratio</td><td>Waste-to-ore ratio</td><td><code>waste_hoisted / ore_hoisted</code></td></tr>
                <tr><td>Milling Efficiency</td><td>% of hoisted ore that was milled</td><td><code>(ore_milled / ore_hoisted) × 100</code></td></tr>
                <tr><td>Implied Grade</td><td>Gold grams per tonne milled</td><td><code>gold_smelted / ore_milled</code></td></tr>
                <tr><td>Gold Projected</td><td>End-of-month projection</td><td>Daily pace × days in month</td></tr>
                <tr><td>Machines Overdue</td><td>Equipment past service date</td><td>Count where <code>next_service_date &lt; today</code></td></tr>
                <tr><td>Avg Purity</td><td>Average gold purity %</td><td><code>AVG(purity_percentage)</code></td></tr>
            </tbody>
        </table>
        <h3>Charts</h3>
        <ul>
            <li><strong>Daily Gold Trend</strong> — line chart of <code>gold_smelted</code> per day.</li>
            <li><strong>Ore Hoisted vs Milled</strong> — grouped bar chart comparing hoisted vs milled per day.</li>
            <li><strong>Stripping Ratio Trend</strong> — line chart of daily stripping ratio.</li>
        </ul>
        <h3>Date Filter</h3>
        <p>Use the <em>From / To</em> date pickers at the top. The filter persists in the URL as <code>?from=YYYY-MM-DD&amp;to=YYYY-MM-DD</code>, so filtered views can be bookmarked or shared.</p>
        <h3>Mine Map</h3>
        <p>If <strong>mine_latitude</strong> and <strong>mine_longitude</strong> are configured in Settings, an interactive map (Leaflet.js) shows the mine location on the dashboard.</p>
    </section>

    {{-- ── 5. Daily Production ─────────────────────────────────────────────────── --}}
    <section id="production">
        <div class="section-header">
            <div class="section-icon">⚒️</div>
            <h2>5. Daily Production</h2>
            <span class="section-num">§5</span>
        </div>
        <p>Route: <code>GET /production</code>. Records the complete daily production cycle from ore hoisting through to gold smelting.</p>
        <h3>Data Fields</h3>
        <div class="field-grid">
            <div class="field-pill">date</div><div class="field-pill">shift</div><div class="field-pill">mining_site</div>
            <div class="field-pill">ore_hoisted</div><div class="field-pill">ore_hoisted_target</div>
            <div class="field-pill">waste_hoisted</div><div class="field-pill">uncrushed_stockpile</div>
            <div class="field-pill">ore_crushed</div><div class="field-pill">unmilled_stockpile</div>
            <div class="field-pill">ore_milled</div><div class="field-pill">ore_milled_target</div>
            <div class="field-pill">gold_smelted</div><div class="field-pill">purity_percentage</div>
            <div class="field-pill">fidelity_price</div>
        </div>
        <table class="doc-table">
            <thead><tr><th>Field</th><th>Unit</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>date</td><td>Date</td><td>Production date (YYYY-MM-DD)</td></tr>
                <tr><td>shift</td><td>Text</td><td>Day / Night / Morning / Afternoon — linked to configured shifts</td></tr>
                <tr><td>mining_site</td><td>Text</td><td>The active mining site / stope</td></tr>
                <tr><td>ore_hoisted / target</td><td>Tonnes</td><td>Actual vs target ore hoisted</td></tr>
                <tr><td>waste_hoisted</td><td>Tonnes</td><td>Waste rock removed</td></tr>
                <tr><td>uncrushed_stockpile</td><td>Tonnes</td><td>Ore waiting to be crushed</td></tr>
                <tr><td>ore_crushed</td><td>Tonnes</td><td>Ore through the crusher</td></tr>
                <tr><td>unmilled_stockpile</td><td>Tonnes</td><td>Crushed ore waiting for mill</td></tr>
                <tr><td>ore_milled / target</td><td>Tonnes</td><td>Actual vs target ore milled</td></tr>
                <tr><td>gold_smelted</td><td>Grams</td><td>Fine gold produced</td></tr>
                <tr><td>purity_percentage</td><td>%</td><td>Gold purity (0–100)</td></tr>
                <tr><td>fidelity_price</td><td>Currency</td><td>Gold price per gram (local currency)</td></tr>
            </tbody>
        </table>
        <h3>Shift Association</h3>
        <p>The shift dropdown is populated from the <strong>Shifts</strong> settings. A production record is tied to a shift, enabling shift-level analysis and reporting.</p>
        <h3>Production Calendar</h3>
        <p>Route: <code>GET /production/calendar</code>. Visual monthly calendar view of all production records. Days with records are highlighted; click a day to view or edit that record.</p>
        <div class="callout info">
            <span class="callout-icon">ℹ️</span>
            <span>Role required to create/edit: <span class="badge badge-mgr">Manager</span> or above. Viewers can browse all records.</span>
        </div>
    </section>

    {{-- ── 6. Drilling ──────────────────────────────────────────────────────────── --}}
    <section id="drilling">
        <div class="section-header">
            <div class="section-icon">🔩</div>
            <h2>6. Drilling</h2>
            <span class="section-num">§6</span>
        </div>
        <p>Route: <code>GET /drilling</code>. Tracks drilling operations including metres drilled, holes completed, and equipment hours.</p>
        <h3>Key Fields</h3>
        <ul>
            <li><strong>date</strong> — Drilling date</li>
            <li><strong>shift</strong> — Linked shift</li>
            <li><strong>metres_drilled</strong> — Total metres completed</li>
            <li><strong>holes_completed</strong> — Number of holes finished</li>
            <li><strong>equipment_hours</strong> — Machine hours consumed</li>
            <li><strong>drilling_site / area</strong> — Location within the mine</li>
            <li><strong>notes</strong> — Free-text observations</li>
        </ul>
        <p>Full CRUD is available. Records are listed in reverse-chronological order with filtering by date range and site.</p>
    </section>

    {{-- ── 7. Blasting ──────────────────────────────────────────────────────────── --}}
    <section id="blasting">
        <div class="section-header">
            <div class="section-icon">💥</div>
            <h2>7. Blasting</h2>
            <span class="section-num">§7</span>
        </div>
        <p>Route: <code>GET /blasting</code>. Records each blasting event with explosives quantities, hole counts, and fragmentation outcomes.</p>
        <h3>Key Fields</h3>
        <ul>
            <li><strong>date / shift</strong> — When the blast occurred</li>
            <li><strong>holes_blasted</strong> — Total number of holes detonated</li>
            <li><strong>explosive_mass_kg</strong> — Total explosives used (kg)</li>
            <li><strong>blast_area</strong> — Area/stope description</li>
            <li><strong>fragmentation_rating</strong> — Qualitative or quantitative fragmentation assessment</li>
            <li><strong>notes</strong> — Observations, misfires, delays</li>
        </ul>
        <div class="callout warn">
            <span class="callout-icon">⚠️</span>
            <span>Blasting records are linked to the SHE module. A blast event that results in an incident should also have a corresponding SHE indicator entry.</span>
        </div>
    </section>

    {{-- ── 8. Assay Results ─────────────────────────────────────────────────────── --}}
    <section id="assay">
        <div class="section-header">
            <div class="section-icon">🔬</div>
            <h2>8. Assay Results</h2>
            <span class="section-num">§8</span>
        </div>
        <p>Route: <code>GET /assay</code>. Stores laboratory assay values for ore samples. Assay results can be linked to a specific daily production record.</p>
        <h3>Data Fields</h3>
        <table class="doc-table">
            <thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>date</td><td>Date</td><td>Sample date</td></tr>
                <tr><td>type</td><td>Text</td><td>Sample type (e.g., channel, chip, core)</td></tr>
                <tr><td>description</td><td>Text</td><td>Sample location or reference</td></tr>
                <tr><td>assay_value</td><td>Decimal (g/t)</td><td>Gold grade in grams per tonne</td></tr>
                <tr><td>detection_limit</td><td>Decimal</td><td>Instrument detection limit</td></tr>
                <tr><td>daily_production_id</td><td>FK</td><td>Optional link to a production record</td></tr>
            </tbody>
        </table>
        <h3>Trends</h3>
        <p>Route: <code>GET /assay/trends</code> — Chart view showing assay value trends over time, useful for grade control and mine planning.</p>
    </section>

    {{-- ── 9. Consumables ──────────────────────────────────────────────────────── --}}
    <section id="consumables">
        <div class="section-header">
            <div class="section-icon">📦</div>
            <h2>9. Consumables &amp; Stock</h2>
            <span class="section-num">§9</span>
        </div>
        <p>Route: <code>GET /consumables</code>. Manages the mine's consumable inventory — from blasting agents to PPE — with real-time stock levels and low-stock alerts.</p>
        <h3>Categories</h3>
        <table class="doc-table">
            <thead><tr><th>Category Key</th><th>Label</th></tr></thead>
            <tbody>
                <tr><td><code>blasting</code></td><td>🧨 Blasting</td></tr>
                <tr><td><code>chemicals</code></td><td>⚗️ Chemicals</td></tr>
                <tr><td><code>mechanical</code></td><td>🔧 Mechanical</td></tr>
                <tr><td><code>ppe</code></td><td>🦺 PPE</td></tr>
                <tr><td><code>general</code></td><td>📦 General</td></tr>
            </tbody>
        </table>
        <h3>Consumable Master Fields</h3>
        <ul>
            <li><strong>name</strong> — Item name</li>
            <li><strong>category</strong> — One of the categories above</li>
            <li><strong>purchase_unit</strong> — How it is purchased (e.g., "box", "drum")</li>
            <li><strong>use_unit</strong> — How it is consumed (e.g., "each", "litre")</li>
            <li><strong>units_per_pack</strong> — Conversion factor</li>
            <li><strong>pack_cost</strong> — Cost per purchase unit</li>
            <li><strong>reorder_level</strong> — Alert threshold in use_units</li>
        </ul>
        <h3>Stock Movements</h3>
        <p>Every stock change is recorded as a movement. Directions:</p>
        <ul>
            <li><strong>in</strong> — Stock received (purchase, return)</li>
            <li><strong>out</strong> — Stock issued (consumed, issued to site)</li>
        </ul>
        <p>Current stock = <code>SUM(in quantities) − SUM(out quantities)</code>. This provides a full audit trail of every stock change.</p>
        <h3>Low-Stock Alerts</h3>
        <p>When a consumable's current stock drops at or below its reorder level, it is flagged as low stock. Use <strong>Send Low-Stock Alert</strong> (<code>POST /consumables/send-low-stock-alert</code>) to email all users who have opted into low-stock notifications.</p>
        <h3>Chemicals Module</h3>
        <p>Route: <code>GET /chemicals</code>. A separate CRUD module for chemical reagents used in gold processing (cyanide, lime, carbon, etc.) with dosage tracking.</p>
    </section>

    {{-- ── 10. Machine Runtime ──────────────────────────────────────────────────── --}}
    <section id="machines">
        <div class="section-header">
            <div class="section-icon">⚙️</div>
            <h2>10. Machine Runtime</h2>
            <span class="section-num">§10</span>
        </div>
        <p>Route: <code>GET /machines</code>. Tracks individual machine operating hours and service schedules.</p>
        <h3>Data Fields</h3>
        <table class="doc-table">
            <thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>machine_code</td><td>Text</td><td>Unique identifier / registration number</td></tr>
                <tr><td>description</td><td>Text</td><td>Machine name and type</td></tr>
                <tr><td>start_time</td><td>DateTime</td><td>Shift start timestamp</td></tr>
                <tr><td>end_time</td><td>DateTime</td><td>Shift end timestamp</td></tr>
                <tr><td>service_after_hours</td><td>Hours</td><td>Service interval (e.g., every 250 hours)</td></tr>
                <tr><td>next_service_date</td><td>Date</td><td>Calculated next service due date</td></tr>
            </tbody>
        </table>
        <h3>Service Alerts</h3>
        <p>The dashboard shows a count of machines that are <em>overdue for service</em> (next_service_date is in the past) and machines <em>due within 7 days</em>. Email alerts are sent automatically when a machine passes its service date.</p>
        <div class="callout warn">
            <span class="callout-icon">⚠️</span>
            <span>Machines overdue for service appear in red on the dashboard KPI cards. Address these promptly to avoid unplanned downtime.</span>
        </div>
    </section>

    {{-- ── 11. Labour & Energy ──────────────────────────────────────────────────── --}}
    <section id="labour">
        <div class="section-header">
            <div class="section-icon">⚡</div>
            <h2>11. Labour &amp; Energy Costs</h2>
            <span class="section-num">§11</span>
        </div>
        <p>Route: <code>GET /labour-energy</code>. Records daily operational costs across three categories.</p>
        <h3>Cost Categories</h3>
        <table class="doc-table">
            <thead><tr><th>Field</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>date</td><td>Cost date</td></tr>
                <tr><td>zesa_cost</td><td>Grid electricity cost (ZESA)</td></tr>
                <tr><td>diesel_cost</td><td>Diesel / generator fuel cost</td></tr>
                <tr><td>labour_cost</td><td>Total labour cost — automatically calculated from department breakdown</td></tr>
            </tbody>
        </table>
        <h3>Department Labour Breakdown</h3>
        <p>Each labour-energy record can have multiple department-level cost lines (<code>labour_dept_costs</code>). The parent <code>labour_cost</code> field is automatically synced as the sum of all department costs whenever a department line is saved.</p>
        <p>Use the department breakdown to allocate labour costs to specific mining departments (e.g., Underground, Processing, Admin) for cost centre reporting.</p>
    </section>

    {{-- ── 12. SHE ──────────────────────────────────────────────────────────────── --}}
    <section id="she">
        <div class="section-header">
            <div class="section-icon">🦺</div>
            <h2>12. Safety, Health &amp; Environment (SHE)</h2>
            <span class="section-num">§12</span>
        </div>
        <p>Route: <code>GET /she</code>. Centralises all safety and health tracking including incident recording, compliance requirements, and workforce health indicators.</p>
        <h3>SHE Indicators</h3>
        <p>Daily metrics per department:</p>
        <table class="doc-table">
            <thead><tr><th>Field</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>medical_injury_case</td><td>Medical treatment cases (non-LTI)</td></tr>
                <tr><td>fatal_incident</td><td>Fatalities (count)</td></tr>
                <tr><td>lti</td><td>Lost Time Injuries</td></tr>
                <tr><td>nlti</td><td>Non-Lost Time Injuries</td></tr>
                <tr><td>leave</td><td>Planned leave days</td></tr>
                <tr><td>offdays</td><td>Rest days / off days</td></tr>
                <tr><td>sick</td><td>Sick days</td></tr>
                <tr><td>iod</td><td>Injury on Duty</td></tr>
                <tr><td>awol</td><td>Absent without leave</td></tr>
                <tr><td>terminations</td><td>Employee terminations</td></tr>
            </tbody>
        </table>
        <h3>SHE Requirements</h3>
        <p>Route: <code>GET /she/requirements/edit</code>. Define compliance requirements and track whether they have been met. Each requirement can have checklist items with a pass/fail status per period.</p>
        <h3>PDF Export</h3>
        <p>Route: <code>GET /she/pdf</code>. Generates a printable SHE report for the selected date range.</p>
    </section>

    {{-- ── 13. Action Items ─────────────────────────────────────────────────────── --}}
    <section id="actions">
        <div class="section-header">
            <div class="section-icon">✅</div>
            <h2>13. Action Items</h2>
            <span class="section-num">§13</span>
        </div>
        <p>Route: <code>GET /action-items</code>. Task tracking linked to mining departments, with priority and due-date management.</p>
        <h3>Fields</h3>
        <table class="doc-table">
            <thead><tr><th>Field</th><th>Values / Type</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>comment</td><td>Text</td><td>Description of the action required</td></tr>
                <tr><td>priority</td><td>high / medium / low</td><td>Urgency level</td></tr>
                <tr><td>status</td><td>open / in_progress / completed</td><td>Current status</td></tr>
                <tr><td>due_date</td><td>Date</td><td>Deadline</td></tr>
                <tr><td>reported_date</td><td>Date</td><td>When the action was identified</td></tr>
                <tr><td>mining_department_id</td><td>FK</td><td>Responsible department</td></tr>
            </tbody>
        </table>
        <h3>Overdue Actions</h3>
        <p>Items with <code>status != 'completed'</code> and <code>due_date &lt; today</code> are flagged as overdue. The count of overdue items is shown on the dashboard and in the sidebar notification badge.</p>
        <h3>PDF Export</h3>
        <p>Route: <code>GET /action-items/pdf</code>. Generates a printable list of all action items filtered by status.</p>
    </section>

    {{-- ── 14. Reports ─────────────────────────────────────────────────────────── --}}
    <section id="reports">
        <div class="section-header">
            <div class="section-icon">📈</div>
            <h2>14. Reports &amp; PDF Export</h2>
            <span class="section-num">§14</span>
        </div>
        <p>The reports module generates pre-formatted reports for management and regulatory purposes.</p>
        <table class="doc-table">
            <thead><tr><th>Report</th><th>URL</th><th>PDF URL</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Production Report</td><td><code>/reports/production</code></td><td><code>/reports/production/pdf</code></td><td>Daily production summary with totals, averages, and trend charts for the selected range</td></tr>
                <tr><td>Consumables Report</td><td><code>/reports/consumables</code></td><td><code>/reports/consumables/pdf</code></td><td>Stock levels, movements, and cost summary per consumable category</td></tr>
                <tr><td>SHE Report</td><td>Embedded in SHE module</td><td><code>/she/pdf</code></td><td>Safety indicators and compliance status</td></tr>
                <tr><td>Action Items PDF</td><td>Embedded in Action Items</td><td><code>/action-items/pdf</code></td><td>Prioritised action items list</td></tr>
            </tbody>
        </table>
        <div class="callout info">
            <span class="callout-icon">ℹ️</span>
            <span>All PDF routes use the browser print mechanism. When you navigate to a <code>/pdf</code> URL, the browser print dialog opens automatically. Choose <strong>Save as PDF</strong> as the printer destination.</span>
        </div>
    </section>

    {{-- ── 15. Bulk Import ─────────────────────────────────────────────────────── --}}
    <section id="import">
        <div class="section-header">
            <div class="section-icon">📥</div>
            <h2>15. Bulk Import (CSV / Excel)</h2>
            <span class="section-num">§15</span>
        </div>
        <p>Route: <code>GET /import</code>. Import multiple records at once from a CSV or XLSX (Excel) file. Supported modules:</p>
        <table class="doc-table">
            <thead><tr><th>Module</th><th>URL</th><th>Template Download</th></tr></thead>
            <tbody>
                <tr><td>Daily Production</td><td><code>/import/production</code></td><td><code>/import/template/production</code></td></tr>
                <tr><td>Consumables</td><td><code>/import/consumables</code></td><td><code>/import/template/consumables</code></td></tr>
                <tr><td>Labour &amp; Energy</td><td><code>/import/labour-energy</code></td><td><code>/import/template/labour-energy</code></td></tr>
            </tbody>
        </table>
        <h3>Import Workflow</h3>
        <ol>
            <li>Download the CSV template for the target module.</li>
            <li>Fill in your data following the column headers and the notes column.</li>
            <li>Upload the completed file via the import page.</li>
            <li>The system validates every row and imports valid records.</li>
            <li>A summary shows how many rows were imported and lists any rows that failed validation with the reason.</li>
        </ol>
        <h3>Production Import Columns</h3>
        <div class="field-grid">
            <div class="field-pill">date (YYYY-MM-DD)</div><div class="field-pill">shift</div>
            <div class="field-pill">mining_site</div><div class="field-pill">ore_hoisted</div>
            <div class="field-pill">ore_hoisted_target</div><div class="field-pill">waste_hoisted</div>
            <div class="field-pill">uncrushed_stockpile</div><div class="field-pill">ore_crushed</div>
            <div class="field-pill">unmilled_stockpile</div><div class="field-pill">ore_milled</div>
            <div class="field-pill">ore_milled_target</div><div class="field-pill">gold_smelted</div>
            <div class="field-pill">purity_percentage</div><div class="field-pill">fidelity_price</div>
        </div>
        <div class="callout warn">
            <span class="callout-icon">⚠️</span>
            <span>Required fields must be present in every row or the row will be skipped. Maximum file size is 10 MB. Supports <code>.csv</code>, <code>.xlsx</code>, and <code>.xls</code> formats.</span>
        </div>
    </section>

    {{-- ── 16. REST API ─────────────────────────────────────────────────────────── --}}
    <section id="api">
        <div class="section-header">
            <div class="section-icon">🔌</div>
            <h2>16. REST API</h2>
            <span class="section-num">§16</span>
        </div>
        <p>A read-only JSON API for integration with dashboards, mobile apps, and third-party tools. All endpoints are prefixed with <code>/api/v1/</code> and require a <strong>Sanctum Bearer token</strong>.</p>
        <h3>Authentication</h3>
        <pre><span class="comment"># 1. Obtain a token (via the Profile → API Tokens UI, or programmatically)</span>
<span class="method">POST</span> <span class="url">/api/auth/token</span>
Content-Type: application/json
{ "email": "you@example.com", "password": "secret", "token_name": "my-app" }

<span class="comment"># Response: { "token": "1|abc123..." }</span>

<span class="comment"># 2. Use the token on every subsequent request</span>
<span class="method">GET</span> <span class="url">/api/v1/dashboard</span>
Authorization: Bearer 1|abc123...</pre>
        <h3>Endpoints</h3>
        <table class="doc-table">
            <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td><code>GET</code></td><td><code>/api/v1/dashboard</code></td><td>Aggregated KPIs and summary metrics</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/production</code></td><td>Daily production records (paginated, filterable by date range)</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/production/summary</code></td><td>Production totals and averages for a date range</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/consumables</code></td><td>All consumables with current stock levels</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/consumables/low-stock</code></td><td>Consumables at or below reorder level</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/drilling</code></td><td>Drilling records</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/blasting</code></td><td>Blasting records</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/machines</code></td><td>Machine runtime and service status</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/labour-energy</code></td><td>Labour and energy costs</td></tr>
                <tr><td><code>GET</code></td><td><code>/api/v1/action-items</code></td><td>Action items with status and priority</td></tr>
                <tr><td><code>POST</code></td><td><code>/api/auth/token</code></td><td>Issue a new API token</td></tr>
                <tr><td><code>DELETE</code></td><td><code>/api/auth/token</code></td><td>Revoke the authenticated token</td></tr>
            </tbody>
        </table>
        <h3>Query Parameters (pagination & filtering)</h3>
        <table class="doc-table">
            <thead><tr><th>Parameter</th><th>Example</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td><code>from</code></td><td><code>2026-01-01</code></td><td>Start date filter (YYYY-MM-DD)</td></tr>
                <tr><td><code>to</code></td><td><code>2026-01-31</code></td><td>End date filter (YYYY-MM-DD)</td></tr>
                <tr><td><code>per_page</code></td><td><code>25</code></td><td>Records per page (default 25, max 100)</td></tr>
                <tr><td><code>page</code></td><td><code>2</code></td><td>Page number</td></tr>
            </tbody>
        </table>
        <div class="callout info">
            <span class="callout-icon">ℹ️</span>
            <span>All API responses are JSON. Date-range filtering uses inclusive bounds on both ends. The API is read-only — there are no POST/PUT/DELETE endpoints on data resources.</span>
        </div>
    </section>

    {{-- ── 17. User Management ──────────────────────────────────────────────────── --}}
    <section id="users">
        <div class="section-header">
            <div class="section-icon">👤</div>
            <h2>17. User Management</h2>
            <span class="section-num">§17</span>
        </div>
        <p>Route: <code>GET /users</code>. Requires <span class="badge badge-adm">Admin</span> or above.</p>
        <h3>User Fields</h3>
        <table class="doc-table">
            <thead><tr><th>Field</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>name</td><td>Full display name</td></tr>
                <tr><td>email</td><td>Login email (unique)</td></tr>
                <tr><td>role</td><td>super_admin / admin / manager / viewer</td></tr>
                <tr><td>phone</td><td>Contact number (optional)</td></tr>
                <tr><td>job_title</td><td>Position in the organisation</td></tr>
                <tr><td>is_active</td><td>Whether the account can log in</td></tr>
                <tr><td>force_password_change</td><td>Forces password update on next login</td></tr>
            </tbody>
        </table>
        <h3>Operations</h3>
        <ul>
            <li><strong>Create</strong> — <code>GET /users/create</code> — set name, email, temporary password, role</li>
            <li><strong>Edit</strong> — <code>GET /users/{id}/edit</code> — update all fields except password</li>
            <li><strong>Toggle Active</strong> — <code>PATCH /users/{id}/toggle-active</code> — enable/disable login</li>
            <li><strong>Delete</strong> — <code>DELETE /users/{id}</code> — permanent removal (use toggle-active for suspension)</li>
        </ul>
        <h3>Role Assignment</h3>
        <p>Route: <code>PATCH /roles/{user}/assign</code>. Only <span class="badge badge-sa">Super Admin</span> can assign the <em>super_admin</em> role. Admins can assign any role except super_admin.</p>
    </section>

    {{-- ── 18. System Settings ──────────────────────────────────────────────────── --}}
    <section id="settings">
        <div class="section-header">
            <div class="section-icon">⚙️</div>
            <h2>18. System Settings</h2>
            <span class="section-num">§18</span>
        </div>
        <p>Route: <code>GET /settings</code>. Requires <span class="badge badge-adm">Admin</span> or above.</p>
        <h3>Configurable Settings</h3>
        <table class="doc-table">
            <thead><tr><th>Key</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>company_name</td><td>Name shown in header, reports, and emails</td></tr>
                <tr><td>company_email</td><td>Sender address for system emails</td></tr>
                <tr><td>company_phone</td><td>Contact number shown in reports</td></tr>
                <tr><td>company_address</td><td>Physical address shown in reports</td></tr>
                <tr><td>company_location</td><td>Location name for dashboard display</td></tr>
                <tr><td>logo_path</td><td>Uploaded company logo (shown in header and reports)</td></tr>
                <tr><td>gold_monthly_target</td><td>Monthly gold production target in grams</td></tr>
                <tr><td>mine_latitude / mine_longitude</td><td>GPS coordinates for the dashboard map widget</td></tr>
                <tr><td>smtp_*</td><td>Email server configuration (host, port, username, password)</td></tr>
            </tbody>
        </table>
        <h3>Test Email</h3>
        <p>Use <code>POST /settings/test-email</code> to send a test message to verify SMTP settings are working before relying on automated notifications.</p>
    </section>

    {{-- ── 19. Shifts ──────────────────────────────────────────────────────────── --}}
    <section id="shifts">
        <div class="section-header">
            <div class="section-icon">🕐</div>
            <h2>19. Shifts</h2>
            <span class="section-num">§19</span>
        </div>
        <p>Route: <code>GET /settings/shifts</code>. Requires <span class="badge badge-adm">Admin</span> or above.</p>
        <p>Shifts are the named work periods (e.g., <em>Day</em>, <em>Night</em>, <em>Afternoon</em>) used throughout the system to tag production, drilling, and blasting records.</p>
        <h3>Operations</h3>
        <ul>
            <li>Create, rename, and delete shifts.</li>
            <li>Toggle a shift active/inactive (<code>PATCH /settings/shifts/{id}/toggle</code>).</li>
            <li>Inactive shifts are hidden from data-entry dropdowns but existing records linked to them are preserved.</li>
        </ul>
    </section>

    {{-- ── 20. Mining Sites & Departments ─────────────────────────────────────── --}}
    <section id="sites">
        <div class="section-header">
            <div class="section-icon">🏔️</div>
            <h2>20. Mining Sites &amp; Departments</h2>
            <span class="section-num">§20</span>
        </div>
        <h3>Mining Sites</h3>
        <p>Route: <code>GET /settings/mining-sites</code>. Named locations within the mine (e.g., <em>Main Pit</em>, <em>Shaft 2</em>, <em>Open Cast</em>). Sites appear in production record dropdowns. Each can be toggled active/inactive.</p>
        <h3>Mining Departments</h3>
        <p>Route: <code>GET /settings/mining-departments</code>. Organisational units used in:</p>
        <ul>
            <li>Action item assignment</li>
            <li>SHE indicator recording (per-department daily metrics)</li>
            <li>Labour cost department breakdown</li>
        </ul>
        <p>Departments can also be toggled active/inactive. Inactive departments are hidden from data-entry forms.</p>
    </section>

    {{-- ── 21. Notifications ────────────────────────────────────────────────────── --}}
    <section id="notifications">
        <div class="section-header">
            <div class="section-icon">🔔</div>
            <h2>21. Notifications</h2>
            <span class="section-num">§21</span>
        </div>
        <p>The system sends automated email notifications for key events. Each user controls their own notification preferences.</p>
        <h3>Notification Types</h3>
        <table class="doc-table">
            <thead><tr><th>Event</th><th>Trigger</th><th>Who Can Opt In</th></tr></thead>
            <tbody>
                <tr><td>Low Stock Alert</td><td>Consumable stock ≤ reorder level</td><td>All roles</td></tr>
                <tr><td>Machine Service Due</td><td>Machine passes <code>next_service_date</code></td><td>All roles</td></tr>
                <tr><td>Overdue Action Items</td><td>Action item past due date &amp; not completed</td><td>All roles</td></tr>
                <tr><td>Production Target</td><td>Monthly gold target reached or missed</td><td>All roles</td></tr>
            </tbody>
        </table>
        <h3>Managing Preferences</h3>
        <p>Route: <code>GET /admin/notification-preferences</code>. Each user can toggle individual notification types on or off. Preferences are stored per-user in the database.</p>
    </section>

    {{-- ── 22. Knowledge Base ───────────────────────────────────────────────────── --}}
    <section id="kb">
        <div class="section-header">
            <div class="section-icon">📚</div>
            <h2>22. Knowledge Base</h2>
            <span class="section-num">§22</span>
        </div>
        <p>Route: <code>GET /help</code>. The built-in help system with 14 categories and 41 articles covering every aspect of the application.</p>
        <h3>Categories</h3>
        <div class="field-grid">
            <div class="field-pill">Getting Started</div><div class="field-pill">Daily Production</div>
            <div class="field-pill">Drilling &amp; Blasting</div><div class="field-pill">Stores &amp; Consumables</div>
            <div class="field-pill">Labour &amp; Energy</div><div class="field-pill">Machine Runtime</div>
            <div class="field-pill">Assay Results</div><div class="field-pill">Safety (SHE)</div>
            <div class="field-pill">Action Items</div><div class="field-pill">Reports &amp; Exports</div>
            <div class="field-pill">Bulk Import</div><div class="field-pill">API Access</div>
            <div class="field-pill">User Management</div><div class="field-pill">System Settings</div>
        </div>
        <h3>Search</h3>
        <p>Route: <code>GET /help/search?q=keyword</code>. Full-text search across all article titles and content. Requires a minimum of 2 characters.</p>
        <h3>Admin — Content Management</h3>
        <p>Route: <code>GET /admin/kb</code>. Requires <span class="badge badge-adm">Admin</span> or above.</p>
        <ul>
            <li>Create, edit, reorder, and delete categories.</li>
            <li>Create, edit, reorder, publish/unpublish, and delete articles.</li>
            <li>Articles use raw HTML with a live preview editor.</li>
            <li>Slugs are auto-generated from titles and deduplicated automatically.</li>
            <li>Unpublished articles are hidden from all non-admin users.</li>
        </ul>
    </section>

    {{-- ── 23. Maintenance Tools ────────────────────────────────────────────────── --}}
    <section id="maintenance">
        <div class="section-header">
            <div class="section-icon">🛠️</div>
            <h2>23. Maintenance Tools</h2>
            <span class="section-num">§23</span>
        </div>
        <p>Route: <code>GET /maintenance</code>. Requires <span class="badge badge-adm">Admin</span> or above.</p>
        <h3>Available Tools</h3>
        <table class="doc-table">
            <thead><tr><th>Tool</th><th>Route</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>Cache Clear</td><td><code>POST /maintenance/cache/clear</code></td><td>Clears application config, route, and view caches</td></tr>
                <tr><td>Audit Logs</td><td><code>GET /maintenance/audit-logs</code></td><td>View system audit trail — who changed what and when</td></tr>
                <tr><td>Purge Audit Logs</td><td><code>POST /maintenance/audit-logs/purge</code></td><td>Remove old audit log entries (irreversible)</td></tr>
                <tr><td>Login Logs</td><td><code>GET /maintenance/login-logs</code></td><td>View login history — successful and failed attempts, with IP and timestamp</td></tr>
                <tr><td>Purge Login Logs</td><td><code>POST /maintenance/login-logs/purge</code></td><td>Remove old login log entries</td></tr>
            </tbody>
        </table>
        <h3>Audit Trail</h3>
        <p>The audit log automatically records create / update / delete operations across all major modules. Each entry captures: the authenticated user, the action performed, the model type and ID, and a before/after snapshot of changed fields.</p>
        <div class="callout danger">
            <span class="callout-icon">🚨</span>
            <span>Purging logs is <strong>irreversible</strong>. Only purge old logs after confirming they are no longer needed for compliance or investigation purposes.</span>
        </div>
    </section>

    {{-- ── Footer ──────────────────────────────────────────────────────────────── --}}
    <div class="doc-footer">
        <span>{{ $companyName }} — Mining Operations System</span>
        <span>Generated {{ now()->format('d F Y') }} · Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</span>
    </div>

</div>{{-- /page-wrap --}}
</body>
</html>
