<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Safarjaisur\AdminPanel\Facades\AdminPanel;
use Safarjaisur\AdminPanel\Models\AdminUser;

class DashboardController extends Controller
{
    public function index(): View
    {
        $widgets = AdminPanel::widgets()->map(fn (string $class) => app($class)->handle());

        return view('sjadminpanel::dashboard.index', [
            'widgets' => $widgets,
            'usersCount' => AdminUser::query()->count(),
            'recentUsers' => AdminUser::query()->latest()->limit(5)->get(),
        ]);
    }
}
