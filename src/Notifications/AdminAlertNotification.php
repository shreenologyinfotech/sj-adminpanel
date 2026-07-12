<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * A single generic notification type covers every admin-panel alert
 * (new user, backup finished, etc.) rather than one class per event —
 * simple to dispatch, and the header dropdown only needs to read
 * "message"/"url"/"level" out of the data payload either way.
 */
class AdminAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $message,
        protected ?string $url = null,
        protected string $level = 'info',
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'level' => $this->level,
        ];
    }
}
