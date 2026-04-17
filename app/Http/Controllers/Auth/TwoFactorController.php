<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    // ── Login challenge ───────────────────────────────────────────────────

    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        if ($request->session()->get('two_factor_verified')) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code'          => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $user = $request->user();

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        $code         = trim($request->input('code', ''));
        $recoveryCode = trim($request->input('recovery_code', ''));

        if ($recoveryCode !== '') {
            $codes = $user->two_factor_recovery_codes ?? [];
            $index = array_search($recoveryCode, $codes, true);

            if ($index === false) {
                return back()
                    ->withErrors(['recovery_code' => 'Invalid recovery code.'])
                    ->with('show_recovery', true);
            }

            // Burn the used code
            unset($codes[$index]);
            $user->update(['two_factor_recovery_codes' => array_values($codes)]);
            AuditLog::record('2fa_recovery_used', 'Signed in using a 2FA recovery code', 'User', $user->id);
        } else {
            $google2fa = new Google2FA();
            $secret    = $user->two_factor_secret;

            if (!$secret || !$google2fa->verifyKey($secret, $code)) {
                return back()
                    ->withErrors(['code' => 'Invalid authentication code. Please try again.'])
                    ->onlyInput('code');
            }
        }

        $request->session()->put('two_factor_verified', true);
        AuditLog::record('2fa_verified', '2FA challenge passed successfully', 'User', $user->id);

        return redirect()->intended(route('dashboard'));
    }

    // ── Setup (enable 2FA) ────────────────────────────────────────────────

    public function setup(Request $request): View
    {
        $user      = $request->user();
        $google2fa = new Google2FA();

        // Persist a pending secret in session so the same QR is shown on refresh
        if (!$request->session()->has('2fa_setup_secret')) {
            $secret = $google2fa->generateSecretKey(32);
            $request->session()->put('2fa_setup_secret', $secret);
        } else {
            $secret = $request->session()->get('2fa_setup_secret');
        }

        $settings    = Setting::all()->pluck('value', 'key');
        $companyName = $settings['company_name'] ?? config('app.name');

        $qrCodeUri = $google2fa->getQRCodeUrl($companyName, $user->email, $secret);

        return view('auth.two-factor-setup', [
            'secret'    => $secret,
            'qrCodeUri' => $qrCodeUri,
            'step'      => 'scan',
        ]);
    }

    public function confirm(Request $request): RedirectResponse|View
    {
        $request->validate([
            'code' => 'required|string|digits:6',
        ]);

        $user   = $request->user();
        $secret = $request->session()->get('2fa_setup_secret');

        if (!$secret) {
            return redirect()->route('two-factor.setup')
                ->withErrors(['code' => 'Setup session expired. Please start again.']);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()
                ->withErrors(['code' => 'Incorrect code — please check your authenticator app and try again.'])
                ->withInput();
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at'   => now(),
        ]);

        $request->session()->forget('2fa_setup_secret');
        $request->session()->put('two_factor_verified', true);

        AuditLog::record('2fa_enabled', '2FA was enabled via authenticator app', 'User', $user->id);

        return view('auth.two-factor-setup', [
            'secret'        => null,
            'qrCodeUri'     => null,
            'step'          => 'complete',
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    // ── Disable 2FA ───────────────────────────────────────────────────────

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $request->user()->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        $request->session()->forget('two_factor_verified');

        AuditLog::record('2fa_disabled', '2FA was disabled', 'User', $request->user()->id);

        return redirect()->route('profile.edit')->with('status', '2fa-disabled');
    }

    // ── Recovery codes ────────────────────────────────────────────────────

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $codes = $this->generateRecoveryCodes();
        $request->user()->update(['two_factor_recovery_codes' => $codes]);

        AuditLog::record('2fa_recovery_regenerated', '2FA recovery codes were regenerated', 'User', $request->user()->id);

        return redirect()->route('profile.edit')
            ->with('status', '2fa-recovery-regenerated')
            ->with('new_recovery_codes', $codes);
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5))) . '-' . strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }
}
