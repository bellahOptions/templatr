<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class User2faLoginController extends Controller
{
    /**
     * Show the 2FA verification form.
     * Auto-sends a code if one hasn't been sent recently.
     */
    public function showForm()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->intended('/');
        }

        if (session()->get('user_2fa_verified')) {
            return redirect()->intended('/');
        }

        // Only send a new code if we haven't sent one in the last 60 seconds
        $cacheKey = '2fa_sent_'.$user->id;
        if (! Cache::has($cacheKey)) {
            $this->sendNewCode($user);
            Cache::put($cacheKey, true, now()->addSeconds(60));
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

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        if (! $user->validateTwoFactorCode($request->code)) {
            // Send a new code automatically on failed attempt
            $this->sendNewCode($user);

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

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        $this->sendNewCode($user);

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    /**
     * Generate and send a new 2FA code.
     */
    private function sendNewCode($user): void
    {
        $code = $user->generateTwoFactorCode();

        try {
            Mail::send([], [], function ($message) use ($user, $code) {
                $message->to($user->email)
                    ->subject('Login Verification Code - Templatr')
                    ->text("Your Templatr login verification code is: {$code}\n\nThis code will expire in 10 minutes.\n\nIf you did not attempt to login, please secure your account immediately.");
            });
        } catch (\Exception $e) {
            Log::warning('Failed to send 2FA login code: '.$e->getMessage());
        }
    }
}
