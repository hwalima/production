<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        $data = [

            // ── 1. Getting Started ──────────────────────────────────────────────
            [
                'title' => 'Getting Started', 'slug' => 'getting-started', 'icon' => '🚀', 'sort_order' => 1,
                'articles' => [
                    [
                        'title' => 'Introduction to MyMine',
                        'slug'  => 'introduction',
                        'content' => <<<'HTML'
<p><strong>MyMine</strong> is the mining operations management platform for Epoch Mines. It brings production data, stores, costs, safety, and reporting into one place so every team member has the information they need to make good decisions.</p>
<h2 id="modules">Key Modules <a class="kb-anchor" href="#modules">¶</a></h2>
<ul>
  <li><strong>Daily Production</strong> — Log ore hoisted, crushed, milled, and gold smelted per shift</li>
  <li><strong>Drilling &amp; Blasting</strong> — Record drill metres and blasting operations by date and site</li>
  <li><strong>Stores &amp; Consumables</strong> — Full inventory management with stock-in / stock-out movements</li>
  <li><strong>Labour &amp; Energy</strong> — Track ZESA, diesel, and labour costs with per-department breakdowns</li>
  <li><strong>Machine Runtime</strong> — Log daily machine hours and monitor service schedules</li>
  <li><strong>Assay Results</strong> — Record metallurgical assay data and view grade/recovery trends</li>
  <li><strong>SHE</strong> — Safety, health and environment indicators and compliance requirements</li>
  <li><strong>Action Items</strong> — Create, assign and track corrective/preventive actions</li>
  <li><strong>Analytics &amp; Insights</strong> — 13 KPI charts: mill recovery, AISC, SPC control charts, anomaly detection, predictive maintenance, and more</li>
  <li><strong>Reports &amp; PDF Export</strong> — Production, consumables, and accounts reports</li>
  <li><strong>Bulk Import</strong> — CSV / Excel import for historical data across all modules</li>
  <li><strong>API Access</strong> — Read-only JSON API for dashboards and third-party integrations</li>
</ul>
<h2 id="roles">User Roles <a class="kb-anchor" href="#roles">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Role</th><th>Access Level</th></tr></thead>
  <tbody>
    <tr><td><strong>Super Admin</strong></td><td>Full system access — user management, role assignment, all settings</td></tr>
    <tr><td><strong>Admin</strong></td><td>Full operational access plus settings; cannot reassign roles</td></tr>
    <tr><td><strong>Manager</strong></td><td>Create, edit, and delete all operational records</td></tr>
    <tr><td><strong>Viewer</strong></td><td>Read-only access to all data, dashboards, and reports</td></tr>
  </tbody>
</table>
<h2 id="help">Using This Knowledge Base <a class="kb-anchor" href="#help">¶</a></h2>
<p>Browse categories in the left sidebar to find the topic you need. Use the search bar at the top of the sidebar to find articles by keyword. Each article includes <strong>Previous</strong> and <strong>Next</strong> navigation at the bottom for sequential reading.</p>
HTML,
                    ],
                    [
                        'title' => 'Dashboard Overview',
                        'slug'  => 'dashboard-overview',
                        'content' => <<<'HTML'
<p>The Dashboard is your live summary of mine performance. It loads automatically after login and is always accessible from the top of the left sidebar.</p>
<h2 id="kpi">KPI Cards <a class="kb-anchor" href="#kpi">¶</a></h2>
<p>The four cards at the top of the dashboard show:</p>
<ul>
  <li><strong>Gold Today (g)</strong> — Gold smelted in the most recent production record</li>
  <li><strong>Gold MTD (g)</strong> — Total gold smelted from the 1st of the current month to today</li>
  <li><strong>MTD Target %</strong> — Month-to-date gold vs the monthly ore-milled target (colour-coded: green ≥ 80 %, amber ≥ 50 %, red &lt; 50 %)</li>
  <li><strong>Active Action Items</strong> — Open + in-progress action items; red badge when any are overdue</li>
</ul>
<h2 id="chart">Production Chart <a class="kb-anchor" href="#chart">¶</a></h2>
<p>The main chart shows daily gold smelted over the last 30 days. Hover a data point to see the exact date and value. The chart updates automatically when new production records are saved.</p>
<h2 id="panels">Quick-Glance Panels <a class="kb-anchor" href="#panels">¶</a></h2>
<ul>
  <li><strong>Low Stock</strong> — Consumable items below their reorder level</li>
  <li><strong>Overdue Machines</strong> — Machines where running hours to next service have reached zero</li>
  <li><strong>Recent Production</strong> — Last 5 production records with date, shift, and gold smelted</li>
</ul>
<div class="kb-callout kb-info"><strong>Tip:</strong> Clicking any panel item takes you directly to that record.</div>
HTML,
                    ],
                    [
                        'title' => 'Navigating the App',
                        'slug'  => 'navigating-the-app',
                        'content' => <<<'HTML'
<p>MyMine uses a persistent left sidebar for navigation and a topbar for quick actions. Understanding these two areas will help you move efficiently around the system.</p>
<h2 id="sidebar">Left Sidebar <a class="kb-anchor" href="#sidebar">¶</a></h2>
<p>The sidebar organises links into three groups:</p>
<ul>
  <li><strong>Operations</strong> — Daily Production, Drilling, Blasting, Chemicals, Stores, Labour &amp; Energy, Machines, Assay, SHE, Action Items</li>
  <li><strong>Reports</strong> — Production Report, Consumables Report, Accounts Report</li>
  <li><strong>Admin</strong> — Users, Settings, Departments, Roles, Maintenance (visible to Admin and above)</li>
</ul>
<p>Collapse the sidebar by clicking the hamburger menu (☰) in the topbar. In collapsed mode, only icons are shown and tooltips appear on hover.</p>
<h2 id="topbar">Topbar <a class="kb-anchor" href="#topbar">¶</a></h2>
<ul>
  <li><strong>☰ Hamburger</strong> — Toggles sidebar width</li>
  <li><strong>🔔 Bell icon</strong> — Opens the notification panel; unread count shown as a red badge</li>
  <li><strong>🌙 / ☀️ Theme toggle</strong> — Switches between dark and light mode; preference is saved to your account</li>
  <li><strong>Profile button</strong> — Your name/avatar; opens a dropdown with profile, settings, and logout links</li>
</ul>
<h2 id="mobile">Mobile Use <a class="kb-anchor" href="#mobile">¶</a></h2>
<p>On small screens the sidebar slides in as an overlay when you tap the hamburger. Tap the dark overlay to close it. All features are fully available on mobile.</p>
HTML,
                    ],
                    [
                        'title' => 'Your Profile & Account Settings',
                        'slug'  => 'profile-account',
                        'content' => <<<'HTML'
<p>All personal settings are under <strong>My Profile</strong>, accessible from the profile dropdown in the topbar or at <code>/profile</code>.</p>
<h2 id="details">Personal Details <a class="kb-anchor" href="#details">¶</a></h2>
<p>Update your <strong>name</strong>, <strong>email address</strong>, <strong>phone number</strong>, and <strong>job title</strong> from the Edit Profile tab. Click <em>Save Changes</em> to apply. Your email must be unique across all users.</p>
<h2 id="avatar">Profile Photo <a class="kb-anchor" href="#avatar">¶</a></h2>
<p>Upload a profile photo (JPG / PNG, max 2 MB) from the Edit Profile tab. Your photo appears in the topbar and in notification emails.</p>
<h2 id="password">Changing Your Password <a class="kb-anchor" href="#password">¶</a></h2>
<p>Go to the <em>Change Password</em> tab. Enter your current password, then your new password twice. Passwords must be at least 8 characters.</p>
<h2 id="theme">Dark / Light Mode <a class="kb-anchor" href="#theme">¶</a></h2>
<p>Use the ☀️ / 🌙 button in the topbar to toggle the theme. Your preference is saved to the database so it follows you on any device or browser.</p>
<h2 id="notifications">Notification Preferences <a class="kb-anchor" href="#notifications">¶</a></h2>
<p>Under <em>Notification Preferences</em> you can opt in or out of specific email alerts (low stock, machine overdue, action items due, etc.).</p>
<h2 id="api">API Tokens <a class="kb-anchor" href="#api">¶</a></h2>
<p>The <em>API Access</em> tab lets you create and revoke personal API tokens for use with the read-only JSON API. See the <strong>API Access</strong> category in this knowledge base for details.</p>
HTML,
                    ],
                ],
            ],

            // ── 2. Daily Production ─────────────────────────────────────────────
            [
                'title' => 'Daily Production', 'slug' => 'daily-production', 'icon' => '⛏️', 'sort_order' => 2,
                'articles' => [
                    [
                        'title' => 'Recording Daily Production',
                        'slug'  => 'recording-production',
                        'content' => <<<'HTML'
<p>Production records capture the ore and gold output for each shift. Navigate to <strong>Daily Production → Add Record</strong> (or click <em>Add Record</em> on the production index).</p>
<h2 id="fields">Form Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Field</th><th>Description</th><th>Required</th></tr></thead>
  <tbody>
    <tr><td>Date</td><td>Production date (today by default)</td><td>Yes</td></tr>
    <tr><td>Shift</td><td>Day or Night (or custom shift name)</td><td>No</td></tr>
    <tr><td>Mining Site</td><td>Which pit / level the ore came from</td><td>No</td></tr>
    <tr><td>Ore Hoisted (t)</td><td>Tonnes brought to surface</td><td>No</td></tr>
    <tr><td>Ore Hoisted Target (t)</td><td>Planned hoisting target for this shift</td><td>No</td></tr>
    <tr><td>Waste Hoisted (t)</td><td>Waste rock removed</td><td>No</td></tr>
    <tr><td>Uncrushed Stockpile (t)</td><td>Ore awaiting the crusher</td><td>No</td></tr>
    <tr><td>Ore Crushed (t)</td><td>Tonnes crushed this shift</td><td>No</td></tr>
    <tr><td>Unmilled Stockpile (t)</td><td>Crushed ore awaiting the mill</td><td>No</td></tr>
    <tr><td>Ore Milled (t)</td><td>Tonnes processed through the mill</td><td>No</td></tr>
    <tr><td>Ore Milled Target (t)</td><td>Planned milling target</td><td>No</td></tr>
    <tr><td>Gold Smelted (g)</td><td>Grams of gold recovered this shift</td><td>No</td></tr>
    <tr><td>Purity (%)</td><td>Gold purity of the smelted bar</td><td>No</td></tr>
    <tr><td>Fidelity Price ($/g)</td><td>Current gold price per gram</td><td>No</td></tr>
  </tbody>
</table>
<h2 id="upsert">Upsert Behaviour <a class="kb-anchor" href="#upsert">¶</a></h2>
<p>If a record for the same <strong>date + shift</strong> combination already exists, saving will <em>update</em> that record rather than creating a duplicate. This also applies during bulk import.</p>
<div class="kb-callout kb-info"><strong>Tip:</strong> Leave numeric fields blank (or zero) if they do not apply to the shift — they won't affect totals that are calculated from non-zero values.</div>
HTML,
                    ],
                    [
                        'title' => 'Production Calendar',
                        'slug'  => 'production-calendar',
                        'content' => <<<'HTML'
<p>The <strong>Production Calendar</strong> gives a month-at-a-glance view of all recorded production. Access it by clicking <em>Calendar</em> on the Daily Production index page.</p>
<h2 id="reading">Reading the Calendar <a class="kb-anchor" href="#reading">¶</a></h2>
<p>Each day that has at least one production record shows a coloured dot or badge. Multiple shifts on the same day appear as stacked badges. The badge displays the gold smelted for quick reference.</p>
<h2 id="navigate">Navigation <a class="kb-anchor" href="#navigate">¶</a></h2>
<p>Use the <strong>← / →</strong> arrows to move between months. Click any day cell to jump directly to the production records for that date. Days without records are clickable to open the Add Record form pre-filled with that date.</p>
<div class="kb-callout kb-tip"><strong>Tip:</strong> The calendar is useful for spotting missing records — blank days in an otherwise busy month stand out immediately.</div>
HTML,
                    ],
                    [
                        'title' => 'Production Targets',
                        'slug'  => 'production-targets',
                        'content' => <<<'HTML'
<p>Monthly production targets let management set expected ore and gold output goals. Navigate to <strong>Daily Production → Targets vs Actuals</strong>.</p>
<h2 id="setting">Setting Targets <a class="kb-anchor" href="#setting">¶</a></h2>
<p>Each target record covers one calendar month and year. You can set:</p>
<ul>
  <li><strong>Ore Hoisted Target (t)</strong> — Total tonnes planned to be hoisted for the month</li>
  <li><strong>Ore Milled Target (t)</strong> — Total tonnes planned to be milled for the month</li>
</ul>
<p>Create a target with <em>Add Target</em>. Only one target record per month/year is stored; editing overwrites it.</p>
<h2 id="dashboard">Targets on the Dashboard <a class="kb-anchor" href="#dashboard">¶</a></h2>
<p>The <strong>MTD Target %</strong> KPI card on the dashboard compares month-to-date ore milled against the current month's target. The colour coding provides an instant health check:</p>
<ul>
  <li>🟢 <strong>≥ 80 %</strong> — On or ahead of plan</li>
  <li>🟡 <strong>50–79 %</strong> — Behind but recoverable</li>
  <li>🔴 <strong>&lt; 50 %</strong> — Significantly behind plan</li>
</ul>
HTML,
                    ],
                    [
                        'title' => 'Working with Shifts',
                        'slug'  => 'shifts',
                        'content' => <<<'HTML'
<p>Shifts allow you to split production data into day/night (or other) time periods. Shift names are configured in <strong>Settings → Shifts</strong> by an Admin.</p>
<h2 id="assigning">Assigning a Shift <a class="kb-anchor" href="#assigning">¶</a></h2>
<p>When creating or editing a production record, select the applicable shift from the <em>Shift</em> dropdown. Leaving it blank records the data without a shift label — useful when shift-level breakdown is not tracked.</p>
<h2 id="breakdown">Per-Shift Statistics <a class="kb-anchor" href="#breakdown">¶</a></h2>
<p>The production index table shows records grouped by date. Where multiple shifts exist for the same date, each appears as a separate row. Reports can be filtered by shift to compare Day vs Night performance over time.</p>
<h2 id="config">Configuring Shifts <a class="kb-anchor" href="#config">¶</a></h2>
<p>Go to <strong>Settings → Shifts</strong> to add, rename, or deactivate shift names. Deactivating a shift hides it from new-record dropdowns but retains it on historical data.</p>
HTML,
                    ],
                ],
            ],

            // ── 3. Drilling & Blasting ──────────────────────────────────────────
            [
                'title' => 'Drilling & Blasting', 'slug' => 'drilling-blasting', 'icon' => '💥', 'sort_order' => 3,
                'articles' => [
                    [
                        'title' => 'Drilling Records',
                        'slug'  => 'drilling-records',
                        'content' => <<<'HTML'
<p>Drilling records capture the daily drilling activity at each mining site. Navigate to <strong>Drilling → Add Record</strong>.</p>
<h2 id="fields">Form Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<ul>
  <li><strong>Date</strong> — Date of the drilling activity</li>
  <li><strong>Shift</strong> — Day / Night (optional)</li>
  <li><strong>Mining Site</strong> — Location where drilling occurred</li>
  <li><strong>Holes Drilled</strong> — Number of blast holes completed</li>
  <li><strong>Drill Metres</strong> — Total metres advanced</li>
  <li><strong>Hole Depth (m)</strong> — Average or target depth per hole</li>
  <li><strong>Hole Diameter (mm)</strong> — Drill bit diameter used</li>
  <li><strong>Notes</strong> — Free-text notes (equipment problems, geology observations, etc.)</li>
</ul>
<h2 id="index">Viewing Records <a class="kb-anchor" href="#index">¶</a></h2>
<p>The drilling index lists records in reverse-chronological order. Filter by date range or site using the filter bar at the top. Click a record to view or edit it. Managers and above can delete records.</p>
HTML,
                    ],
                    [
                        'title' => 'Blasting Records',
                        'slug'  => 'blasting-records',
                        'content' => <<<'HTML'
<p>Blasting records log each blast event, including explosive type and charge weights. Navigate to <strong>Blasting → Add Record</strong>.</p>
<h2 id="fields">Form Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<ul>
  <li><strong>Date</strong> — Date of the blast</li>
  <li><strong>Shift</strong> — Day / Night</li>
  <li><strong>Mining Site</strong> — Blast location</li>
  <li><strong>Number of Blasts</strong> — How many separate blasts were fired</li>
  <li><strong>Total Explosive Charge (kg)</strong> — Total kilograms of explosive used</li>
  <li><strong>Explosive Type</strong> — ANFO, emulsion, etc.</li>
  <li><strong>Detonators Used</strong> — Count of detonators consumed</li>
  <li><strong>Notes</strong> — Additional observations</li>
</ul>
<div class="kb-callout kb-warning"><strong>Note:</strong> Blasting records are separate from consumable stock movements. If explosives are tracked in the Stores module, remember to record usage there separately.</div>
HTML,
                    ],
                ],
            ],

            // ── 4. Stores & Consumables ─────────────────────────────────────────
            [
                'title' => 'Stores & Consumables', 'slug' => 'stores-consumables', 'icon' => '📦', 'sort_order' => 4,
                'articles' => [
                    [
                        'title' => 'Managing the Consumables Catalog',
                        'slug'  => 'consumables-catalog',
                        'content' => <<<'HTML'
<p>The Consumables Catalog is the master list of every item held in the stores. Navigate to <strong>Stores → Add Item</strong> to add a new consumable.</p>
<h2 id="fields">Fields Explained <a class="kb-anchor" href="#fields">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Field</th><th>Description</th></tr></thead>
  <tbody>
    <tr><td>Name</td><td>Unique item name (e.g. "Atlas Copco drill bit 45mm")</td></tr>
    <tr><td>Category</td><td>One of: <em>blasting, chemicals, mechanical, ppe, general</em></td></tr>
    <tr><td>Description</td><td>Optional long description or part number</td></tr>
    <tr><td>Purchase Unit</td><td>How you buy it — e.g. "box", "drum", "bag"</td></tr>
    <tr><td>Use Unit</td><td>How you issue it — e.g. "each", "litre", "kg"</td></tr>
    <tr><td>Units per Pack</td><td>How many use-units are in one purchase unit</td></tr>
    <tr><td>Pack Cost ($)</td><td>Cost of one purchase unit</td></tr>
    <tr><td>Reorder Level</td><td>Stock quantity (in use-units) that triggers a low-stock alert</td></tr>
  </tbody>
</table>
<h2 id="unit-cost">Unit Cost Calculation <a class="kb-anchor" href="#unit-cost">¶</a></h2>
<p>The system automatically calculates <strong>unit cost = pack cost ÷ units per pack</strong> and stores it for use in cost reports. Keep <em>Units per Pack</em> and <em>Pack Cost</em> up to date to maintain accurate cost tracking.</p>
HTML,
                    ],
                    [
                        'title' => 'Receiving Stock',
                        'slug'  => 'receiving-stock',
                        'content' => <<<'HTML'
<p>When a purchase order arrives, record it as a stock-in movement against the relevant catalog item.</p>
<h2 id="steps">How to Receive Stock <a class="kb-anchor" href="#steps">¶</a></h2>
<ol>
  <li>Go to <strong>Stores</strong> and open the item by clicking its name.</li>
  <li>Click <em>Receive Stock</em>.</li>
  <li>Fill in the receive form:</li>
</ol>
<ul>
  <li><strong>Date</strong> — Date the goods were received</li>
  <li><strong>Packs Received</strong> — Number of purchase-unit packs received</li>
  <li><strong>Pack Cost ($)</strong> — Actual cost per pack for this delivery (may differ from catalog price)</li>
  <li><strong>Reference</strong> — Purchase order number or delivery note number</li>
  <li><strong>Notes</strong> — Supplier, batch number, condition, etc.</li>
</ul>
<p>4. Click <em>Save</em>. The stock level increases by <code>packs received × units per pack</code> (in use-units).</p>
<h2 id="history">Movement History <a class="kb-anchor" href="#history">¶</a></h2>
<p>All stock movements (in and out) are listed on the item detail page in reverse-chronological order. Each row shows the date, type (receive/use), quantity, reference, and which user recorded it.</p>
HTML,
                    ],
                    [
                        'title' => 'Recording Usage',
                        'slug'  => 'recording-usage',
                        'content' => <<<'HTML'
<p>When consumables are issued from the store, record a stock-out movement to keep the stock level accurate.</p>
<h2 id="steps">How to Record Usage <a class="kb-anchor" href="#steps">¶</a></h2>
<ol>
  <li>Go to <strong>Stores</strong> and open the item.</li>
  <li>Click <em>Use / Issue</em>.</li>
  <li>Enter: <strong>Date</strong>, <strong>Quantity Used</strong> (in use-units), and optional <strong>Notes</strong> (e.g. which section or machine it was used on).</li>
  <li>Click <em>Save</em>. The stock level decreases by the entered quantity.</li>
</ol>
<div class="kb-callout kb-warning"><strong>Warning:</strong> The system will allow usage that takes stock below zero to avoid blocking operations, but the negative balance will appear highlighted in red as a prompt to investigate.</div>
<h2 id="bulk">Bulk Usage Entry <a class="kb-anchor" href="#bulk">¶</a></h2>
<p>For end-of-day or end-of-week stock counts, consider using the <strong>Bulk Import</strong> feature to upload a spreadsheet of usage events rather than entering them individually. See the <em>Bulk Import</em> section of this knowledge base.</p>
HTML,
                    ],
                    [
                        'title' => 'Low-Stock Alerts',
                        'slug'  => 'low-stock-alerts',
                        'content' => <<<'HTML'
<p>The system monitors consumable stock levels and alerts users when items fall below their reorder threshold.</p>
<h2 id="visual">Visual Warnings <a class="kb-anchor" href="#visual">¶</a></h2>
<p>Items below their reorder level are shown with an amber <em>Low Stock</em> badge in the Stores index. A counter in the topbar notification area also shows the total number of low-stock items.</p>
<h2 id="email">Email Alert <a class="kb-anchor" href="#email">¶</a></h2>
<p>An Admin can click the <strong>Send Low-Stock Alert</strong> button on the Stores page to email all admin users a summary of items that need replenishing. This button has a 5-per-minute rate limit to prevent accidental spam. Users who have opted out of low-stock alerts in their Notification Preferences will not receive these emails.</p>
<h2 id="reorder">Adjusting Reorder Levels <a class="kb-anchor" href="#reorder">¶</a></h2>
<p>Edit an item and change the <em>Reorder Level</em> field. Set it to the quantity at which you need to have a purchase order placed so stock arrives before you run out, factoring in your typical lead time.</p>
HTML,
                    ],
                ],
            ],

            // ── 5. Labour & Energy ──────────────────────────────────────────────
            [
                'title' => 'Labour & Energy', 'slug' => 'labour-energy', 'icon' => '⚡', 'sort_order' => 5,
                'articles' => [
                    [
                        'title' => 'Recording Daily Costs',
                        'slug'  => 'recording-costs',
                        'content' => <<<'HTML'
<p>Labour &amp; Energy records capture the three main daily operating costs. Navigate to <strong>Labour &amp; Energy → Add Record</strong>.</p>
<h2 id="fields">Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<ul>
  <li><strong>Date</strong> — The cost date (one record per day; duplicate dates update the existing record)</li>
  <li><strong>ZESA Cost ($)</strong> — Electricity cost for the day from the ZESA grid</li>
  <li><strong>Diesel Cost ($)</strong> — Fuel cost for generators, haul trucks, and other diesel equipment</li>
  <li><strong>Labour Cost ($)</strong> — Total wages and salaries paid on the day</li>
</ul>
<div class="kb-callout kb-info"><strong>Tip:</strong> If your electricity and diesel costs are billed monthly, you can divide the bill by the number of working days and enter a daily average. Use the <em>Notes</em> field to indicate this.</div>
<h2 id="totals">Monthly Totals <a class="kb-anchor" href="#totals">¶</a></h2>
<p>The Labour &amp; Energy index shows a running monthly total at the top and individual daily rows below. The Accounts Report (under Reports) provides a detailed breakdown with charts.</p>
HTML,
                    ],
                    [
                        'title' => 'Department Breakdowns',
                        'slug'  => 'department-breakdowns',
                        'content' => <<<'HTML'
<p>If labour costs need to be split by department, you can add per-department entries to any daily Labour &amp; Energy record.</p>
<h2 id="steps">Adding a Department Entry <a class="kb-anchor" href="#steps">¶</a></h2>
<ol>
  <li>Open a Labour &amp; Energy record.</li>
  <li>Click <em>Add Department Entry</em> in the Department Breakdown section.</li>
  <li>Select the department from the dropdown (departments are managed in <strong>Settings → Departments</strong>).</li>
  <li>Enter the labour cost for that department.</li>
  <li>Click <em>Save</em>.</li>
</ol>
<h2 id="totals">Auto-Recalculation <a class="kb-anchor" href="#totals">¶</a></h2>
<p>The <em>Labour Cost</em> on the parent record is <strong>automatically recalculated</strong> as the sum of all department entries whenever you add, edit, or delete one. If no department entries exist, the labour cost field can be entered manually.</p>
<h2 id="departments">Configuring Departments <a class="kb-anchor" href="#departments">¶</a></h2>
<p>Department names are managed in <strong>Settings → Mining Departments</strong> (Admin/above). Toggle departments active/inactive to control which appear in the breakdown form.</p>
HTML,
                    ],
                ],
            ],

            // ── 6. Machine Runtime ──────────────────────────────────────────────
            [
                'title' => 'Machine Runtime', 'slug' => 'machine-runtime', 'icon' => '⚙️', 'sort_order' => 6,
                'articles' => [
                    [
                        'title' => 'Logging Machine Hours',
                        'slug'  => 'logging-machine-hours',
                        'content' => <<<'HTML'
<p>Machine runtime records track daily operating hours for each piece of equipment and help schedule preventive maintenance. Navigate to <strong>Machines → Add Record</strong>.</p>
<h2 id="fields">Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<ul>
  <li><strong>Date</strong> — Date the hours apply to</li>
  <li><strong>Machine Name</strong> — Name or asset number of the machine</li>
  <li><strong>Operating Hours</strong> — Productive running hours for the day</li>
  <li><strong>Idle Hours</strong> — Hours the engine was running but not productive</li>
  <li><strong>Breakdown Hours</strong> — Hours lost to unplanned stoppages</li>
  <li><strong>Running Hours to Next Service</strong> — Countdown of hours remaining until the next scheduled service</li>
  <li><strong>Last Service Date</strong> — Date of the most recent service (for reference)</li>
</ul>
<h2 id="tracking">Tracking Over Time <a class="kb-anchor" href="#tracking">¶</a></h2>
<p>The machines index shows the latest record for each machine, including the hours-to-next-service countdown. Colour coding: green (&gt; 50 h), amber (10–50 h), red (≤ 10 h or overdue).</p>
HTML,
                    ],
                    [
                        'title' => 'Machine Service Alerts',
                        'slug'  => 'machine-service-alerts',
                        'content' => <<<'HTML'
<p>MyMine automatically detects when a machine is overdue for service and alerts the team.</p>
<h2 id="trigger">When Is an Alert Triggered? <a class="kb-anchor" href="#trigger">¶</a></h2>
<p>A machine is considered overdue when its <em>Running Hours to Next Service</em> value reaches <strong>zero or below</strong> in the latest record. An email alert is sent to all admin users who have opted in to machine-overdue notifications.</p>
<h2 id="dashboard">Dashboard Indicator <a class="kb-anchor" href="#dashboard">¶</a></h2>
<p>The dashboard <em>Overdue Machines</em> panel lists all overdue equipment. The count is also cached and shown in the admin sidebar.</p>
<h2 id="reset">Resetting After a Service <a class="kb-anchor" href="#reset">¶</a></h2>
<p>After a service has been performed, add a new machine record with the updated <em>Running Hours to Next Service</em> (e.g. 250 for a 250-hour service interval) and update the <em>Last Service Date</em>. This resets the countdown and clears the overdue flag.</p>
HTML,
                    ],
                ],
            ],

            // ── 7. Assay Results ────────────────────────────────────────────────
            [
                'title' => 'Assay Results', 'slug' => 'assay-results', 'icon' => '🔬', 'sort_order' => 7,
                'articles' => [
                    [
                        'title' => 'Recording Assay Results',
                        'slug'  => 'recording-assay',
                        'content' => <<<'HTML'
<p>Assay records capture metallurgical laboratory results for each processing run. Navigate to <strong>Assay Results → Add Result</strong>.</p>
<h2 id="fields">Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<ul>
  <li><strong>Sample Date</strong> — Date the sample was collected</li>
  <li><strong>Head Grade (g/t)</strong> — Gold grade of the feed material</li>
  <li><strong>Recovery (%)</strong> — Percentage of gold recovered from the feed</li>
  <li><strong>Concentrate Grade (g/t)</strong> — Gold grade of the final concentrate or doré</li>
  <li><strong>Tail Grade (g/t)</strong> — Residual grade in the tailing</li>
  <li><strong>Notes</strong> — Sample ID, laboratory reference, process conditions, etc.</li>
</ul>
<p>Multiple assay results can be recorded for the same date (e.g. two shifts, two sample batches).</p>
HTML,
                    ],
                    [
                        'title' => 'Viewing Assay Trends',
                        'slug'  => 'assay-trends',
                        'content' => <<<'HTML'
<p>The Assay Trends view plots head grade, recovery, and concentrate grade over time, making it easy to spot process variations.</p>
<h2 id="access">Accessing Trends <a class="kb-anchor" href="#access">¶</a></h2>
<p>Click the <em>Trends</em> button on the Assay Results index page. The chart shows the last 90 days by default.</p>
<h2 id="filter">Filtering <a class="kb-anchor" href="#filter">¶</a></h2>
<p>Use the date-range picker at the top of the Trends page to zoom in on a specific period. The chart redraws automatically. Hover any data point to see the exact date and value.</p>
<div class="kb-callout kb-info"><strong>Tip:</strong> Compare the recovery trend against the head grade trend to check whether a drop in recovery is due to ore variability or process inefficiency.</div>
HTML,
                    ],
                ],
            ],

            // ── 8. Safety (SHE) ─────────────────────────────────────────────────
            [
                'title' => 'Safety (SHE)', 'slug' => 'safety-she', 'icon' => '🦺', 'sort_order' => 8,
                'articles' => [
                    [
                        'title' => 'SHE Indicators',
                        'slug'  => 'she-indicators',
                        'content' => <<<'HTML'
<p>Safety, Health and Environment (SHE) indicators are logged daily to maintain a record of mine safety performance. Navigate to <strong>SHE → Add Entry</strong>.</p>
<h2 id="fields">Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<ul>
  <li><strong>Date</strong> — Date the observation / incident occurred</li>
  <li><strong>Department</strong> — Department responsible</li>
  <li><strong>LTI (Lost Time Injury)</strong> — Number of injuries resulting in lost working time</li>
  <li><strong>FAI (First Aid Injury)</strong> — Injuries treated with first aid only</li>
  <li><strong>Near Misses</strong> — Incidents with potential for injury that did not result in injury</li>
  <li><strong>Property Damage</strong> — Number of property/equipment damage events</li>
  <li><strong>Notes</strong> — Description of incidents</li>
</ul>
<h2 id="dashboard">SHE Dashboard <a class="kb-anchor" href="#dashboard">¶</a></h2>
<p>The SHE index shows colour-coded totals for the current month (LTI in red, FAI in amber, near misses in blue, property damage in orange). A zero-LTI month is highlighted in green.</p>
HTML,
                    ],
                    [
                        'title' => 'Requirements & Compliance',
                        'slug'  => 'she-requirements',
                        'content' => <<<'HTML'
<p>SHE Requirements track regulatory and operational compliance items — inspections, certifications, training renewals, etc.</p>
<h2 id="adding">Adding a Requirement <a class="kb-anchor" href="#adding">¶</a></h2>
<p>Click <em>Add Requirement</em> on the SHE page. Fill in:</p>
<ul>
  <li><strong>Title</strong> — e.g. "Annual compressor inspection"</li>
  <li><strong>Due Date</strong> — When the requirement must be met</li>
  <li><strong>Responsible Person</strong> — Name of the person accountable</li>
  <li><strong>Status</strong> — Open, In Progress, or Met</li>
  <li><strong>Notes</strong> — Certificate number, authority, etc.</li>
</ul>
<h2 id="tracking">Tracking Status <a class="kb-anchor" href="#tracking">¶</a></h2>
<p>Overdue requirements (past due date and not Met) are highlighted in red. The SHE index summary shows open vs overdue counts. Export the requirements list to PDF for inspection reports.</p>
HTML,
                    ],
                ],
            ],

            // ── 9. Action Items ─────────────────────────────────────────────────
            [
                'title' => 'Action Items', 'slug' => 'action-items', 'icon' => '✅', 'sort_order' => 9,
                'articles' => [
                    [
                        'title' => 'Creating & Managing Action Items',
                        'slug'  => 'managing-action-items',
                        'content' => <<<'HTML'
<p>Action items are used to track corrective or preventive actions arising from production issues, safety observations, audits, or management meetings. Navigate to <strong>Action Items → Add Item</strong>.</p>
<h2 id="fields">Fields <a class="kb-anchor" href="#fields">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Field</th><th>Description</th></tr></thead>
  <tbody>
    <tr><td>Title</td><td>Short description of the action required</td></tr>
    <tr><td>Description</td><td>Detailed explanation, context, or corrective measures</td></tr>
    <tr><td>Priority</td><td>Low / Medium / High / Critical</td></tr>
    <tr><td>Due Date</td><td>Date by which the action must be completed</td></tr>
    <tr><td>Assigned To</td><td>User responsible for completing the action</td></tr>
    <tr><td>Status</td><td>Open → In Progress → Closed</td></tr>
  </tbody>
</table>
<h2 id="lifecycle">Lifecycle <a class="kb-anchor" href="#lifecycle">¶</a></h2>
<p>Items are created with status <em>Open</em>. Move them to <em>In Progress</em> when work has started, and <em>Closed</em> when completed. Closed items are archived from the default view but remain searchable.</p>
<h2 id="overdue">Overdue Items <a class="kb-anchor" href="#overdue">¶</a></h2>
<p>An item is <strong>overdue</strong> when its due date has passed and its status is not Closed. Overdue items are highlighted in red on the index. The dashboard shows the overdue count, and a red badge appears on the sidebar Action Items link.</p>
HTML,
                    ],
                ],
            ],

            // ── 10. Reports & Exports ────────────────────────────────────────────
            [
                'title' => 'Reports & Exports', 'slug' => 'reports-exports', 'icon' => '📊', 'sort_order' => 10,
                'articles' => [
                    [
                        'title' => 'Production Report',
                        'slug'  => 'production-report',
                        'content' => <<<'HTML'
<p>The Production Report provides a detailed view of ore and gold output across any date range. Navigate to <strong>Reports → Production Report</strong>.</p>
<h2 id="filters">Filters <a class="kb-anchor" href="#filters">¶</a></h2>
<ul>
  <li><strong>Date From / Date To</strong> — Limit the report to a specific period (default: current month)</li>
  <li><strong>Shift</strong> — Filter by a single shift or show all shifts</li>
  <li><strong>Mining Site</strong> — Limit to records from a specific site</li>
</ul>
<h2 id="summary">Summary Section <a class="kb-anchor" href="#summary">¶</a></h2>
<p>Below the filters, summary cards show: total ore hoisted, total ore milled, total gold smelted, average purity, and total estimated revenue (gold × fidelity price).</p>
<h2 id="table">Detail Table <a class="kb-anchor" href="#table">¶</a></h2>
<p>Each row represents one shift-day record. Columns include all production fields plus a calculated <em>gold value ($)</em> column. Click any row to jump to the full record.</p>
<h2 id="export">Exporting <a class="kb-anchor" href="#export">¶</a></h2>
<p>Click <em>Export PDF</em> to download a formatted PDF report with the current filters applied. See <em>Exporting to PDF</em> in this section for tips.</p>
HTML,
                    ],
                    [
                        'title' => 'Consumables Report',
                        'slug'  => 'consumables-report',
                        'content' => <<<'HTML'
<p>The Consumables Report analyses stock usage and cost for the selected period. Navigate to <strong>Reports → Consumables Report</strong>.</p>
<h2 id="overview">Report Overview <a class="kb-anchor" href="#overview">¶</a></h2>
<ul>
  <li><strong>Total Spend</strong> — Sum of all stock-out movements (quantity × unit cost) in the period</li>
  <li><strong>Usage by Category</strong> — Pie chart and table breaking spend down by category (blasting, chemicals, mechanical, ppe, general)</li>
  <li><strong>Top Items by Cost</strong> — Top 10 items by total cost in the period</li>
</ul>
<h2 id="filters">Filters <a class="kb-anchor" href="#filters">¶</a></h2>
<p>Use the date-range picker and the category dropdown to narrow the report. PDF export applies the current filters.</p>
HTML,
                    ],
                    [
                        'title' => 'Accounts Report',
                        'slug'  => 'accounts-report',
                        'content' => <<<'HTML'
<p>The Accounts Report shows all operating costs (ZESA, diesel, labour) by month. Navigate to <strong>Reports → Accounts Report</strong>.</p>
<h2 id="breakdown">Cost Breakdown <a class="kb-anchor" href="#breakdown">¶</a></h2>
<p>The report shows monthly bars with ZESA, diesel, and labour stacked. Summary cards at the top show period totals. The bottom section shows daily detail for the selected month.</p>
<h2 id="dept">Department Labour Breakdown <a class="kb-anchor" href="#dept">¶</a></h2>
<p>If department entries have been recorded, a second table on the report shows the labour cost per department for the period, enabling allocation analysis.</p>
HTML,
                    ],
                    [
                        'title' => 'Exporting to PDF',
                        'slug'  => 'exporting-pdf',
                        'content' => <<<'HTML'
<p>Most report pages and several index pages have a <strong>PDF</strong> or <em>Export PDF</em> button that generates a formatted, printable PDF.</p>
<h2 id="how">How It Works <a class="kb-anchor" href="#how">¶</a></h2>
<p>PDFs are generated server-side using Laravel DomPDF. They include the company name and logo (if configured in Settings), the applied filters, and all table data visible on the page.</p>
<h2 id="tips">Tips <a class="kb-anchor" href="#tips">¶</a></h2>
<ul>
  <li>Apply filters before clicking Export — only filtered data is included in the PDF.</li>
  <li>If your browser blocks the download, allow pop-ups for <code>production.epochmines.co.zw</code>.</li>
  <li>Large reports (thousands of rows) may take a few seconds to generate.</li>
  <li>PDF export is rate-limited to 10 requests per minute to protect server resources.</li>
</ul>
<h2 id="logo">Adding Your Logo to PDFs <a class="kb-anchor" href="#logo">¶</a></h2>
<p>Upload a company logo in <strong>Settings → Company</strong>. The logo will appear in the header of all generated PDFs.</p>
HTML,
                    ],
                ],
            ],

            // ── 11. Bulk Import ─────────────────────────────────────────────────
            [
                'title' => 'Bulk Import', 'slug' => 'bulk-import', 'icon' => '📤', 'sort_order' => 11,
                'articles' => [
                    [
                        'title' => 'Import Overview',
                        'slug'  => 'import-overview',
                        'content' => <<<'HTML'
<p>The Bulk Import feature lets you load historical or batch data from <strong>CSV</strong> or <strong>Excel (.xlsx)</strong> files, avoiding the need to enter records one by one.</p>
<h2 id="access">Accessing Import <a class="kb-anchor" href="#access">¶</a></h2>
<p>Go to the import hub at <code>/import</code>, or click the <em>Import</em> button on the Production, Stores, or Labour &amp; Energy index pages. Import is available to Managers and above.</p>
<h2 id="rules">File Rules <a class="kb-anchor" href="#rules">¶</a></h2>
<ul>
  <li>Maximum file size: <strong>10 MB</strong></li>
  <li>Accepted formats: <code>.csv</code>, <code>.xlsx</code>, <code>.xls</code></li>
  <li>The <strong>first row must contain column headers</strong> (case-insensitive, spaces/underscores interchangeable)</li>
  <li>Dates must be formatted as <code>YYYY-MM-DD</code></li>
  <li>Blank rows are skipped automatically</li>
</ul>
<h2 id="transactions">Transactional Safety <a class="kb-anchor" href="#transactions">¶</a></h2>
<p>Each import runs inside a database transaction. If a fatal error occurs, <strong>no data is saved</strong>. Rows with validation errors are skipped and reported; all valid rows are always saved. The result page shows a summary of rows inserted, updated, and skipped.</p>
<div class="kb-callout kb-info"><strong>Tip:</strong> Download the template from the import page to get a pre-formatted file with example data and column notes.</div>
HTML,
                    ],
                    [
                        'title' => 'Importing Production Records',
                        'slug'  => 'import-production',
                        'content' => <<<'HTML'
<p>Use this import to load large volumes of historical production data in one upload.</p>
<h2 id="columns">Columns <a class="kb-anchor" href="#columns">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
  <tbody>
    <tr><td>date</td><td>Yes</td><td>YYYY-MM-DD</td></tr>
    <tr><td>shift</td><td>No</td><td>Day / Night or custom shift name</td></tr>
    <tr><td>mining_site</td><td>No</td><td>Free text</td></tr>
    <tr><td>ore_hoisted</td><td>No</td><td>Tonnes; decimal allowed</td></tr>
    <tr><td>ore_hoisted_target</td><td>No</td><td>Tonnes</td></tr>
    <tr><td>waste_hoisted</td><td>No</td><td>Tonnes</td></tr>
    <tr><td>uncrushed_stockpile</td><td>No</td><td>Defaults to 0</td></tr>
    <tr><td>ore_crushed</td><td>No</td><td>Tonnes</td></tr>
    <tr><td>unmilled_stockpile</td><td>No</td><td>Defaults to 0</td></tr>
    <tr><td>ore_milled</td><td>No</td><td>Tonnes</td></tr>
    <tr><td>ore_milled_target</td><td>No</td><td>Tonnes</td></tr>
    <tr><td>gold_smelted</td><td>No</td><td>Grams</td></tr>
    <tr><td>purity_percentage</td><td>No</td><td>0–100</td></tr>
    <tr><td>fidelity_price</td><td>No</td><td>$/gram</td></tr>
  </tbody>
</table>
<h2 id="upsert">Upsert Key <a class="kb-anchor" href="#upsert">¶</a></h2>
<p>Records with the same <strong>date + shift</strong> as an existing record are <em>updated</em>; records with a new combination are <em>inserted</em>. This makes it safe to re-upload files that include already-imported data.</p>
HTML,
                    ],
                    [
                        'title' => 'Importing Consumables',
                        'slug'  => 'import-consumables',
                        'content' => <<<'HTML'
<p>The consumables import loads or updates your catalog of store items (not stock movements — use Receive and Use for those).</p>
<h2 id="columns">Required Columns <a class="kb-anchor" href="#columns">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
  <tbody>
    <tr><td>name</td><td>Yes</td><td>Must be unique; existing items with the same name are updated</td></tr>
    <tr><td>category</td><td>Yes</td><td>Must be: blasting, chemicals, mechanical, ppe, or general</td></tr>
    <tr><td>purchase_unit</td><td>Yes</td><td>e.g. box, drum, bag</td></tr>
    <tr><td>use_unit</td><td>Yes</td><td>e.g. each, litre, kg</td></tr>
    <tr><td>units_per_pack</td><td>Yes</td><td>Positive number</td></tr>
    <tr><td>pack_cost</td><td>Yes</td><td>Cost in dollars</td></tr>
    <tr><td>reorder_level</td><td>No</td><td>In use-units; defaults to 0</td></tr>
    <tr><td>description</td><td>No</td><td>Free text</td></tr>
  </tbody>
</table>
<div class="kb-callout kb-warning"><strong>Note:</strong> The import updates catalog data only. It does not create stock-in movements. After importing your catalog, use the <em>Receive Stock</em> function to record opening stock quantities.</div>
HTML,
                    ],
                    [
                        'title' => 'Importing Labour & Energy',
                        'slug'  => 'import-labour-energy',
                        'content' => <<<'HTML'
<p>The Labour &amp; Energy import loads daily cost records from a CSV or Excel file.</p>
<h2 id="columns">Columns <a class="kb-anchor" href="#columns">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
  <tbody>
    <tr><td>date</td><td>Yes</td><td>YYYY-MM-DD</td></tr>
    <tr><td>zesa_cost</td><td>Yes</td><td>Dollar amount; 0 if not applicable</td></tr>
    <tr><td>diesel_cost</td><td>Yes</td><td>Dollar amount</td></tr>
    <tr><td>labour_cost</td><td>Yes</td><td>Dollar amount</td></tr>
  </tbody>
</table>
<h2 id="upsert">Upsert Key <a class="kb-anchor" href="#upsert">¶</a></h2>
<p>One record per date. Importing a row whose date already exists in the database will <em>update</em> that record.</p>
<div class="kb-callout kb-info"><strong>Note:</strong> Department-level labour breakdowns cannot be imported in bulk. Add them manually via the Labour &amp; Energy record detail page after import.</div>
HTML,
                    ],
                ],
            ],

            // ── 12. API Access ───────────────────────────────────────────────────
            [
                'title' => 'API Access', 'slug' => 'api-access', 'icon' => '🔌', 'sort_order' => 12,
                'articles' => [
                    [
                        'title' => 'API Overview',
                        'slug'  => 'api-overview',
                        'content' => <<<'HTML'
<p>MyMine provides a <strong>read-only JSON API</strong> at <code>https://production.epochmines.co.zw/api/v1/</code>. It is suitable for external dashboards, mobile apps, and data integrations.</p>
<h2 id="auth">Authentication <a class="kb-anchor" href="#auth">¶</a></h2>
<p>All API requests must include a <strong>Bearer token</strong> in the <code>Authorization</code> header:</p>
<pre><code>Authorization: Bearer YOUR_TOKEN_HERE</code></pre>
<p>Tokens are created from your <a href="/profile">Profile → API Access</a> page. Each token has read-only scope — the API cannot create or modify data.</p>
<h2 id="format">Response Format <a class="kb-anchor" href="#format">¶</a></h2>
<p>All responses are JSON. Successful responses include a <code>data</code> key. Error responses include a <code>message</code> key and an appropriate HTTP status code.</p>
<h2 id="rate-limits">Rate Limits <a class="kb-anchor" href="#rate-limits">¶</a></h2>
<p>Token creation is limited to 5 requests per minute. Regular data endpoints are not separately rate-limited but are subject to server capacity.</p>
HTML,
                    ],
                    [
                        'title' => 'Creating & Managing Tokens',
                        'slug'  => 'api-tokens',
                        'content' => <<<'HTML'
<p>API tokens are personal — each user manages their own tokens from their profile.</p>
<h2 id="create">Creating a Token <a class="kb-anchor" href="#create">¶</a></h2>
<ol>
  <li>Go to <strong>Profile → API Access</strong> (or visit <code>/profile/api-tokens</code>).</li>
  <li>Click <em>Create New Token</em>.</li>
  <li>Enter a descriptive name (e.g. "Power BI Dashboard" or "Mobile App").</li>
  <li>Click <em>Create</em>.</li>
  <li><strong>Copy the token immediately</strong> — it is shown only once and cannot be retrieved later.</li>
</ol>
<div class="kb-callout kb-warning"><strong>Important:</strong> Treat API tokens like passwords. Do not share them or commit them to source control.</div>
<h2 id="revoke">Revoking a Token <a class="kb-anchor" href="#revoke">¶</a></h2>
<p>On the API Access page, each token shows its name, creation date, and last-used date. Click <em>Revoke</em> next to any token to permanently delete it. All requests using that token will then receive a 401 Unauthorized response.</p>
HTML,
                    ],
                    [
                        'title' => 'Available Endpoints',
                        'slug'  => 'api-endpoints',
                        'content' => <<<'HTML'
<p>All data endpoints are under <code>/api/v1/</code> and require a valid Bearer token.</p>
<h2 id="endpoints">Endpoint Reference <a class="kb-anchor" href="#endpoints">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
  <tbody>
    <tr><td>POST</td><td>/api/auth/token</td><td>Obtain a token using email + password (rate-limited 5/min)</td></tr>
    <tr><td>DELETE</td><td>/api/auth/token</td><td>Revoke the current token</td></tr>
    <tr><td>GET</td><td>/api/v1/dashboard</td><td>KPI cards and quick-glance stats</td></tr>
    <tr><td>GET</td><td>/api/v1/production</td><td>Production records (filter: from, to, shift)</td></tr>
    <tr><td>GET</td><td>/api/v1/production/summary</td><td>Aggregated production totals by month</td></tr>
    <tr><td>GET</td><td>/api/v1/consumables</td><td>Full consumables catalog with current stock</td></tr>
    <tr><td>GET</td><td>/api/v1/consumables/low-stock</td><td>Items below reorder level</td></tr>
    <tr><td>GET</td><td>/api/v1/action-items</td><td>Open and in-progress action items</td></tr>
    <tr><td>GET</td><td>/api/v1/machines</td><td>Latest machine runtime record per machine</td></tr>
    <tr><td>GET</td><td>/api/v1/drilling</td><td>Drilling records (filter: from, to)</td></tr>
    <tr><td>GET</td><td>/api/v1/blasting</td><td>Blasting records (filter: from, to)</td></tr>
    <tr><td>GET</td><td>/api/v1/labour-energy</td><td>Daily labour and energy cost records (filter: from, to)</td></tr>
  </tbody>
</table>
<h2 id="example">Example Request <a class="kb-anchor" href="#example">¶</a></h2>
<pre><code>GET /api/v1/production?from=2026-01-01&amp;to=2026-01-31
Authorization: Bearer YOUR_TOKEN</code></pre>
HTML,
                    ],
                ],
            ],

            // ── 13. User Management ──────────────────────────────────────────────
            [
                'title' => 'User Management', 'slug' => 'user-management', 'icon' => '👥', 'sort_order' => 13,
                'articles' => [
                    [
                        'title' => 'Roles & Permissions',
                        'slug'  => 'roles-permissions',
                        'content' => <<<'HTML'
<p>MyMine has four user roles. Access to features is strictly controlled by role. Only a Super Admin can assign or change roles.</p>
<h2 id="matrix">Permission Matrix <a class="kb-anchor" href="#matrix">¶</a></h2>
<table class="kb-table">
  <thead>
    <tr><th>Capability</th><th>Super Admin</th><th>Admin</th><th>Manager</th><th>Viewer</th></tr>
  </thead>
  <tbody>
    <tr><td>View all data &amp; reports</td><td>✅</td><td>✅</td><td>✅</td><td>✅</td></tr>
    <tr><td>Create / edit / delete operational records</td><td>✅</td><td>✅</td><td>✅</td><td>❌</td></tr>
    <tr><td>Bulk import</td><td>✅</td><td>✅</td><td>✅</td><td>❌</td></tr>
    <tr><td>Export PDFs</td><td>✅</td><td>✅</td><td>✅</td><td>✅</td></tr>
    <tr><td>Manage settings, shifts, sites</td><td>✅</td><td>✅</td><td>❌</td><td>❌</td></tr>
    <tr><td>Manage users (create, activate)</td><td>✅</td><td>✅</td><td>❌</td><td>❌</td></tr>
    <tr><td>Assign / change user roles</td><td>✅</td><td>❌</td><td>❌</td><td>❌</td></tr>
    <tr><td>View audit logs</td><td>✅</td><td>✅</td><td>❌</td><td>❌</td></tr>
    <tr><td>Manage knowledge base content</td><td>✅</td><td>✅</td><td>❌</td><td>❌</td></tr>
  </tbody>
</table>
HTML,
                    ],
                    [
                        'title' => 'Adding & Managing Users',
                        'slug'  => 'managing-users',
                        'content' => <<<'HTML'
<p>User management is available to Admins and above under the <strong>Admin → Users</strong> sidebar link.</p>
<h2 id="create">Creating a User <a class="kb-anchor" href="#create">¶</a></h2>
<ol>
  <li>Click <em>New User</em>.</li>
  <li>Enter name, email address, password, role, and optionally phone and job title.</li>
  <li>Set <em>Active</em> to enabled (default).</li>
  <li>Click <em>Save</em>.</li>
</ol>
<p>The user can then log in with their email and the password you set. On first login they may be prompted to change their password if <em>Force Password Change</em> was checked.</p>
<h2 id="activate">Activating / Deactivating <a class="kb-anchor" href="#activate">¶</a></h2>
<p>Toggle the <em>Active</em> flag to lock a user out without deleting their account. Inactive users cannot log in but their data (audit logs, action item assignments, etc.) is preserved.</p>
<h2 id="delete">Deleting a User <a class="kb-anchor" href="#delete">¶</a></h2>
<p>Click <em>Delete</em> on the user edit page. This is permanent — the user's account and login history are removed. Data they created (production records, etc.) is retained.</p>
HTML,
                    ],
                    [
                        'title' => 'Two-Factor Authentication',
                        'slug'  => 'two-factor-auth',
                        'content' => <<<'HTML'
<p>Two-Factor Authentication (2FA) adds an extra login step requiring a time-based one-time password (TOTP) from an authenticator app.</p>
<h2 id="enable">Enabling 2FA <a class="kb-anchor" href="#enable">¶</a></h2>
<ol>
  <li>Go to <strong>Profile → Two-Factor Authentication</strong>.</li>
  <li>Click <em>Enable Two-Factor Authentication</em>.</li>
  <li>Scan the QR code with an authenticator app such as <strong>Google Authenticator</strong>, <strong>Authy</strong>, or <strong>Microsoft Authenticator</strong>.</li>
  <li>Enter the 6-digit code shown in the app to confirm setup.</li>
</ol>
<h2 id="login">Logging In With 2FA <a class="kb-anchor" href="#login">¶</a></h2>
<p>After entering your email and password, you are prompted for the 6-digit code. Open your authenticator app, find the MyMine entry, and enter the current code.</p>
<h2 id="recovery">Recovery Codes <a class="kb-anchor" href="#recovery">¶</a></h2>
<p>After enabling 2FA, download your recovery codes and store them securely offline. Each recovery code can be used once in place of the TOTP code if you lose access to your authenticator app.</p>
<div class="kb-callout kb-warning"><strong>Important:</strong> Admins cannot bypass 2FA for other users. If a user is locked out and has no recovery codes, an Admin must disable their 2FA from the User Management page and ask them to re-enrol.</div>
HTML,
                    ],
                ],
            ],

            // ── 14. System Settings ──────────────────────────────────────────────
            [
                'title' => 'System Settings', 'slug' => 'system-settings', 'icon' => '⚙️', 'sort_order' => 14,
                'articles' => [
                    [
                        'title' => 'Company & App Settings',
                        'slug'  => 'company-settings',
                        'content' => <<<'HTML'
<p>Company-wide settings are managed in <strong>Admin → Settings</strong> (Admin and above). Changes take effect immediately for all users.</p>
<h2 id="company">Company Information <a class="kb-anchor" href="#company">¶</a></h2>
<ul>
  <li><strong>Company Name</strong> — Shown in the sidebar, email headers, and PDF reports</li>
  <li><strong>Company Logo</strong> — Uploaded image (PNG / JPG, max 2 MB) used in PDFs and the sidebar</li>
  <li><strong>Address</strong> — Appears in PDF footers</li>
</ul>
<h2 id="defaults">Operational Defaults <a class="kb-anchor" href="#defaults">¶</a></h2>
<ul>
  <li><strong>Currency Symbol</strong> — Displayed throughout the app (default: $)</li>
  <li><strong>Default Gold Price ($/g)</strong> — Pre-fills the fidelity price field on new production records</li>
  <li><strong>Default ZESA / Diesel Cost</strong> — Pre-fills Labour &amp; Energy forms to save time on unchanged days</li>
</ul>
HTML,
                    ],
                    [
                        'title' => 'Email Configuration',
                        'slug'  => 'email-config',
                        'content' => <<<'HTML'
<p>Email alerts (low stock, machine overdue, action item due, etc.) require a working SMTP configuration. Set this up in <strong>Settings → Email</strong>.</p>
<h2 id="smtp">SMTP Settings <a class="kb-anchor" href="#smtp">¶</a></h2>
<table class="kb-table">
  <thead><tr><th>Setting</th><th>Example</th></tr></thead>
  <tbody>
    <tr><td>SMTP Host</td><td>smtp.gmail.com / mail.yourdomain.com</td></tr>
    <tr><td>SMTP Port</td><td>587 (TLS) or 465 (SSL)</td></tr>
    <tr><td>Encryption</td><td>tls or ssl</td></tr>
    <tr><td>Username</td><td>alerts@yourdomain.com</td></tr>
    <tr><td>Password</td><td>App password or SMTP password</td></tr>
    <tr><td>From Name</td><td>Epoch Mines</td></tr>
    <tr><td>From Email</td><td>alerts@yourdomain.com</td></tr>
  </tbody>
</table>
<h2 id="test">Testing Email <a class="kb-anchor" href="#test">¶</a></h2>
<p>After saving, click <em>Send Test Email</em> to dispatch a test message to your own email address. If it arrives, notifications are working correctly.</p>
<div class="kb-callout kb-warning"><strong>Note:</strong> If SMTP is not configured, all email notifications silently fail. Users will not receive any alerts.</div>
HTML,
                    ],
                    [
                        'title' => 'Shifts, Sites & Departments',
                        'slug'  => 'shifts-sites-depts',
                        'content' => <<<'HTML'
<p>The lookup tables for shifts, mining sites, and departments are managed in Settings. These lists populate the dropdowns throughout the application.</p>
<h2 id="shifts">Shifts <a class="kb-anchor" href="#shifts">¶</a></h2>
<p>Go to <strong>Settings → Shifts</strong> to add or rename shifts (e.g. Day, Night, Afternoon). Toggle a shift <em>inactive</em> to hide it from new-record dropdowns while preserving it on existing historical data.</p>
<h2 id="sites">Mining Sites <a class="kb-anchor" href="#sites">¶</a></h2>
<p>Go to <strong>Settings → Mining Sites</strong> to manage site names (e.g. "Level 4 North", "Open Pit East"). Sites are used in production, drilling, and blasting records for location-level reporting.</p>
<h2 id="departments">Departments <a class="kb-anchor" href="#departments">¶</a></h2>
<p>Go to <strong>Admin → Departments</strong> to manage department names used in Labour &amp; Energy breakdowns and SHE records. Deactivating a department prevents it appearing in new entries but does not affect historical records.</p>
HTML,
                    ],
                    [
                        'title' => 'Audit & Login Logs',
                        'slug'  => 'audit-logs',
                        'content' => <<<'HTML'
<p>Audit logs track all data changes in the application, providing a full history of who did what and when. Access them via <strong>Admin → Maintenance</strong>.</p>
<h2 id="audit">Audit Log <a class="kb-anchor" href="#audit">¶</a></h2>
<p>Each audit log entry records:</p>
<ul>
  <li><strong>User</strong> — Who made the change</li>
  <li><strong>Action</strong> — create, update, delete, or import</li>
  <li><strong>Model</strong> — Which table/model was affected (e.g. DailyProduction, Consumable)</li>
  <li><strong>Record ID</strong> — The primary key of the affected record</li>
  <li><strong>Changes</strong> — JSON snapshot of before and after values (for updates)</li>
  <li><strong>Timestamp</strong> — Date and time of the change</li>
</ul>
<h2 id="login">Login Log <a class="kb-anchor" href="#login">¶</a></h2>
<p>The Login Log records every login attempt — successful and failed — with the user's email, IP address, browser, and timestamp. Filter by user or date to investigate suspicious activity.</p>
<h2 id="retention">Log Retention <a class="kb-anchor" href="#retention">¶</a></h2>
<p>Logs are retained indefinitely unless manually purged. The Maintenance page includes a <em>Purge old logs</em> tool to delete entries older than a specified number of days.</p>
HTML,
                    ],
                ],
            ],

            // ── 15. Analytics & Insights ─────────────────────────────────────────
            [
                'title' => 'Analytics & Insights', 'slug' => 'analytics-insights', 'icon' => '📈', 'sort_order' => 15,
                'articles' => [
                    [
                        'title' => 'Analytics Overview',
                        'slug'  => 'analytics-overview',
                        'content' => <<<'HTML'
<p>The <strong>Analytics</strong> module transforms raw operational data into actionable intelligence. It delivers 13 KPI charts covering every aspect of the mining operation — from gold recovery and cost control to safety, equipment health, and statistical process control.</p>
<h2 id="access">Accessing Analytics <a class="kb-anchor" href="#access">¶</a></h2>
<p>Navigate to <strong>Analytics</strong> in the left sidebar. The page is available to all roles (Viewer and above).</p>
<h2 id="filter">Date Range Filter <a class="kb-anchor" href="#filter">¶</a></h2>
<p>At the top of the page, set a <strong>From</strong> and <strong>To</strong> date and click <strong>Apply</strong>. All 13 charts update simultaneously to reflect the selected period. The default range is the last 90 days.</p>
<h2 id="charts">Chart Layout <a class="kb-anchor" href="#charts">¶</a></h2>
<p>Charts are arranged in a responsive grid. Each card has a title, a brief description of what the metric measures, and an interactive Chart.js chart. Hover over data points to see exact values.</p>
<div class="kb-callout kb-info"><strong>Data requirement:</strong> Charts only display data that has been recorded. If a chart appears empty, enter production, assay, or cost records for the selected date range first.</div>
HTML,
                    ],
                    [
                        'title' => 'KPI 1 — Mill Recovery %',
                        'slug'  => 'kpi-mill-recovery',
                        'content' => <<<'HTML'
<p><strong>Mill Recovery %</strong> measures how efficiently the mill extracts gold from the ore it processes. It is the most critical metallurgical KPI.</p>
<h2 id="formula">Formula <a class="kb-anchor" href="#formula">¶</a></h2>
<p>$$\text{Recovery \%} = \frac{\text{Gold Produced (g)}}{\text{Ore Milled (t)} \times \text{Head Grade (g/t)}} \times 100$$</p>
<p>Head grade is taken from the <strong>Fire Assay</strong> result recorded in Assay Results for the same date.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>The line chart shows daily recovery % over the selected period. A horizontal reference line at <strong>85%</strong> indicates a typical target threshold. Points below this line warrant investigation of mill settings, ore hardness, or reagent dosage.</p>
<div class="kb-callout kb-warning"><strong>Note:</strong> Days with no fire assay result are excluded from the calculation and will appear as gaps in the chart.</div>
HTML,
                    ],
                    [
                        'title' => 'KPI 2 — All-In Sustaining Cost (AISC)',
                        'slug'  => 'kpi-aisc',
                        'content' => <<<'HTML'
<p><strong>AISC per gram</strong> is the total cost of producing one gram of gold, including all operational, labour, energy, and consumable costs. It is the primary profitability metric.</p>
<h2 id="formula">Formula <a class="kb-anchor" href="#formula">¶</a></h2>
<p>$$\text{AISC ($/g)} = \frac{\text{Total Costs (\$)}}{\text{Total Gold Produced (g)}}$$</p>
<p>Total costs include: Labour, ZESA electricity, diesel, and all consumable usage costs recorded in the selected period.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>Monthly bars show AISC for each month in the selected range. Compare against the <strong>Fidelity Gold Price</strong> (the price at which gold is sold) — if AISC exceeds the gold price, the operation is running at a loss for that month.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 3 — Grade Reconciliation',
                        'slug'  => 'kpi-grade-reconciliation',
                        'content' => <<<'HTML'
<p><strong>Grade Reconciliation</strong> compares the <em>predicted</em> gold grade (from fire assay) against the <em>actual</em> grade achieved in production. Divergence indicates measurement error, ore sorting issues, or dilution.</p>
<h2 id="formula">Formula <a class="kb-anchor" href="#formula">¶</a></h2>
<ul>
  <li><strong>Predicted grade (g/t)</strong> — Average fire assay result for the period</li>
  <li><strong>Actual grade (g/t)</strong> — Gold produced ÷ ore milled</li>
  <li><strong>Reconciliation factor</strong> — Actual ÷ Predicted × 100%</li>
</ul>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A grouped bar chart shows predicted vs actual grade per day. Consistent under-performance of actual vs predicted suggests dilution or sampling bias. Over-performance may indicate conservative assay estimates.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 4 — Cost per Tonne Milled',
                        'slug'  => 'kpi-cost-per-tonne',
                        'content' => <<<'HTML'
<p><strong>Cost per tonne milled</strong> tracks operational efficiency by showing how much it costs to process each tonne of ore through the mill.</p>
<h2 id="formula">Formula <a class="kb-anchor" href="#formula">¶</a></h2>
<p>$$\text{Cost/t} = \frac{\text{Total Costs (\$)}}{\text{Ore Milled (t)}}$$</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>Monthly bars. Rising cost/tonne over time signals increasing inefficiency (rising input costs, falling throughput, or equipment degradation). Use alongside AISC to distinguish metallurgical problems from throughput problems.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 5 — Month-on-Month & YTD Comparison',
                        'slug'  => 'kpi-mom-ytd',
                        'content' => <<<'HTML'
<p>Compares gold production across months and accumulates year-to-date (YTD) output to track progress against annual targets.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A dual-axis chart: bars show monthly gold production (g), the line shows cumulative YTD production. Use this to spot seasonal patterns, identify strong and weak months, and forecast year-end output.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 6 — Stockpile Balance Trend',
                        'slug'  => 'kpi-stockpile',
                        'content' => <<<'HTML'
<p>Tracks the volume of ore at each stage of the processing pipeline — run-of-mine (ROM), crushed, and milled — over time.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A stacked area chart showing <strong>Uncrushed Stockpile</strong> and <strong>Unmilled Stockpile</strong> by day. A growing uncrushed stockpile with a shrinking unmilled stockpile indicates a crusher bottleneck. Both declining signals the mill is consuming faster than the mine is producing.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 7 — Blasting Powder Factor',
                        'slug'  => 'kpi-powder-factor',
                        'content' => <<<'HTML'
<p><strong>Powder factor (kg/t)</strong> measures explosive efficiency — how many kilograms of ANFO are consumed per tonne of rock blasted.</p>
<h2 id="formula">Formula <a class="kb-anchor" href="#formula">¶</a></h2>
<p>$$\text{Powder Factor (kg/t)} = \frac{\text{ANFO Used (kg)}}{\text{Tonnes Blasted (t)}}$$</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A line chart over the selected period. Higher powder factors cost more per tonne but may be necessary for hard rock. Sudden spikes can indicate poor blast design or incorrect charge loading. Data is sourced from Blasting Records.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 8 — Safety Rates (LTIFR & TRIFR)',
                        'slug'  => 'kpi-safety-rates',
                        'content' => <<<'HTML'
<p>Safety performance is measured using industry-standard frequency rates based on SHE records.</p>
<h2 id="ltifr">Lost Time Injury Frequency Rate (LTIFR) <a class="kb-anchor" href="#ltifr">¶</a></h2>
<p>$$\text{LTIFR} = \frac{\text{Lost Time Injuries} \times 1{,}000{,}000}{\text{Hours Worked}}$$</p>
<h2 id="trifr">Total Recordable Injury Frequency Rate (TRIFR) <a class="kb-anchor" href="#trifr">¶</a></h2>
<p>$$\text{TRIFR} = \frac{\text{Total Recordable Injuries} \times 1{,}000{,}000}{\text{Hours Worked}}$$</p>
<p>Total recordable injuries = lost time injuries + medical treatment injuries + restricted work injuries.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>Monthly dual-line chart. Lower is better. A zero LTIFR for the period is a significant safety achievement. Spikes should trigger immediate incident review.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 9 — Consumables Burn Rate & Runway',
                        'slug'  => 'kpi-consumables-runway',
                        'content' => <<<'HTML'
<p>Shows how quickly consumable stock is being depleted and estimates how many days of stock remain at the current consumption rate.</p>
<h2 id="burnrate">Burn Rate <a class="kb-anchor" href="#burnrate">¶</a></h2>
<p>Average daily usage (quantity) per consumable item over the selected period, calculated from stock movement records with type <em>usage</em>.</p>
<h2 id="runway">Runway (days) <a class="kb-anchor" href="#runway">¶</a></h2>
<p>$$\text{Runway (days)} = \frac{\text{Current Stock Quantity}}{\text{Average Daily Burn Rate}}$$</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A horizontal bar chart ranked by shortest runway first. Items with runway below the reorder threshold are highlighted in red — these require immediate procurement action.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 10 — Drill Metres Trend',
                        'slug'  => 'kpi-drill-metres',
                        'content' => <<<'HTML'
<p>Tracks total metres drilled per day, broken down by drill type (production vs development), giving visibility of future ore availability.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A stacked bar chart by day. Production drilling directly feeds the blast-mine cycle. Development drilling creates access for future mining blocks. A sustained drop in drill metres will reduce available ore tonnage 2–4 weeks later. Data sourced from Drilling Records.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 11 — Statistical Process Control (SPC)',
                        'slug'  => 'kpi-spc',
                        'content' => <<<'HTML'
<p>The <strong>SPC Control Chart</strong> applies statistical process control to implied gold grade, detecting whether the production process is operating within normal variation or has experienced a significant shift.</p>
<h2 id="how">How It Works <a class="kb-anchor" href="#how">¶</a></h2>
<ul>
  <li><strong>Centre line (CL)</strong> — Mean implied gold grade over the period</li>
  <li><strong>Upper Control Limit (UCL)</strong> — Mean + 2 standard deviations</li>
  <li><strong>Lower Control Limit (LCL)</strong> — Mean − 2 standard deviations (floor 0)</li>
</ul>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>Points within the control limits indicate <strong>common cause variation</strong> — normal process noise. Points <strong>outside</strong> the control limits (highlighted in red) are <strong>out-of-control signals</strong> requiring investigation: equipment failure, assay error, ore change, or process disruption.</p>
<div class="kb-callout kb-info">SPC requires at least 7 data points to calculate meaningful control limits.</div>
HTML,
                    ],
                    [
                        'title' => 'KPI 12 — Predictive Maintenance',
                        'slug'  => 'kpi-predictive-maintenance',
                        'content' => <<<'HTML'
<p>The <strong>Predictive Maintenance</strong> chart monitors machine runtime hours and forecasts when each machine will reach its next service threshold.</p>
<h2 id="how">How It Works <a class="kb-anchor" href="#how">¶</a></h2>
<p>For each machine, the system calculates the average daily runtime hours over the selected period. It then projects how many days remain before the machine accumulates enough hours to require its next scheduled service.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A horizontal bar chart showing <strong>days to next service</strong> per machine, colour-coded:</p>
<ul>
  <li><span style="color:#22c55e">■</span> <strong>Green</strong> — More than 14 days remaining</li>
  <li><span style="color:#f59e0b">■</span> <strong>Amber</strong> — 7–14 days remaining</li>
  <li><span style="color:#ef4444">■</span> <strong>Red</strong> — Less than 7 days — service overdue or imminent</li>
</ul>
<p>Data is sourced from Machine Runtime records. Machines with no recent runtime records are excluded.</p>
HTML,
                    ],
                    [
                        'title' => 'KPI 13 — Anomaly Detection',
                        'slug'  => 'kpi-anomaly-detection',
                        'content' => <<<'HTML'
<p><strong>Anomaly Detection</strong> uses z-score analysis to automatically flag days where gold grade deviated abnormally from the historical mean, helping identify events that require investigation.</p>
<h2 id="how">How It Works <a class="kb-anchor" href="#how">¶</a></h2>
<p>$$\text{Z-score} = \frac{\text{Daily Grade} - \mu}{\sigma}$$</p>
<p>Where μ is the mean implied gold grade and σ is the standard deviation over the selected period. Days with |z-score| > 2 are flagged as anomalies.</p>
<h2 id="reading">Reading the Chart <a class="kb-anchor" href="#reading">¶</a></h2>
<p>A scatter chart where normal days appear as blue dots and anomalous days appear as <span style="color:#ef4444">red dots</span>. Hover over a red dot to see the exact date, grade value, and z-score. Investigate flagged days in the corresponding Daily Production and Assay Results records.</p>
<div class="kb-callout kb-warning"><strong>Note:</strong> Anomaly detection requires at least 10 data points to be statistically meaningful. With fewer records, all points will appear as normal.</div>
HTML,
                    ],
                ],
            ],
        ];

        foreach ($data as $catRow) {
            $articles = $catRow['articles'];
            unset($catRow['articles']);
            $catRow['created_at'] = $now;
            $catRow['updated_at'] = $now;

            // Upsert category by slug so re-running is safe
            $existing = DB::table('knowledge_base_categories')->where('slug', $catRow['slug'])->first();
            if ($existing) {
                DB::table('knowledge_base_categories')->where('slug', $catRow['slug'])->update($catRow);
                $catId = $existing->id;
            } else {
                $catId = DB::table('knowledge_base_categories')->insertGetId($catRow);
            }

            foreach ($articles as $i => $art) {
                DB::table('knowledge_base_articles')->updateOrInsert(
                    ['slug' => $art['slug']],
                    [
                        'knowledge_base_category_id' => $catId,
                        'title'      => $art['title'],
                        'slug'       => $art['slug'],
                        'content'    => $art['content'],
                        'sort_order' => $i + 1,
                        'is_published' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
