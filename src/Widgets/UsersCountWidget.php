<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Widgets;

use Safarjaisur\AdminPanel\Models\AdminUser;

class UsersCountWidget extends Widget
{
    public function handle(): array
    {
        return [
            'type' => 'stat-card',
            'title' => 'Total Users',
            'icon' => 'iconoir-group',
            'value' => AdminUser::query()->count(),
        ];
    }
}
