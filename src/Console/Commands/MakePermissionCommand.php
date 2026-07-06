<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\Command;
use Safarjaisur\AdminPanel\Models\Permission;

class MakePermissionCommand extends Command
{
    protected $signature = 'make:safarjaisur-permission {name} {--group=general}';

    protected $description = 'Create a new permission';

    public function handle(): int
    {
        Permission::query()->firstOrCreate(
            ['slug' => str($this->argument('name'))->slug()->value()],
            ['name' => $this->argument('name'), 'group' => $this->option('group')]
        );

        $this->components->info("Permission [{$this->argument('name')}] created.");

        return self::SUCCESS;
    }
}
