<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'safarjaisuradmin:install {--force : Overwrite existing published files}';

    protected $description = 'Install the Safarjaisur AdminPanel package (assets, config, views, migrations, seed data)';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->components->info('Installing Safarjaisur AdminPanel...');

        $this->publish('sjadminpanel-config', 'Config', $force);
        $this->publish('sjadminpanel-assets', 'Assets', $force);
        $this->publish('sjadminpanel-views', 'Views', $force);
        $this->publish('sjadminpanel-lang', 'Language files', $force);

        $this->components->task('Creating storage symlink', fn () => $this->createStorageLink());

        $this->components->task('Running migrations', function () {
            Artisan::call('migrate', ['--force' => true]);

            return true;
        });

        $this->components->task('Seeding default admin, roles, menus & settings', function () {
            Artisan::call('db:seed', [
                '--class' => \Safarjaisur\AdminPanel\Database\Seeders\AdminPanelSeeder::class,
                '--force' => true,
            ]);

            return true;
        });

        $this->newLine();
        $this->components->info('Safarjaisur AdminPanel installed successfully.');
        $this->line('  URL:      ' . url(config('sjadminpanel.route.prefix')));
        $this->line('  Email:    ' . config('sjadminpanel.default_admin.email'));
        $this->line('  Password: ' . config('sjadminpanel.default_admin.password'));

        return self::SUCCESS;
    }

    protected function publish(string $tag, string $label, bool $force): void
    {
        $this->components->task("Publishing {$label}", function () use ($tag, $force) {
            Artisan::call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $force,
            ]);

            return true;
        });
    }

    protected function createStorageLink(): bool
    {
        if (! file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        return true;
    }
}
