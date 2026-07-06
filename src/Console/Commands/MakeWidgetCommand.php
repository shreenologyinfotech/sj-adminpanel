<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeWidgetCommand extends GeneratorCommand
{
    protected $signature = 'make:safarjaisur-widget {name}';

    protected $description = 'Create a new dashboard widget';

    protected $type = 'Widget';

    protected function getStub(): string
    {
        return __DIR__ . '/../../../stubs/widget.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return 'App\\AdminWidgets';
    }
}
