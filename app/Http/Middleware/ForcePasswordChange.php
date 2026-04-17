<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /** Routes the user is allowed to visit before changing their password. */
    private const ALLOWED_ROUTES = [
        'password.force-change',
        'password.force-change.update',
        'two-factor.challenge',
        'two-factor.challenge.verify',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Boot deactivated accounts that are mid-session
            if (!$user->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been deactivated. Please contact your administrator.',
                ]);
            }

            // Redirect to forced password change if flagged
            if ($user->force_password_change) {
                if (!in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
                    return redirect()->route('password.force-change');
                }
            }
        }

        return $next($request);
    }
}
