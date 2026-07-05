<?php

declare(strict_types=1);

namespace SJ\AdminPanel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'status', 'two_factor_secret'
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'sj_user_roles');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string $permissionKey): bool
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('key', $permissionKey)->exists()) {
                return true;
            }
        }
        return false;
    }
}