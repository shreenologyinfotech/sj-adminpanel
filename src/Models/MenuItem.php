<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $table = 'sjadmin_menu_items';

    protected $fillable = [
        'menu_id', 'parent_id', 'title', 'icon',
        'url', 'route', 'target', 'permission', 'order',
    ];

    protected function casts(): array
    {
        return ['order' => 'integer'];
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function resolvedUrl(): string
    {
        if ($this->route && \Illuminate\Support\Facades\Route::has($this->route)) {
            return route($this->route);
        }

        return $this->url ?: '#';
    }
}
