<?php

namespace App\Http\Controllers;

use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                'title' => 'Two-Factor Authentication Enabled',
                'icon' => '🔐',
                'message' => 'You have enabled two-factor authentication on your Templatr account. Your verification code is:',
                'actionText' => null,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('2FA Enabled - Templatr');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send 2FA enable email: ' . $e->getMessage());
        }

        // Store the code in session for verification
        session(['2fa_enable_code' => $code]);

        return back()->with('success', 'Two-factor authentication is being enabled. Please check your email for the verification code.')
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

        if (!$user->validateTwoFactorCode($request->code)) {
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
