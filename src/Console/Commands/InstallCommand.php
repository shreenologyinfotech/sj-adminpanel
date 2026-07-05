<?php

declare(strict_types=1);

namespace safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'sjadmin:install';
    protected $description = 'Install and boot the sjadminpanel environment and assets';

    public function handle(): int
    {
        $this->info('Initializing sjadminpanel Installation...');

        $this->info('Publishing config...');
        Artisan::call('vendor:publish', ['--tag' => 'sjadminpanel-config']);

        $this->info('Publishing assets...');
        Artisan::call('vendor:publish', ['--tag' => 'sjadminpanel-assets']);

        $this->info('Publishing views...');
        Artisan::call('vendor:publish', ['--tag' => 'sjadminpanel-views']);

        $this->info('Running database migrations...');
        Artisan::call('migrate');

        $this->info('Seeding default administrator credentials...');
        Artisan::call('db:seed', ['--class' => '\\safarjaisur\\AdminPanel\\Database\\Seeders\\AdminPanelSeeder']);

        $this->info('Creating symlink for file storage...');
        Artisan::call('storage:link');

        $this->info('sjadminpanel installed successfully!');
        $this->table(
            ['Parameter', 'Default Value'],
            [
                ['Route URL', url(config('sjadminpanel.route_prefix', 'admin'))],
                ['Admin Email', 'admin@example.com'],
                ['Password', 'password'],
                ['Database Host', config('sjadminpanel.database.host')],
                ['Database Name', config('sjadminpanel.database.database')]
            ]
        );

        return self::SUCCESS;
    }
}