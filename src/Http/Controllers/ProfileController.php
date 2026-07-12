<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Safarjaisur\AdminPanel\Services\TwoFactorAuthenticationService;

class ProfileController extends Controller
{
    public function __construct(protected TwoFactorAuthenticationService $twoFactor)
    {
    }

    public function edit(): View
    {
        $user = Auth::guard('sjadmin')->user();

        // A secret pending confirmation is kept in the session only —
        // nothing is persisted to the user record until they prove
        // possession of it by submitting a valid code.
        $pendingSecret = session('sjadmin.2fa.pending_secret');

        return view('sjadminpanel::profile.edit', [
            'user' => $user,
            'pendingSecret' => $pendingSecret,
            'pendingQrCodeUrl' => $pendingSecret ? $this->twoFactor->qrCodeUrl($pendingSecret, $user->email, config('app.name')) : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('sjadmin')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:sjadmin_users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', config('sjadminpanel.storage.disk'));
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    /**
     * Step 1: generate a secret and stash it in the session, then show
     * the QR code / manual key for the user to add to their authenticator app.
     */
    public function enableTwoFactor(): RedirectResponse
    {
        session(['sjadmin.2fa.pending_secret' => $this->twoFactor->generateSecretKey()]);

        return back();
    }

    /**
     * Step 2: the user proves they scanned the code correctly. Only now
     * do we persist the secret and issue recovery codes.
     */
    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $secret = session('sjadmin.2fa.pending_secret');

        if (! $secret || ! $this->twoFactor->verify($secret, $request->string('code')->value())) {
            return back()->withErrors(['code' => 'That code is invalid or has expired. Please try again.']);
        }

        Auth::guard('sjadmin')->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $this->twoFactor->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        session()->forget('sjadmin.2fa.pending_secret');

        return back()->with('success', 'Two-factor authentication is now enabled. Store your recovery codes somewhere safe.');
    }

    public function disableTwoFactor(): RedirectResponse
    {
        Auth::guard('sjadmin')->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Two-factor authentication disabled.');
    }

    public function regenerateRecoveryCodes(): RedirectResponse
    {
        $user = Auth::guard('sjadmin')->user();

        abort_unless($user->hasEnabledTwoFactorAuthentication(), 400);

        $user->forceFill(['two_factor_recovery_codes' => $this->twoFactor->generateRecoveryCodes()])->save();

        return back()->with('success', 'New recovery codes generated. Store them somewhere safe.');
    }
}
