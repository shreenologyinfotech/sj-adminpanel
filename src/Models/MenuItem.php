<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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

    protected static function booted(): void
    {
        $clearMenuCache = function (self $item): void {
            $slug = Menu::query()->whereKey($item->menu_id)->value('slug');

            if ($slug) {
                Cache::forget("sjadmin.menu.{$slug}");
            }
        };

        static::saved($clearMenuCache);
        static::deleted($clearMenuCache);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('order')
            ->with('children');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function resolvedUrl(): string
    {
        if ($this->route && \Illuminate\Support\Facades\Route::has($this->route)) {
            return route($this->route);
        }

        return $this->url ?: '#';
    }
}
