<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Contracts\Repositories;

use Illuminate\Support\Collection;

interface MenuRepositoryInterface
{
    public function getBySlug(string $slug): ?\Safarjaisur\AdminPanel\Models\Menu;

    public function itemsFor(string $slug): Collection;
}
