<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static static registerWidget(string $widgetClass)
 * @method static \Illuminate\Support\Collection widgets()
 * @method static static registerBread(string $slug, array $definition)
 * @method static \Illuminate\Support\Collection breads()
 * @method static static registerMenuItem(array $item)
 * @method static \Illuminate\Support\Collection extensionMenuItems()
 * @method static string version()
 *
 * @see \Safarjaisur\AdminPanel\AdminPanel
 */
class AdminPanel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Safarjaisur\AdminPanel\AdminPanel::class;
    }
}
