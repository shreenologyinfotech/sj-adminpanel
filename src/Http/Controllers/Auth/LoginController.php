<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Safarjaisur\AdminPanel\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('sjadminpanel::auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');

        if (! Auth::guard('sjadmin')->attempt($credentials, $request->boolean('remember'))) {
            $request->throwFailedAuthenticationException();
        }

        $request->session()->regenerate();
        $request->clearRateLimit();

        return redirect()->route(config('sjadminpanel.auth.redirect_after_login'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('sjadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(config('sjadminpanel.auth.redirect_after_logout'));
    }
}
