<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorizes an action against a route-bound Bread record.
 *
 * Voyager-style data types carry one permission per ability
 * (browse/read/edit/add/delete) named "{bread-slug}.{ability}".
 * A user is granted access if they hold that specific permission,
 * OR the umbrella "bread.manage" permission (so existing
 * administrators are never locked out by upgrading this middleware).
 */
class EnsureBreadPermission
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = Auth::guard('sjadmin')->user();

        abort_unless($user, 403);

        $bread = $request->route('bread');
        $slug = is_object($bread) ? $bread->slug : (string) $bread;

        $authorized = $user->hasPermission("{$slug}.{$ability}")
            || $user->hasPermission('bread.manage');

        abort_unless($authorized, 403);

        return $next($request);
    }
}
