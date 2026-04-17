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

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// ── PWA manifest (public, no auth) ───────────────────────────────────────────
Route::get('/manifest.json', function () {
    $settings  = \Illuminate\Support\Facades\Cache::remember('app_settings', 600,
        fn() => \App\Models\Setting::all()->pluck('value', 'key'));
    $name      = $settings['company_name'] ?? config('app.name', 'MyMine');
    $shortName = \Illuminate\Support\Str::limit($name, 14, '');

    return response()->json([
        'name'             => $name,
        'short_name'       => $shortName,
        'description'      => $name . ' Mining Production Dashboard',
        'start_url'        => '/dashboard?source=pwa',
        'display'          => 'standalone',
        'background_color' => '#001a4d',
        'theme_color'      => '#fcb913',
        'orientation'      => 'portrait-primary',
        'scope'            => '/',
        'lang'             => 'en',
        'categories'       => ['productivity', 'business'],
        'icons'            => [
            ['src' => asset('icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => asset('icons/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => asset('icons/apple-touch-icon.png'), 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'any'],
        ],
        'shortcuts' => [
            ['name' => 'Dashboard',        'url' => '/dashboard',           'description' => 'Production overview'],
            ['name' => 'Daily Production', 'url' => '/production',          'description' => 'Log daily production data'],
            ['name' => 'Gold Smelting',    'url' => '/mining/gold',         'description' => 'Gold smelting records'],
            ['name' => 'Consumables',      'url' => '/consumables',         'description' => 'Consumable stock levels'],
        ],
        'screenshots' => [],
    ], 200, ['Content-Type' => 'application/manifest+json; charset=utf-8']);
})->name('pwa.manifest');

// ── Forced password change (auth only — no force.pw.change loop) ─────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change',  [\App\Http\Controllers\ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/password/change', [\App\Http\Controllers\ForcePasswordChangeController::class, 'update'])->name('password.force-change.update');
});

Route::middleware(['auth', 'verified', 'force.pw.change'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile — any authenticated user
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Write access (super_admin + admin + manager) ──────────────────────
    Route::middleware('role:super_admin,admin,manager')->group(function () {
        Route::resource('production',    ProductionController::class)->except(['index', 'show']);
        Route::resource('drilling',      DrillingController::class)->except(['index', 'show']);
        Route::resource('blasting',      BlastingController::class)->except(['index', 'show']);
        Route::resource('chemicals',     ChemicalsController::class)->except(['index', 'show']);
        Route::resource('consumables',   ConsumableController::class)->except(['index', 'show']);
        Route::get('consumables/{consumable}/receive',  [ConsumableController::class, 'receiveForm'])->name('consumables.receive.form');
        Route::post('consumables/{consumable}/receive', [ConsumableController::class, 'receiveStock'])->name('consumables.receive');
        Route::get('consumables/{consumable}/use',      [ConsumableController::class, 'useForm'])->name('consumables.use.form');
        Route::post('consumables/{consumable}/use',     [ConsumableController::class, 'useStock'])->name('consumables.use');
        Route::delete('consumables/{consumable}/movements/{movement}', [ConsumableController::class, 'deleteMovement'])->name('consumables.movements.destroy');
        Route::resource('labour-energy', LabourEnergyController::class)->except(['index', 'show']);
        Route::resource('machines',      MachineController::class)->except(['index', 'show']);
        Route::resource('assay',         AssayController::class)->except(['index', 'show']);
    });

    // ── Notifications ─────────────────────────────────────────────────────
    Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // ── Read-only (all roles) — registered AFTER explicit paths ───────────
    Route::get('/production/calendar', [ProductionController::class, 'calendar'])->name('production.calendar');
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
    });
});

require __DIR__.'/auth.php';
