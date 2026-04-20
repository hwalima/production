<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\DrillingController;
use App\Http\Controllers\BlastingController;
use App\Http\Controllers\ChemicalsController;
use App\Http\Controllers\LabourEnergyController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\AssayController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\MiningSiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\MiningDepartmentController;
use App\Http\Controllers\SheController;
use App\Http\Controllers\ActionItemController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferencesController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\KnowledgeBaseAdminController;
use App\Http\Controllers\Auth\TwoFactorController;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// ── PWA Manifest (public — browsers fetch before user is authenticated) ───────
Route::get('/manifest.json', function () {
    $s        = \Illuminate\Support\Facades\Cache::remember('app_settings', 600, fn() => \App\Models\Setting::all()->pluck('value', 'key'));
    $name     = $s['company_name'] ?? config('app.name', 'My Mine');
    $logoPath = $s['logo_path'] ?? '';
    $icon192  = $logoPath ? asset('storage/'.$logoPath) : asset('icons/icon-192.png');
    $icon512  = $logoPath ? asset('storage/'.$logoPath) : asset('icons/icon-512.png');
    return response()->json([
        'name'             => $name,
        'short_name'       => 'MyMine',
        'description'      => 'Mine Production Management System',
        'start_url'        => '/dashboard',
        'scope'            => '/',
        'display'          => 'standalone',
        'background_color' => '#001a4d',
        'theme_color'      => '#fcb913',
        'icons'            => [
            ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ])->header('Content-Type', 'application/manifest+json');
})->name('pwa.manifest');

// ── Forced password change (auth only — no force.pw.change loop) ─────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change',  [\App\Http\Controllers\ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/password/change', [\App\Http\Controllers\ForcePasswordChangeController::class, 'update'])->name('password.force-change.update');

    // ── 2FA challenge — outside the force.pw.change and require.2fa guards ────
    Route::get('/two-factor/challenge',  [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.challenge.verify');
});

Route::middleware(['auth', 'force.pw.change', 'require.2fa'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile — any authenticated user
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notification preferences — any authenticated user manages their own
    Route::get('/profile/notification-preferences',   [NotificationPreferencesController::class, 'edit'])->name('notification-preferences.edit');
    Route::patch('/profile/notification-preferences', [NotificationPreferencesController::class, 'update'])->name('notification-preferences.update');

    // API token management
    Route::get('/profile/api-tokens',                 [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/profile/api-tokens',                [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/profile/api-tokens/{tokenId}',    [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

    // Theme preference
    Route::patch('/user/theme', [ThemeController::class, 'update'])->name('user.theme');

    // ── 2FA management (inside full auth + pw.change + 2fa guard) ─────────────
    Route::get('/two-factor/setup',             [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/two-factor/confirm',          [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/two-factor/disable',        [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/two-factor/recovery-codes',   [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes.regenerate');

    // ── Write access (super_admin + admin + manager) ──────────────────────
    Route::middleware('role:super_admin,admin,manager')->group(function () {
        Route::resource('production',    ProductionController::class)->except(['index', 'show']);
        Route::resource('drilling',      DrillingController::class)->except(['index', 'show']);
        Route::resource('blasting',      BlastingController::class)->except(['index', 'show']);
        Route::resource('chemicals',     ChemicalsController::class)->except(['index', 'show']);
        Route::resource('consumables',   ConsumableController::class)->except(['index', 'show']);

        // Bulk imports
        Route::get('/import',                           [ImportController::class, 'index'])->name('import.index');
        Route::get('/import/template/{type}',           [ImportController::class, 'template'])->name('import.template');
        Route::get('/import/production',                [ImportController::class, 'showProduction'])->name('import.production');
        Route::post('/import/production',               [ImportController::class, 'importProduction'])->name('import.production.store');
        Route::get('/import/consumables',               [ImportController::class, 'showConsumables'])->name('import.consumables');
        Route::post('/import/consumables',              [ImportController::class, 'importConsumables'])->name('import.consumables.store');
        Route::get('/import/labour-energy',             [ImportController::class, 'showLabourEnergy'])->name('import.labour-energy');
        Route::post('/import/labour-energy',            [ImportController::class, 'importLabourEnergy'])->name('import.labour-energy.store');

        Route::get('consumables/{consumable}/receive',  [ConsumableController::class, 'receiveForm'])->name('consumables.receive.form');
        Route::post('consumables/{consumable}/receive', [ConsumableController::class, 'receiveStock'])->name('consumables.receive');
        Route::get('consumables/{consumable}/use',      [ConsumableController::class, 'useForm'])->name('consumables.use.form');
        Route::post('consumables/{consumable}/use',     [ConsumableController::class, 'useStock'])->name('consumables.use');
        Route::delete('consumables/{consumable}/movements/{movement}', [ConsumableController::class, 'deleteMovement'])->name('consumables.movements.destroy');
        Route::resource('labour-energy', LabourEnergyController::class)->except(['index', 'show']);
        Route::resource('machines',      MachineController::class)->except(['index', 'show']);
        Route::resource('assay',         AssayController::class)->except(['index', 'show']);
    });

    // ── Documentation ─────────────────────────────────────────────────────
    Route::get('/docs', fn() => view('docs.index'))->name('docs.index');

    // ── Knowledge Base — all authenticated users ──────────────────────────
    Route::get('/help',                      [KnowledgeBaseController::class, 'index'])->name('kb.index');
    Route::get('/help/search',               [KnowledgeBaseController::class, 'search'])->name('kb.search');
    Route::get('/help/{category}/{article}', [KnowledgeBaseController::class, 'show'])->name('kb.show');

    // ── Notifications ─────────────────────────────────────────────────────
    Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // ── Read-only (all roles) — registered AFTER explicit paths ───────────
    Route::get('/production/calendar', [ProductionController::class, 'calendar'])->name('production.calendar');
    Route::get('/production/targets',  [ProductionController::class, 'targets'])->name('production.targets');
    Route::get('/assay/trends',        [AssayController::class,      'trends'])->name('assay.trends');
    Route::resource('production',    ProductionController::class)->only(['index', 'show']);
    Route::resource('drilling',      DrillingController::class)->only(['index', 'show']);
    Route::resource('blasting',      BlastingController::class)->only(['index', 'show']);
    Route::resource('chemicals',     ChemicalsController::class)->only(['index', 'show']);
    Route::resource('consumables',   ConsumableController::class)->only(['index', 'show']);
    Route::resource('labour-energy', LabourEnergyController::class)->only(['index', 'show']);
    Route::resource('machines',      MachineController::class)->only(['index', 'show']);
    Route::resource('assay',         AssayController::class)->only(['index', 'show']);

    Route::get('/reports/production',  [ReportController::class, 'production'])->name('reports.production');
    Route::get('/reports/consumables', [ReportController::class, 'consumables'])->name('reports.consumables');
    Route::get('/reports/accounts',    [ReportController::class, 'accounts'])->name('reports.accounts');
    Route::get('/reports/production/pdf',  [ReportController::class, 'productionPdf'])->name('reports.production.pdf')->middleware('throttle:10,1');
    Route::get('/reports/consumables/pdf', [ReportController::class, 'consumablesPdf'])->name('reports.consumables.pdf')->middleware('throttle:10,1');
    Route::get('/reports/accounts/pdf',    [ReportController::class, 'accountsPdf'])->name('reports.accounts.pdf')->middleware('throttle:10,1');

    // ── Action Items — all roles can view, managers+ can write ─────────────
    Route::get('/action-items',     [ActionItemController::class, 'index'])->name('action-items.index');
    Route::get('/action-items/pdf', [ActionItemController::class, 'pdf'])->name('action-items.pdf');
    Route::middleware('role:super_admin,admin,manager')->group(function () {
        Route::get('/action-items/create',           [ActionItemController::class, 'create'])->name('action-items.create');
        Route::post('/action-items',                 [ActionItemController::class, 'store'])->name('action-items.store');
        Route::get('/action-items/{actionItem}/edit',[ActionItemController::class, 'edit'])->name('action-items.edit');
        Route::put('/action-items/{actionItem}',     [ActionItemController::class, 'update'])->name('action-items.update');
        Route::delete('/action-items/{actionItem}',  [ActionItemController::class, 'destroy'])->name('action-items.destroy');
    });

    // ── SHE — read-only for all roles ────────────────────────────────────
    Route::get('/she',     [SheController::class, 'index'])->name('she.index');
    Route::get('/she/pdf', [SheController::class, 'pdf'])->name('she.pdf')->middleware('throttle:10,1');

    // ── SHE — write access ───────────────────────────────────────────────
    Route::middleware('role:super_admin,admin,manager')->group(function () {
        Route::get('/she/indicators/create',              [SheController::class, 'create'])->name('she.indicators.create');
        Route::post('/she/indicators',                    [SheController::class, 'store'])->name('she.indicators.store');
        Route::get('/she/indicators/{indicator}/edit',    [SheController::class, 'edit'])->name('she.indicators.edit');
        Route::put('/she/indicators/{indicator}',         [SheController::class, 'update'])->name('she.indicators.update');
        Route::delete('/she/indicators/{indicator}',      [SheController::class, 'destroy'])->name('she.indicators.destroy');
        Route::get('/she/requirements/edit',              [SheController::class, 'editRequirements'])->name('she.requirements.edit');
        Route::post('/she/requirements',                  [SheController::class, 'storeRequirements'])->name('she.requirements.store');
    });

    // ── SHE items management — admin+ only ───────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::post('/she/items',          [SheController::class, 'storeItem'])->name('she.items.store');
        Route::delete('/she/items/{item}', [SheController::class, 'destroyItem'])->name('she.items.destroy');
    });

    // ── Admin + super_admin ───────────────────────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        // User management
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Consumable low-stock alert — manual trigger
        Route::post('/consumables/send-low-stock-alert', [ConsumableController::class, 'sendLowStockAlert'])
            ->name('consumables.low-stock-alert')
            ->middleware('throttle:5,1');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test-email');

        // Shifts
        Route::resource('settings/shifts', ShiftController::class)->names('shifts')->except(['show', 'create']);
        Route::patch('settings/shifts/{shift}/toggle', [ShiftController::class, 'toggle'])->name('shifts.toggle');

        // Mining Sites
        Route::resource('settings/mining-sites', MiningSiteController::class)->names('mining-sites')->parameter('mining-sites', 'miningSite')->except(['show', 'create']);
        Route::patch('settings/mining-sites/{miningSite}/toggle', [MiningSiteController::class, 'toggle'])->name('mining-sites.toggle');

        // Mining Departments
        Route::resource('settings/mining-departments', MiningDepartmentController::class)->names('mining-departments')->parameter('mining-departments', 'miningDepartment')->except(['show', 'create']);
        Route::patch('settings/mining-departments/{miningDepartment}/toggle', [MiningDepartmentController::class, 'toggle'])->name('mining-departments.toggle');

        // Knowledge Base admin
        Route::get('/admin/kb',                              [KnowledgeBaseAdminController::class, 'index'])->name('kb.admin.index');
        Route::get('/admin/kb/categories/create',            [KnowledgeBaseAdminController::class, 'createCategory'])->name('kb.categories.create');
        Route::post('/admin/kb/categories',                  [KnowledgeBaseAdminController::class, 'storeCategory'])->name('kb.categories.store');
        Route::get('/admin/kb/categories/{category}/edit',   [KnowledgeBaseAdminController::class, 'editCategory'])->name('kb.categories.edit');
        Route::put('/admin/kb/categories/{category}',        [KnowledgeBaseAdminController::class, 'updateCategory'])->name('kb.categories.update');
        Route::delete('/admin/kb/categories/{category}',     [KnowledgeBaseAdminController::class, 'destroyCategory'])->name('kb.categories.destroy');
        Route::get('/admin/kb/articles/create',              [KnowledgeBaseAdminController::class, 'createArticle'])->name('kb.articles.create');
        Route::post('/admin/kb/articles',                    [KnowledgeBaseAdminController::class, 'storeArticle'])->name('kb.articles.store');
        Route::get('/admin/kb/articles/{article}/edit',      [KnowledgeBaseAdminController::class, 'editArticle'])->name('kb.articles.edit');
        Route::put('/admin/kb/articles/{article}',           [KnowledgeBaseAdminController::class, 'updateArticle'])->name('kb.articles.update');
        Route::delete('/admin/kb/articles/{article}',        [KnowledgeBaseAdminController::class, 'destroyArticle'])->name('kb.articles.destroy');
        Route::post('/admin/kb/articles/{article}/toggle',   [KnowledgeBaseAdminController::class, 'toggleArticle'])->name('kb.articles.toggle');

        // Maintenance
        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('/maintenance/cache/clear', [MaintenanceController::class, 'clearCache'])->name('maintenance.cache.clear');
        Route::get('/maintenance/audit-logs', [MaintenanceController::class, 'auditLogs'])->name('maintenance.audit-logs');
        Route::post('/maintenance/audit-logs/purge', [MaintenanceController::class, 'purgeAuditLogs'])->name('maintenance.audit-logs.purge');
        Route::get('/maintenance/login-logs', [MaintenanceController::class, 'loginLogs'])->name('maintenance.login-logs');
        Route::post('/maintenance/login-logs/purge', [MaintenanceController::class, 'purgeLoginLogs'])->name('maintenance.login-logs.purge');
    });

    // ── Super admin only ──────────────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::patch('/roles/{user}/assign', [RoleController::class, 'assign'])->name('roles.assign');

        // Notification preferences overview — who opted out of what
        Route::get('/admin/notification-preferences', [NotificationPreferencesController::class, 'adminOverview'])->name('admin.notification-preferences');
    });
});

require __DIR__.'/auth.php';
