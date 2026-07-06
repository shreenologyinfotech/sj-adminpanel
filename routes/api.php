<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('api/sjadmin')
    ->middleware(['api', 'auth:sanctum'])
    ->name('sjadmin.api.')
    ->group(function () {
        // REST API resources are added here as BREAD modules are generated.
    });
