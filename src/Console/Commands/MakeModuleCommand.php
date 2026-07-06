<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeModuleCommand extends GeneratorCommand
{
    protected $signature = 'make:safarjaisur-module {name}';

    protected $description = 'Create a new admin panel extension module';

    protected $type = 'Module';

    protected function getStub(): string
    {
        return __DIR__ . '/../../../stubs/module.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return 'App\\AdminModules';
    }
}
