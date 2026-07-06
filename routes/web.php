<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Safarjaisur\AdminPanel\Http\Controllers\DashboardController;
use Safarjaisur\AdminPanel\Http\Controllers\UserController;
use Safarjaisur\AdminPanel\Http\Controllers\RoleController;
use Safarjaisur\AdminPanel\Http\Controllers\PermissionController;
use Safarjaisur\AdminPanel\Http\Controllers\BreadController;
use Safarjaisur\AdminPanel\Http\Controllers\DatabaseManagerController;
use Safarjaisur\AdminPanel\Http\Controllers\MenuBuilderController;
use Safarjaisur\AdminPanel\Http\Controllers\MediaController;
use Safarjaisur\AdminPanel\Http\Controllers\SettingController;
use Safarjaisur\AdminPanel\Http\Controllers\ProfileController;

Route::group([
    'prefix' => config('sjadminpanel.route.prefix'),
    'middleware' => config('sjadminpanel.route.middleware'),
    'domain' => config('sjadminpanel.route.domain'),
    'as' => 'sjadmin.',
], function () {
    Route::get('/', DashboardController::class . '@index')->name('dashboard');

    Route::resource('users', UserController::class)->names('users');
    Route::resource('roles', RoleController::class)->names('roles');
    Route::resource('permissions', PermissionController::class)->names('permissions');
    Route::resource('bread', BreadController::class)->names('bread');
    Route::resource('menu', MenuBuilderController::class)->names('menu');
    Route::resource('settings', SettingController::class)->only(['index', 'update'])->names('settings');
    Route::resource('media', MediaController::class)->names('media');

    Route::prefix('database')->name('database.')->group(function () {
        Route::get('/', [DatabaseManagerController::class, 'index'])->name('index');
        Route::post('tables', [DatabaseManagerController::class, 'store'])->name('tables.store');
        Route::delete('tables/{table}', [DatabaseManagerController::class, 'destroy'])->name('tables.destroy');
    });

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
});
