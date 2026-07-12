<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'sjadmin_activity_log';

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }
}
