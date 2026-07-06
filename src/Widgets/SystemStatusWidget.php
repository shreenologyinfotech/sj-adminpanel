<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Widgets;

class SystemStatusWidget extends Widget
{
    public function handle(): array
    {
        return [
            'type' => 'status',
            'title' => 'System Status',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'debug' => (bool) config('app.debug'),
            'environment' => app()->environment(),
        ];
    }
}
