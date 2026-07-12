<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::guard('sjadmin')->user()
            ->notifications()
            ->paginate(config('sjadminpanel.pagination.per_page'));

        return view('sjadminpanel::notifications.index', compact('notifications'));
    }

    public function markRead(string $notification): RedirectResponse
    {
        Auth::guard('sjadmin')->user()
            ->notifications()
            ->where('id', $notification)
            ->first()
            ?->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::guard('sjadmin')->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
