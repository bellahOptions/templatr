<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Routes that 2FA should be skipped for.
     */
    protected array $except = [
        'profile.2fa.login',
        'profile.2fa.login.verify',
        'profile.2fa.login.resend',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Skip 2FA for admins (they have their own separate 2FA)
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Skip 2FA for the routes in the except array
        $routeName = $request->route()?->getName();
        if (in_array($routeName, $this->except, true)) {
            return $next($request);
        }

        // If user has 2FA enabled and hasn't verified in this session
        if ($user->hasTwoFactorEnabled() && ! $request->session()->get('user_2fa_verified')) {
            return redirect()->route('profile.2fa.login');
        }

        return $next($request);
    }
}
