<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Skip 2FA for admins (they have their own separate 2FA)
        if ($user->isAdmin()) {
            return $next($request);
        }

        // If user has 2FA enabled and hasn't verified in this session
        if ($user->hasTwoFactorEnabled() && !$request->session()->get('user_2fa_verified')) {
            return redirect()->route('profile.2fa.login');
        }

        return $next($request);
    }
}
