<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless(Auth::guard('sjadmin')->user()?->hasPermission($permission), 403);

        return $next($request);
    }
}
