<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'sjadmin_permissions';

    protected $fillable = ['name', 'slug', 'group'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'sjadmin_permission_role', 'permission_id', 'role_id');
    }
}
