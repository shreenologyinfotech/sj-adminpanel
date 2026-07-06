<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\Command;
use Safarjaisur\AdminPanel\Models\Bread;

class MakeBreadCommand extends Command
{
    protected $signature = 'make:safarjaisur-bread {name} {--table=}';

    protected $description = 'Register a new BREAD (Browse/Read/Edit/Add/Delete) resource for a table';

    public function handle(): int
    {
        $name = $this->argument('name');
        $table = $this->option('table') ?: str($name)->plural()->snake()->value();

        Bread::query()->create([
            'name' => str($name)->headline()->value(),
            'slug' => str($name)->slug()->value(),
            'table_name' => $table,
            'fields' => [],
        ]);

        $this->components->info("BREAD [{$name}] registered for table [{$table}].");

        return self::SUCCESS;
    }
}
