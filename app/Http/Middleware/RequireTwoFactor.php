<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    /** Routes that are always reachable regardless of 2FA state. */
    private const BYPASSED_ROUTES = [
        'two-factor.challenge',
        'two-factor.challenge.verify',
        'logout',
        'password.force-change',
        'password.force-change.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->hasTwoFactorEnabled()
            && !$request->session()->get('two_factor_verified')
        ) {
            $routeName = $request->route()?->getName();

            if (!in_array($routeName, self::BYPASSED_ROUTES, true)) {
                // Preserve the intended destination so we can redirect back after 2FA
                $request->session()->put('url.intended', $request->fullUrl());
                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}
