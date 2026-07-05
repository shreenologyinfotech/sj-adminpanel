<?php

declare(strict_types=1);

namespace safarjaisur\AdminPanel\Facades;

use Illuminate\Support\Facades\Facade;

class AdminPanel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sjadminpanel';
    }
}