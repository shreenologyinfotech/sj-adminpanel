<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Safarjaisur\AdminPanel\Http\Controllers\Auth\LoginController;
use Safarjaisur\AdminPanel\Http\Controllers\Auth\TwoFactorChallengeController;

Route::group([
    'prefix' => config('sjadminpanel.route.prefix'),
    'middleware' => 'web',
    'as' => 'sjadmin.',
], function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])
        ->middleware('throttle:sjadmin-login')
        ->name('login');

    Route::post('login', [LoginController::class, 'login'])
        ->middleware('throttle:sjadmin-login')
        ->name('login.attempt');

    Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'show'])
        ->middleware('throttle:sjadmin-login')
        ->name('two-factor.challenge');

    Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])
        ->middleware('throttle:sjadmin-login')
        ->name('two-factor.verify');

    Route::post('logout', [LoginController::class, 'logout'])
        ->middleware('sjadmin.auth')
        ->name('logout');
});
