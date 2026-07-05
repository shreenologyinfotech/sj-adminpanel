<?php

declare(strict_types=1);

namespace safarjaisur\AdminPanel\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use safarjaisur\AdminPanel\Console\Commands\InstallCommand;
use safarjaisur\AdminPanel\Console\Commands\MakeBreadCommand;
use safarjaisur\AdminPanel\Console\Commands\MakeModuleCommand;
use safarjaisur\AdminPanel\Console\Commands\MakeWidgetCommand;

class AdminPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/sjadminpanel.php', 'sjadminpanel'
        );

        $this->app->singleton('sjadminpanel', function ($app) {
            return new \safarjaisur\AdminPanel\Support\AdminPanelManager();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'sjadmin');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'sjadmin');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('sjadminpanel.route_prefix', 'admin'),
            'middleware' => config('sjadminpanel.middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        });

        Route::group([
            'prefix' => 'api/sjadmin',
            'middleware' => ['api'],
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        });
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/sjadminpanel.php' => config_path('sjadminpanel.php'),
        ], 'sjadminpanel-config');

        $this->publishes([
            __DIR__ . '/../Views' => resource_path('views/vendor/sjadmin'),
        ], 'sjadminpanel-views');

        $this->publishes([
            __DIR__ . '/../Assets' => public_path('vendor/sjadmin'),
        ], 'sjadminpanel-assets');

        $this->commands([
            InstallCommand::class,
            MakeBreadCommand::class,
            MakeModuleCommand::class,
            MakeWidgetCommand::class,
        ]);
    }
}