<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /** Routes the user is allowed to visit before changing their password. */
    private const ALLOWED_ROUTES = [
        'password.force-change',
        'password.force-change.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->force_password_change) {
            if (!in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
                return redirect()->route('password.force-change');
            }
        }

        return $next($request);
    }
}
