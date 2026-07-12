<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Safarjaisur\AdminPanel\Http\Requests\LoginRequest;
use Safarjaisur\AdminPanel\Models\ActivityLog;

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

        if (! Auth::guard('sjadmin')->validate($credentials)) {
            $request->throwFailedAuthenticationException();
        }

        $request->clearRateLimit();

        /** @var \Safarjaisur\AdminPanel\Models\AdminUser $user */
        $user = Auth::guard('sjadmin')->getProvider()->retrieveByCredentials($credentials);

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put('sjadmin.2fa.user_id', $user->id);
            $request->session()->put('sjadmin.2fa.remember', $request->boolean('remember'));

            return redirect()->route('sjadmin.two-factor.challenge');
        }

        Auth::guard('sjadmin')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLog::query()->create([
            'user_id' => Auth::guard('sjadmin')->id(),
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()->route(config('sjadminpanel.auth.redirect_after_login'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::guard('sjadmin')->id();

        Auth::guard('sjadmin')->logout();
        ActivityLog::query()->create([
            'user_id' => $userId,
            'action' => 'logout',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(config('sjadminpanel.auth.redirect_after_logout'));
    }
}
