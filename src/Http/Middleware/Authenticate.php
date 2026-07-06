<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('sjadmin')->check()) {
            return redirect()->route('sjadmin.login');
        }

        return $next($request);
    }
}
