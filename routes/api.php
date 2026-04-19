<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ApiProductionController;
use App\Http\Controllers\Api\V1\ApiConsumableController;
use App\Http\Controllers\Api\V1\ApiActionItemController;
use App\Http\Controllers\Api\V1\ApiMachineController;
use App\Http\Controllers\Api\V1\ApiDrillingController;
use App\Http\Controllers\Api\V1\ApiBlastingController;
use App\Http\Controllers\Api\V1\ApiLabourEnergyController;

/*
|--------------------------------------------------------------------------
| API Routes — MyMine v1
|
| Authentication: POST /api/auth/token  → returns a Bearer token.
| All v1 endpoints require:  Authorization: Bearer {token}
|--------------------------------------------------------------------------
*/

// Issue a Sanctum token (rate-limited: 5 attempts / minute)
Route::post('/auth/token', [AuthController::class, 'issue'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {

    // Revoke the current token
    Route::delete('/auth/token', [AuthController::class, 'revoke'])
        ->middleware('throttle:10,1');

    // ── v1 read-only endpoints (rate-limited: 60 requests / minute) ─
    Route::prefix('v1')->middleware('throttle:60,1')->group(function () {

        // Dashboard KPIs
        Route::get('/dashboard',              [DashboardController::class,       'index']);

        // Production records
        Route::get('/production',             [ApiProductionController::class,   'index']);
        Route::get('/production/summary',     [ApiProductionController::class,   'summary']);

        // Consumables / stores
        Route::get('/consumables',            [ApiConsumableController::class,   'index']);
        Route::get('/consumables/low-stock',  [ApiConsumableController::class,   'lowStock']);

        // Action items
        Route::get('/action-items',           [ApiActionItemController::class,   'index']);

        // Machine runtimes
        Route::get('/machines',               [ApiMachineController::class,      'index']);

        // Drilling & blasting
        Route::get('/drilling',               [ApiDrillingController::class,     'index']);
        Route::get('/blasting',               [ApiBlastingController::class,     'index']);

        // Labour & energy
        Route::get('/labour-energy',          [ApiLabourEnergyController::class, 'index']);
    });
});
