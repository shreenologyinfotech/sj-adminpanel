<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\Command;
use Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface;

class MakeSettingCommand extends Command
{
    protected $signature = 'make:safarjaisur-setting {key} {value?} {--group=general} {--type=text}';

    protected $description = 'Create or update an admin setting';

    public function handle(SettingRepositoryInterface $settings): int
    {
        $settings->set(
            $this->argument('key'),
            $this->argument('value'),
            $this->option('group'),
            $this->option('type')
        );

        $this->components->info("Setting [{$this->argument('key')}] saved.");

        return self::SUCCESS;
    }
}
