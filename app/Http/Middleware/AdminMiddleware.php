<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access. Admin only.');
        }

        // Ensure 2FA has been verified for this session
        if (!$request->session()->has('admin_2fa_verified')) {
            $request->session()->put('admin_2fa_user_id', Auth::user()->id);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.2fa.form')
                ->with('info', 'Session expired. Please verify your identity again.');
        }

        return $next($request);
    }
}
