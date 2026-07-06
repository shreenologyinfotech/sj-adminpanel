<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Repositories;

use Illuminate\Support\Facades\Cache;
use Safarjaisur\AdminPanel\Contracts\Repositories\SettingRepositoryInterface;
use Safarjaisur\AdminPanel\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('sjadmin.settings', fn () => Setting::query()->pluck('value', 'key'));

        return $settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );

        Cache::forget('sjadmin.settings');
    }

    public function group(string $group): array
    {
        return Setting::query()->where('group', $group)->pluck('value', 'key')->all();
    }
}
