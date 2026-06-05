<?php

namespace App\Http\Controllers;

use App\Models\Admin2faToken;
use App\Models\User;
use App\Notifications\Admin2faNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Admin2faController extends Controller
{
    /**
     * Show the 2FA verification form.
     */
    public function showForm()
    {
        if (! session()->has('admin_2fa_user_id')) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        return view('auth.admin-2fa');
    }

    /**
     * Send a new 2FA code to the admin's email.
     */
    public function sendCode(Request $request)
    {
        $userId = session('admin_2fa_user_id');

        if (! $userId) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        $user = User::find($userId);

        if (! $user || ! $user->isAdmin()) {
            session()->forget('admin_2fa_user_id');

            return redirect()->route('login')
                ->with('error', 'Invalid session. Please sign in again.');
        }

        // Rate limit: prevent sending codes more than once per 30 seconds
        $rateLimitKey = 'admin_2fa_send_'.$user->id;
        if (Cache::has($rateLimitKey)) {
            return back()->with('error', 'Please wait before requesting a new code.');
        }

        // Invalidate any existing unused tokens
        $user->admin2faTokens()->whereNull('used_at')->where('expires_at', '>', now())->update([
            'expires_at' => now(),
        ]);

        // Generate a new 6-digit code
        $token = new Admin2faToken([
            'user_id' => $user->id,
            'token' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'auth_code' => Str::random(64),
            'expires_at' => now()->addMinutes(10),
        ]);
        $token->save();

        // Store the auth_code for session verification
        session(['admin_2fa_auth_code' => $token->auth_code]);

        // Rate limit: 30 second cooldown
        Cache::put($rateLimitKey, true, now()->addSeconds(30));

        // Send the code via email
        $user->notify(new Admin2faNotification($token));

        return back()->with('success', 'A verification code has been sent to your email.');
    }

    /**
     * Verify the 2FA code and log the admin in.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $userId = session('admin_2fa_user_id');
        $authCode = session('admin_2fa_auth_code');

        if (! $userId || ! $authCode) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        $user = User::find($userId);

        if (! $user || ! $user->isAdmin()) {
            session()->forget(['admin_2fa_user_id', 'admin_2fa_auth_code']);

            return redirect()->route('login')
                ->with('error', 'Invalid session. Please sign in again.');
        }

        // Find the valid token
        $token = Admin2faToken::where('user_id', $user->id)
            ->where('token', $request->token)
            ->where('auth_code', $authCode)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            return back()->with('error', 'Invalid or expired verification code. Please request a new one.');
        }

        $token->markAsUsed();

        // Log the admin in
        Auth::login($user);
        session()->forget(['admin_2fa_user_id', 'admin_2fa_auth_code']);

        // Mark 2FA as verified for this session
        session()->put('admin_2fa_verified', true);

        session()->regenerate();

        return redirect()->intended('/admin/dashboard')
            ->with('success', 'Welcome back, '.$user->name.'!');
    }

    /**
     * Cancel 2FA and redirect to login.
     */
    public function cancel()
    {
        session()->forget(['admin_2fa_user_id', 'admin_2fa_auth_code']);

        return redirect()->route('login')
            ->with('info', 'Two-factor authentication cancelled. Please sign in again.');
    }
}
