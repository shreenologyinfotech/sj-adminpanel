<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'sjadmin_menus';

    protected $fillable = ['name', 'slug'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->orderBy('order');
    }
}
