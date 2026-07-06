<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Contracts\Repositories;

interface SettingRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void;

    public function group(string $group): array;
}
