<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless(Auth::guard('sjadmin')->user()?->hasRole($role), 403);

        return $next($request);
    }
}
