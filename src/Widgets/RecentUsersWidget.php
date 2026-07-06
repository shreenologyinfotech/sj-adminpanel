<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Widgets;

use Safarjaisur\AdminPanel\Models\AdminUser;

class RecentUsersWidget extends Widget
{
    public function handle(): array
    {
        return [
            'type' => 'table',
            'title' => 'Latest Users',
            'rows' => AdminUser::query()->latest()->limit(5)->get(),
        ];
    }
}
