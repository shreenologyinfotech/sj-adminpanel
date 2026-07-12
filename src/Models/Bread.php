<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class Bread extends Model
{
    protected $table = 'sjadmin_breads';

    protected $fillable = ['name', 'slug', 'table_name', 'model', 'icon', 'fields'];

    protected function casts(): array
    {
        return ['fields' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
