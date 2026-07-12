<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Safarjaisur\AdminPanel\Http\Controllers\DashboardController;
use Safarjaisur\AdminPanel\Http\Controllers\ActivityLogController;
use Safarjaisur\AdminPanel\Http\Controllers\UserController;
use Safarjaisur\AdminPanel\Http\Controllers\RoleController;
use Safarjaisur\AdminPanel\Http\Controllers\PermissionController;
use Safarjaisur\AdminPanel\Http\Controllers\BreadController;
use Safarjaisur\AdminPanel\Http\Controllers\BreadResourceController;
use Safarjaisur\AdminPanel\Http\Controllers\BackupController;
use Safarjaisur\AdminPanel\Http\Controllers\DatabaseManagerController;
use Safarjaisur\AdminPanel\Http\Controllers\FileManagerController;
use Safarjaisur\AdminPanel\Http\Controllers\LogViewerController;
use Safarjaisur\AdminPanel\Http\Controllers\MenuBuilderController;
use Safarjaisur\AdminPanel\Http\Controllers\MediaController;
use Safarjaisur\AdminPanel\Http\Controllers\NotificationController;
use Safarjaisur\AdminPanel\Http\Controllers\SearchController;
use Safarjaisur\AdminPanel\Http\Controllers\SettingController;
use Safarjaisur\AdminPanel\Http\Controllers\ProfileController;

Route::group([
    'prefix' => config('sjadminpanel.route.prefix'),
    'middleware' => config('sjadminpanel.route.middleware'),
    'domain' => config('sjadminpanel.route.domain'),
    'as' => 'sjadmin.',
], function () {
    Route::get('/', DashboardController::class . '@index')->name('dashboard');
    Route::get('search', [SearchController::class, 'index'])->name('search.index');
    Route::get('activity', [ActivityLogController::class, 'index'])->middleware('sjadmin.permission:activity.view')->name('activity.index');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::resource('users', UserController::class)->names('users')->middleware('sjadmin.permission:users.manage');
    Route::resource('roles', RoleController::class)->names('roles')->middleware('sjadmin.permission:roles.manage');
    Route::resource('permissions', PermissionController::class)->names('permissions')->middleware('sjadmin.permission:roles.manage');

    Route::resource('bread', BreadController::class)->except(['show'])->names('bread')->middleware('sjadmin.permission:bread.manage');

    // Dynamic per-record-type data (BREAD-driven resource CRUD).
    // Each ability maps to a granular "{bread-slug}.{ability}" permission,
    // auto-created/synced by BreadController whenever a Bread is saved.
    // Users holding the umbrella "bread.manage" permission always pass too.
    Route::prefix('resources/{bread:slug}')->name('resources.')->group(function () {
        Route::get('/', [BreadResourceController::class, 'index'])->middleware('sjadmin.bread_permission:browse')->name('index');
        Route::get('export', [BreadResourceController::class, 'export'])->middleware('sjadmin.bread_permission:browse')->name('export');
        Route::get('fields/{field}/search', [BreadResourceController::class, 'relationshipSearch'])->middleware('sjadmin.bread_permission:browse')->name('relationship-search');
        Route::get('import', [BreadResourceController::class, 'importForm'])->middleware('sjadmin.bread_permission:add')->name('import.form');
        Route::post('import', [BreadResourceController::class, 'import'])->middleware(['sjadmin.bread_permission:add', 'throttle:sjadmin-writes'])->name('import');
        Route::delete('bulk', [BreadResourceController::class, 'bulkDestroy'])->middleware(['sjadmin.bread_permission:delete', 'throttle:sjadmin-writes'])->name('bulk-destroy');
        Route::get('create', [BreadResourceController::class, 'create'])->middleware('sjadmin.bread_permission:add')->name('create');
        Route::post('/', [BreadResourceController::class, 'store'])->middleware(['sjadmin.bread_permission:add', 'throttle:sjadmin-writes'])->name('store');
        Route::get('{record}', [BreadResourceController::class, 'show'])->middleware('sjadmin.bread_permission:read')->name('show');
        Route::get('{record}/edit', [BreadResourceController::class, 'edit'])->middleware('sjadmin.bread_permission:edit')->name('edit');
        Route::put('{record}', [BreadResourceController::class, 'update'])->middleware(['sjadmin.bread_permission:edit', 'throttle:sjadmin-writes'])->name('update');
        Route::delete('{record}', [BreadResourceController::class, 'destroy'])->middleware(['sjadmin.bread_permission:delete', 'throttle:sjadmin-writes'])->name('destroy');
    });

    Route::resource('menu', MenuBuilderController::class)->only(['index', 'store', 'update', 'destroy'])->names('menu')->middleware('sjadmin.permission:menu.manage');

    Route::middleware('sjadmin.permission:settings.manage')->group(function () {
        Route::resource('settings', SettingController::class)->only(['index'])->names('settings');
        Route::post('settings', [SettingController::class, 'store'])->name('settings.store');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy');
    });

    Route::middleware('sjadmin.permission:media.manage')->group(function () {
        Route::resource('media', MediaController::class)->only(['index', 'store'])->names('media');
        Route::post('media/folder', [MediaController::class, 'folder'])->name('media.folder');
        Route::put('media/rename', [MediaController::class, 'rename'])->name('media.rename');
        Route::delete('media', [MediaController::class, 'destroy'])->name('media.destroy');
    });

    Route::prefix('backups')->name('backups.')->middleware('sjadmin.permission:backups.manage')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/', [BackupController::class, 'store'])->name('store');
        Route::get('{file}/download', [BackupController::class, 'download'])->where('file', '[A-Za-z0-9._-]+')->name('download');
        Route::post('{file}/restore', [BackupController::class, 'restore'])->where('file', '[A-Za-z0-9._-]+')->name('restore');
        Route::delete('{file}', [BackupController::class, 'destroy'])->where('file', '[A-Za-z0-9._-]+')->name('destroy');
    });

    Route::prefix('files')->name('files.')->middleware('sjadmin.permission:files.manage')->group(function () {
        Route::get('/', [FileManagerController::class, 'index'])->name('index');
        Route::post('upload', [FileManagerController::class, 'upload'])->name('upload');
        Route::post('folder', [FileManagerController::class, 'folder'])->name('folder');
        Route::put('rename', [FileManagerController::class, 'rename'])->name('rename');
        Route::get('download', [FileManagerController::class, 'download'])->name('download');
        Route::delete('/', [FileManagerController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('logs')->name('logs.')->middleware('sjadmin.permission:logs.view')->group(function () {
        Route::get('/', [LogViewerController::class, 'index'])->name('index');
        Route::get('{file}/download', [LogViewerController::class, 'download'])->where('file', '[A-Za-z0-9._-]+')->name('download');
        Route::delete('{file}', [LogViewerController::class, 'destroy'])->where('file', '[A-Za-z0-9._-]+')->name('destroy');
    });

    Route::prefix('database')->name('database.')->middleware('sjadmin.permission:database.manage')->group(function () {
        Route::get('/', [DatabaseManagerController::class, 'index'])->name('index');
        Route::post('tables', [DatabaseManagerController::class, 'store'])->name('tables.store');
        Route::delete('tables/{table}', [DatabaseManagerController::class, 'destroy'])->name('tables.destroy');
    });

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/two-factor', [ProfileController::class, 'enableTwoFactor'])->name('profile.two-factor.enable');
    Route::post('profile/two-factor/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('profile.two-factor.confirm');
    Route::delete('profile/two-factor', [ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable');
    Route::post('profile/two-factor/recovery-codes', [ProfileController::class, 'regenerateRecoveryCodes'])->name('profile.two-factor.recovery-codes');
});
