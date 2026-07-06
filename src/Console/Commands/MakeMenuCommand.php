<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Console\Commands;

use Illuminate\Console\Command;
use Safarjaisur\AdminPanel\Models\Menu;
use Safarjaisur\AdminPanel\Models\MenuItem;

class MakeMenuCommand extends Command
{
    protected $signature = 'make:safarjaisur-menu {title} {--menu=admin} {--icon=ti ti-point} {--route=} {--url=}';

    protected $description = 'Add a new item to an admin menu';

    public function handle(): int
    {
        $menu = Menu::query()->firstOrCreate(
            ['slug' => $this->option('menu')],
            ['name' => str($this->option('menu'))->headline()->value()]
        );

        MenuItem::query()->create([
            'menu_id' => $menu->id,
            'title' => $this->argument('title'),
            'icon' => $this->option('icon'),
            'route' => $this->option('route') ?: null,
            'url' => $this->option('url') ?: null,
            'order' => MenuItem::query()->where('menu_id', $menu->id)->max('order') + 1,
        ]);

        $this->components->info("Menu item [{$this->argument('title')}] added to [{$menu->slug}].");

        return self::SUCCESS;
    }
}
