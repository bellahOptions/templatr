<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Profile2faController extends Controller
{
    /**
     * Show 2FA settings page.
     */
    public function index()
    {
        $user = Auth::user();

        return view('profile.2fa', compact('user'));
    }

    /**
     * Enable 2FA for the user account.
     */
    public function enable(Request $request)
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return back()->with('info', 'Two-factor authentication is already enabled.');
        }

        // Generate and send initial code to confirm
        $code = $user->generateTwoFactorCode();

        // Send the code via email
        try {
            Mail::send('emails.notification', [
                'user' => $user,
                'title' => 'Your 2FA Verification Code',
                'icon' => '🔐',
                'message' => 'You requested to enable two-factor authentication on your Templatr account. Your verification code is:',
                'actionText' => null,
            ], function ($message) use ($user, $code) {
                $message->to($user->email)
                    ->subject('Your 2FA Verification Code - Templatr')
                    ->text("Your verification code is: {$code}\n\nThis code will expire in 10 minutes.");
            });
        } catch (\Exception $e) {
            Log::warning('Failed to send 2FA enable email: '.$e->getMessage());

            return back()->with('error', 'Failed to send verification code. Please try again.');
        }

        return back()->with('success', 'A verification code has been sent to your email. Enter it below to confirm.')
            ->with('show_2fa_verify', true);
    }

    /**
     * Confirm and finalize 2FA enable.
     */
    public function confirmEnable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.2fa')->with('info', 'Two-factor authentication is already enabled.');
        }

        if (! $user->validateTwoFactorCode($request->code)) {
            return back()->with('error', 'Invalid or expired verification code. Please try again.');
        }

        $user->update(['two_factor_enabled' => true]);
        $user->resetTwoFactorCode();

        return redirect()->route('profile.2fa')->with('success', 'Two-factor authentication has been enabled successfully.');
    }

    /**
     * Disable 2FA for the user account.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
        ]);

        $user = Auth::user();

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }
}
