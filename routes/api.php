<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Safarjaisur\AdminPanel\Http\Controllers\Api\BreadApiController;

// Only loaded by AdminPanelServiceProvider when laravel/sanctum is installed
// (see the register() method) — safe to assume auth:sanctum resolves here.
Route::prefix('api/sjadmin')
    ->middleware(['api', 'auth:sanctum', 'throttle:sjadmin-api'])
    ->name('sjadmin.api.')
    ->group(function () {
        Route::prefix('resources/{bread:slug}')->name('resources.')->group(function () {
            Route::get('/', [BreadApiController::class, 'index'])->middleware('sjadmin.bread_permission:browse')->name('index');
            Route::get('{record}', [BreadApiController::class, 'show'])->middleware('sjadmin.bread_permission:read')->name('show');
            Route::post('/', [BreadApiController::class, 'store'])->middleware('sjadmin.bread_permission:add')->name('store');
            Route::put('{record}', [BreadApiController::class, 'update'])->middleware('sjadmin.bread_permission:edit')->name('update');
            Route::delete('{record}', [BreadApiController::class, 'destroy'])->middleware('sjadmin.bread_permission:delete')->name('destroy');
        });
    });
