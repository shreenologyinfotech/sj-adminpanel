<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Safarjaisur\AdminPanel\Providers\AdminPanelServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [AdminPanelServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
