<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Safarjaisur\AdminPanel\AdminPanel;
use Safarjaisur\AdminPanel\Console\Commands\InstallCommand;
use Safarjaisur\AdminPanel\Console\Commands\MakeBreadCommand;
use Safarjaisur\AdminPanel\Console\Commands\MakeMenuCommand;
use Safarjaisur\AdminPanel\Console\Commands\MakeModuleCommand;
use Safarjaisur\AdminPanel\Console\Commands\MakePermissionCommand;
use Safarjaisur\AdminPanel\Console\Commands\MakeSettingCommand;
use Safarjaisur\AdminPanel\Console\Commands\MakeWidgetCommand;
use Safarjaisur\AdminPanel\Contracts\Repositories\MenuRepositoryInterface;
use Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface;
use Safarjaisur\AdminPanel\Repositories\MenuRepository;
use Safarjaisur\AdminPanel\Repositories\SettingRepository;

class AdminPanelServiceProvider extends ServiceProvider
{
    /**
     * Package base path, used for publishing / loading resources.
     */
    protected string $packagePath = __DIR__ . '/../..';

    /**
     * Bind singletons and merge config before boot.
     */
    public function register(): void
    {
        $this->mergeConfigFrom("{$this->packagePath}/config/sjadminpanel.php", 'sjadminpanel');

        $this->app->singleton(AdminPanel::class, fn ($app) => new AdminPanel());

        $this->app->singleton(\Safarjaisur\AdminPanel\Services\MediaThumbnailService::class, function ($app) {
            $driver = config('sjadminpanel.media.driver', 'gd');

            $manager = $driver === 'imagick'
                ? \Intervention\Image\ImageManager::imagick()
                : \Intervention\Image\ImageManager::gd();

            return new \Safarjaisur\AdminPanel\Services\MediaThumbnailService($manager);
        });

        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);

        $this->registerCommands();
    }

    /**
     * Bootstrap routes, views, migrations, translations and publishables.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViewsFrom("{$this->packagePath}/resources/views", 'sjadminpanel');
        $this->loadMigrationsFrom("{$this->packagePath}/database/migrations");
        $this->loadTranslationsFrom("{$this->packagePath}/resources/lang", 'sjadminpanel');

        $this->registerAuthGuardAndProvider();
        $this->registerMiddlewareAliases();
        $this->registerBladeComponents();
        $this->registerRateLimiter();
        $this->registerPublishables();
    }

    protected function loadRoutes(): void
    {
        $this->loadRoutesFrom("{$this->packagePath}/routes/web.php");
        $this->loadRoutesFrom("{$this->packagePath}/routes/auth.php");

        // The REST API is opt-in: it relies on Laravel Sanctum for token
        // auth, which is a suggested (not required) dependency. Loading
        // api.php unconditionally would throw on "auth:sanctum" for any
        // app that hasn't installed it.
        if (class_exists(\Laravel\Sanctum\Sanctum::class)) {
            $this->loadRoutesFrom("{$this->packagePath}/routes/api.php");
        }
    }

    /**
     * Register the isolated `sjadmin` guard so the package never collides
     * with the host application's own authentication system.
     */
    protected function registerAuthGuardAndProvider(): void
    {
        Config::set('auth.guards.sjadmin', array_merge(
            ['driver' => 'session', 'provider' => 'sjadmin_users'],
            Config::get('auth.guards.sjadmin', [])
        ));

        Config::set('auth.providers.sjadmin_users', array_merge(
            ['driver' => 'eloquent', 'model' => Config::get('sjadminpanel.auth.model')],
            Config::get('auth.providers.sjadmin_users', [])
        ));
    }

    protected function registerMiddlewareAliases(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('sjadmin.auth', \Safarjaisur\AdminPanel\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('sjadmin.permission', \Safarjaisur\AdminPanel\Http\Middleware\EnsurePermission::class);
        $router->aliasMiddleware('sjadmin.role', \Safarjaisur\AdminPanel\Http\Middleware\EnsureRole::class);
        $router->aliasMiddleware('sjadmin.bread_permission', \Safarjaisur\AdminPanel\Http\Middleware\EnsureBreadPermission::class);
    }

    protected function registerBladeComponents(): void
    {
        Blade::componentNamespace('Safarjaisur\\AdminPanel\\View\\Components', 'sjadmin');
    }

    protected function registerRateLimiter(): void
    {
        RateLimiter::for('sjadmin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('sjadmin-api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('sjadmin-writes', function (Request $request) {
            return Limit::perMinute(60)->by($request->user('sjadmin')?->id ?: $request->ip());
        });
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            MakeModuleCommand::class,
            MakeWidgetCommand::class,
            MakeBreadCommand::class,
            MakeMenuCommand::class,
            MakeSettingCommand::class,
            MakePermissionCommand::class,
        ]);
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            "{$this->packagePath}/config/sjadminpanel.php" => config_path('sjadminpanel.php'),
        ], 'sjadminpanel-config');

        $this->publishes([
            "{$this->packagePath}/resources/assets" => public_path('vendor/sjadminpanel'),
        ], 'sjadminpanel-assets');

        $this->publishes([
            "{$this->packagePath}/resources/views" => resource_path('views/vendor/sjadminpanel'),
        ], 'sjadminpanel-views');

        // $this->publishes([
        //     "{$this->packagePath}/resources/lang" => $this->app->langPath('vendor/sjadminpanel'),
        // ], 'sjadminpanel-lang');

        $this->publishes([
            "{$this->packagePath}/database/migrations" => database_path('migrations'),
        ], 'sjadminpanel-migrations');
    }
}
