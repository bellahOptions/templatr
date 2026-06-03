<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerification;
use App\Notifications\VerifyEmailNotification;
use App\Services\Webhook\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    public function __construct(
        private WebhookService $webhookService
    ) {}

    /**
     * Send a new verification link to the authenticated user.
     */
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/')->with('info', 'Your email is already verified.');
        }

        // Invalidate any previous unverified tokens
        $user->emailVerifications()->whereNull('verified_at')->where('expires_at', '>', now())->update([
            'expires_at' => now(),
        ]);

        // Create new verification
        $verification = EmailVerification::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(60),
        ]);

        $user->notify(new VerifyEmailNotification($verification));

        return back()->with('success', 'A new verification link has been sent to your email.');
    }

    /**
     * Verify email using token.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'User not found. Please sign in again.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('info', 'Your email is already verified. Please sign in.');
        }

        $verification = EmailVerification::where('user_id', $user->id)
            ->where('token', $request->token)
            ->whereNull('verified_at')
            ->first();

        if (!$verification || !$verification->isValid()) {
            return redirect()->route('login')
                ->with('error', 'This verification link is invalid or has expired. Please sign in to request a new one.');
        }

        $verification->markAsVerified();
        $user->markEmailAsVerified();

        // Fire webhook for user.verified event
        try {
            $this->webhookService->fire('user.verified', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to fire user.verified webhook: ' . $e->getMessage());
        }

        return redirect()->route('login')
            ->with('success', 'Email verified successfully! You can now sign in.');
    }

    /**
     * Show verification notice page.
     */
    public function notice()
    {
        $user = request()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        return view('auth.verify-email');
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/')->with('info', 'Your email is already verified.');
        }

        // Invalidate old tokens
        $user->emailVerifications()->whereNull('verified_at')->where('expires_at', '>', now())->update([
            'expires_at' => now(),
        ]);

        // Create new verification
        $verification = EmailVerification::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(60),
        ]);

        $user->notify(new VerifyEmailNotification($verification));

        return back()->with('success', 'A new verification link has been sent to your email.');
    }
}
