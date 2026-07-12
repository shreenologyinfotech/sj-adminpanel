<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Safarjaisur\AdminPanel\Models\AdminUser;
use Safarjaisur\AdminPanel\Models\Bread;
use Safarjaisur\AdminPanel\Models\MenuItem;
use Safarjaisur\AdminPanel\Models\Role;
use Safarjaisur\AdminPanel\Models\Setting;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        return view('sjadminpanel::search.index', [
            'query' => $query,
            'results' => $query === '' ? collect() : $this->results($query),
        ]);
    }

    protected function results(string $query): Collection
    {
        $like = '%' . $query . '%';

        return collect()
            ->merge(AdminUser::query()->where('name', 'like', $like)->orWhere('email', 'like', $like)->limit(8)->get()->map(fn (AdminUser $user): array => [
                'type' => 'User',
                'title' => $user->name,
                'description' => $user->email,
                'url' => route('sjadmin.users.edit', $user),
            ]))
            ->merge(Role::query()->where('name', 'like', $like)->orWhere('slug', 'like', $like)->limit(8)->get()->map(fn (Role $role): array => [
                'type' => 'Role',
                'title' => $role->name,
                'description' => $role->slug,
                'url' => route('sjadmin.roles.edit', $role),
            ]))
            ->merge(Bread::query()->where('name', 'like', $like)->orWhere('slug', 'like', $like)->limit(8)->get()->map(fn (Bread $bread): array => [
                'type' => 'BREAD',
                'title' => $bread->name,
                'description' => $bread->table_name,
                'url' => route('sjadmin.resources.index', $bread),
            ]))
            ->merge(MenuItem::query()->where('title', 'like', $like)->orWhere('route', 'like', $like)->orWhere('url', 'like', $like)->limit(8)->get()->map(fn (MenuItem $item): array => [
                'type' => 'Menu',
                'title' => $item->title,
                'description' => $item->route ?: $item->url,
                'url' => route('sjadmin.menu.index'),
            ]))
            ->merge(Setting::query()->where('key', 'like', $like)->orWhere('value', 'like', $like)->limit(8)->get()->map(fn (Setting $setting): array => [
                'type' => 'Setting',
                'title' => $setting->key,
                'description' => (string) $setting->value,
                'url' => route('sjadmin.settings.index'),
            ]))
            ->values();
    }
}
