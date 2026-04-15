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

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

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

    // ── Read-only (all roles) — registered AFTER explicit paths ───────────
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
    Route::get('/reports/production/pdf',  [ReportController::class, 'productionPdf'])->name('reports.production.pdf');
    Route::get('/reports/consumables/pdf', [ReportController::class, 'consumablesPdf'])->name('reports.consumables.pdf');

    // ── Admin + super_admin ───────────────────────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        // User management
        Route::resource('users', UserController::class)->except(['show']);

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
    });

    // ── Super admin only ──────────────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::patch('/roles/{user}/assign', [RoleController::class, 'assign'])->name('roles.assign');
    });
});

require __DIR__.'/auth.php';
