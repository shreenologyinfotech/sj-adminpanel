<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Safarjaisur\AdminPanel\Contracts\Repositories\MenuRepositoryInterface;
use Safarjaisur\AdminPanel\Models\Menu;

class MenuRepository implements MenuRepositoryInterface
{
    public function getBySlug(string $slug): ?Menu
    {
        return Menu::query()->where('slug', $slug)->first();
    }

    public function itemsFor(string $slug): Collection
    {
        return Cache::remember("sjadmin.menu.{$slug}", now()->addMinutes(30), function () use ($slug) {
            $menu = $this->getBySlug($slug);

            if (! $menu) {
                return collect();
            }

            return $menu->items()->with('children.children')->get();
        });
    }
}
