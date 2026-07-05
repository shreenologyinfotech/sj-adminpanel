<?php

use Illuminate\Support\Facades\Route;
use safarjaisur\AdminPanel\Http\Controllers\DashboardController;
use safarjaisur\AdminPanel\Http\Controllers\AuthController;
use safarjaisur\AdminPanel\Http\Controllers\BreadController;
use safarjaisur\AdminPanel\Http\Controllers\DatabaseController;
use safarjaisur\AdminPanel\Http\Controllers\MenuController;
use safarjaisur\AdminPanel\Http\Controllers\MediaController;
use safarjaisur\AdminPanel\Http\Controllers\SettingController;

Route::get('login', [AuthController::class, 'showLogin'])->name('sjadmin.login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('sjadmin.logout');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('sjadmin.dashboard');
    
    // Bread routes
    Route::get('bread', [BreadController::class, 'index'])->name('sjadmin.bread.index');
    Route::get('bread/{table}/create', [BreadController::class, 'create'])->name('sjadmin.bread.create');
    Route::post('bread/{table}', [BreadController::class, 'store'])->name('sjadmin.bread.store');
    Route::get('bread/{table}/edit', [BreadController::class, 'edit'])->name('sjadmin.bread.edit');
    Route::put('bread/{table}', [BreadController::class, 'update'])->name('sjadmin.bread.update');
    
    // Database routes
    Route::get('database', [DatabaseController::class, 'index'])->name('sjadmin.database.index');
    Route::post('database/tables', [DatabaseController::class, 'createTable'])->name('sjadmin.database.createTable');
    
    // Menu builder routes
    Route::get('menus', [MenuController::class, 'index'])->name('sjadmin.menus.index');
    
    // Media manager routes
    Route::get('media', [MediaController::class, 'index'])->name('sjadmin.media.index');
    
    // Settings route
    Route::get('settings', [SettingController::class, 'index'])->name('sjadmin.settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('sjadmin.settings.update');
});