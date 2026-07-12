<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Safarjaisur\AdminPanel\Models\ActivityLog;
use Safarjaisur\AdminPanel\Models\AdminUser;
use Safarjaisur\AdminPanel\Services\TwoFactorAuthenticationService;

class TwoFactorChallengeController extends Controller
{
    public function __construct(protected TwoFactorAuthenticationService $twoFactor)
    {
    }

    public function show(Request $request): \Illuminate\View\View|RedirectResponse
    {
        if (! $request->session()->has('sjadmin.2fa.user_id')) {
            return redirect()->route('sjadmin.login');
        }

        return view('sjadminpanel::auth.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('sjadmin.2fa.user_id');

        if (! $userId) {
            return redirect()->route('sjadmin.login');
        }

        $request->validate(['code' => ['required', 'string']]);

        $user = AdminUser::query()->find($userId);

        if (! $user) {
            return redirect()->route('sjadmin.login');
        }

        $code = $request->string('code')->value();
        $usedRecoveryCode = false;

        $isValid = $this->twoFactor->verify((string) $user->two_factor_secret, $code);

        if (! $isValid && collect($user->two_factor_recovery_codes)->contains(fn (string $recovery) => hash_equals($recovery, $code))) {
            $isValid = true;
            $usedRecoveryCode = true;
        }

        if (! $isValid) {
            return back()->withErrors(['code' => 'That code did not match. Please try again.']);
        }

        if ($usedRecoveryCode) {
            $user->replaceRecoveryCode($code);
        }

        $request->session()->forget('sjadmin.2fa.user_id');
        $remember = $request->session()->pull('sjadmin.2fa.remember', false);

        Auth::guard('sjadmin')->login($user, $remember);
        $request->session()->regenerate();

        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()->route(config('sjadminpanel.auth.redirect_after_login'));
    }
}
