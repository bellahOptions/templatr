<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class User2faLoginController extends Controller
{
    /**
     * Show the 2FA verification form.
     */
    public function showForm()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->intended('/');
        }

        if (session()->get('user_2fa_verified')) {
            return redirect()->intended('/');
        }

        // Send a new code
        $code = $user->generateTwoFactorCode();

        // Send via email
        try {
            \Illuminate\Support\Facades\Mail::send('emails.notification', [
                'user' => $user,
                'title' => 'Your Login Verification Code',
                'icon' => '🔐',
                'message' => 'You are signing in to your Templatr account. Your one-time verification code is below.',
                'actionText' => null,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Login Verification Code - Templatr');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send 2FA login code: ' . $e->getMessage());
        }

        return view('auth.user-2fa-login');
    }

    /**
     * Verify the 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        if (!$user->validateTwoFactorCode($request->code)) {
            return back()->with('error', 'Invalid or expired verification code. A new code has been sent to your email.');
        }

        $user->resetTwoFactorCode();
        session()->put('user_2fa_verified', true);

        return redirect()->intended('/')
            ->with('success', 'Successfully verified! Welcome back.');
    }

    /**
     * Resend the verification code.
     */
    public function resend()
    {
        $user = Auth::user();

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        $code = $user->generateTwoFactorCode();

        try {
            \Illuminate\Support\Facades\Mail::send('emails.notification', [
                'user' => $user,
                'title' => 'Your Login Verification Code',
                'icon' => '🔐',
                'message' => 'A new verification code has been generated for your Templatr login.',
                'actionText' => null,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('New Login Verification Code - Templatr');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to resend 2FA code: ' . $e->getMessage());
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }
}
