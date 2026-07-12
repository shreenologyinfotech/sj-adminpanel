<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Safarjaisur\AdminPanel\Models\AdminUser;
use Safarjaisur\AdminPanel\Models\Menu;
use Safarjaisur\AdminPanel\Models\MenuItem;
use Safarjaisur\AdminPanel\Models\Permission;
use Safarjaisur\AdminPanel\Models\Role;

class AdminPanelSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->seedAdmin();
        $adminRole = $this->seedRolesAndPermissions();
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        $this->seedMenu();
        $this->seedSettings();
    }

    protected function seedAdmin(): AdminUser
    {
        return AdminUser::query()->firstOrCreate(
            ['email' => config('sjadminpanel.default_admin.email')],
            [
                'name' => config('sjadminpanel.default_admin.name'),
                'password' => Hash::make(config('sjadminpanel.default_admin.password')),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }

    protected function seedRolesAndPermissions(): Role
    {
        $adminRole = Role::query()->firstOrCreate(
            ['slug' => 'administrator'],
            ['name' => 'Administrator', 'description' => 'Full system access']
        );

        $permissions = [
            'dashboard.view' => 'Dashboard',
            'users.manage' => 'Users',
            'roles.manage' => 'Roles & Permissions',
            'bread.manage' => 'BREAD Builder',
            'database.manage' => 'Database Manager',
            'menu.manage' => 'Menu Builder',
            'media.manage' => 'Media Manager',
            'files.manage' => 'File Manager',
            'settings.manage' => 'Settings',
            'backups.manage' => 'Backup Manager',
            'logs.view' => 'Log Viewer',
            'activity.view' => 'Activity Log',
        ];

        foreach ($permissions as $slug => $group) {
            $permission = Permission::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => str($slug)->before('.')->headline()->value(), 'group' => $group]
            );

            $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        return $adminRole;
    }

    protected function seedMenu(): void
    {
        $menu = Menu::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin Sidebar']);

        $items = [
            ['title' => 'Dashboard', 'icon' => 'iconoir-home-alt', 'route' => 'sjadmin.dashboard', 'order' => 1],
            ['title' => 'Users', 'icon' => 'iconoir-group', 'route' => 'sjadmin.users.index', 'permission' => 'users.manage', 'order' => 2],
            ['title' => 'Roles & Permissions', 'icon' => 'iconoir-lock', 'route' => 'sjadmin.roles.index', 'permission' => 'roles.manage', 'order' => 3],
            ['title' => 'BREAD Builder', 'icon' => 'iconoir-view-grid', 'route' => 'sjadmin.bread.index', 'permission' => 'bread.manage', 'order' => 4],
            ['title' => 'Database Manager', 'icon' => 'iconoir-database', 'route' => 'sjadmin.database.index', 'permission' => 'database.manage', 'order' => 5],
            ['title' => 'Menu Builder', 'icon' => 'iconoir-menu', 'route' => 'sjadmin.menu.index', 'permission' => 'menu.manage', 'order' => 6],
            ['title' => 'Media Manager', 'icon' => 'iconoir-media-image', 'route' => 'sjadmin.media.index', 'permission' => 'media.manage', 'order' => 7],
            ['title' => 'File Manager', 'icon' => 'iconoir-folder', 'route' => 'sjadmin.files.index', 'permission' => 'files.manage', 'order' => 8],
            ['title' => 'Settings', 'icon' => 'iconoir-settings', 'route' => 'sjadmin.settings.index', 'permission' => 'settings.manage', 'order' => 9],
            ['title' => 'Backups', 'icon' => 'iconoir-archive', 'route' => 'sjadmin.backups.index', 'permission' => 'backups.manage', 'order' => 10],
            ['title' => 'Logs', 'icon' => 'iconoir-page', 'route' => 'sjadmin.logs.index', 'permission' => 'logs.view', 'order' => 11],
            ['title' => 'Activity Log', 'icon' => 'iconoir-list', 'route' => 'sjadmin.activity.index', 'permission' => 'activity.view', 'order' => 12],
        ];

        foreach ($items as $item) {
            MenuItem::query()->updateOrCreate(
                ['menu_id' => $menu->id, 'route' => $item['route']],
                array_merge(['menu_id' => $menu->id], $item)
            );
        }

        Cache::forget('sjadmin.menu.admin');
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'site.name', 'value' => 'Admin Panel', 'type' => 'text'],
            ['group' => 'general', 'key' => 'site.tagline', 'value' => null, 'type' => 'text'],
            ['group' => 'general', 'key' => 'site.logo', 'value' => null, 'type' => 'image'],
            ['group' => 'general', 'key' => 'site.favicon', 'value' => null, 'type' => 'image'],
            ['group' => 'general', 'key' => 'site.maintenance_mode', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'appearance', 'key' => 'theme.dark_mode', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'appearance', 'key' => 'theme.rtl', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'seo', 'key' => 'seo.title', 'value' => null, 'type' => 'text'],
            ['group' => 'seo', 'key' => 'seo.description', 'value' => null, 'type' => 'textarea'],
            ['group' => 'seo', 'key' => 'seo.keywords', 'value' => null, 'type' => 'textarea'],
            ['group' => 'mail', 'key' => 'mail.from_address', 'value' => null, 'type' => 'text'],
            ['group' => 'mail', 'key' => 'mail.from_name', 'value' => 'Admin Panel', 'type' => 'text'],
            ['group' => 'social', 'key' => 'social.facebook', 'value' => null, 'type' => 'text'],
            ['group' => 'social', 'key' => 'social.instagram', 'value' => null, 'type' => 'text'],
            ['group' => 'social', 'key' => 'social.linkedin', 'value' => null, 'type' => 'text'],
            ['group' => 'analytics', 'key' => 'analytics.google_id', 'value' => null, 'type' => 'text'],
            ['group' => 'analytics', 'key' => 'analytics.scripts', 'value' => null, 'type' => 'textarea'],
            ['group' => 'advanced', 'key' => 'advanced.custom_json', 'value' => null, 'type' => 'json'],
        ];

        foreach ($settings as $setting) {
            \Safarjaisur\AdminPanel\Models\Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
