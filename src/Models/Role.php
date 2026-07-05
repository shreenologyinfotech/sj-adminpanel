<?php

declare(strict_types=1);

namespace SJ\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'sj_roles';
    
    protected $fillable = ['name', 'display_name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'sj_role_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'sj_user_roles');
    }
}