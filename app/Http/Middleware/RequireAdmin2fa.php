<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdmin2fa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only applies to admin users
        if (!$user || !$user->isAdmin()) {
            return $next($request);
        }

        // If the user is an admin but doesn't have 2FA session flag, redirect to 2FA
        // The 2FA session flag is set after successful 2FA verification
        if (!$request->session()->has('admin_2fa_verified')) {
            // Store user ID in session for 2FA page
            $request->session()->put('admin_2fa_user_id', $user->id);

            // Log them out temporarily so they're redirected to 2FA
            auth()->logout();

            return redirect()->route('admin.2fa.form')
                ->with('info', 'Please verify your identity to access the admin area.');
        }

        return $next($request);
    }
}
