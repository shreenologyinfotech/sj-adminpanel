<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'sjadmin_settings';

    protected $fillable = ['group', 'key', 'value', 'type'];

    public $timestamps = true;
}
