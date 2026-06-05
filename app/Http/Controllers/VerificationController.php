<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Webhook\WebhookService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    public function __construct(
        private WebhookService $webhookService
    ) {}

    /**
     * Show the email verification notice page.
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
     * Verify the user's email using a signed URL.
     */
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('id'), (string) $user->getKey())) {
            abort(403);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        // If a *different* user is already authenticated, do not hijack their session.
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->id !== $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($user->hasVerifiedEmail()) {
            Auth::login($user, true);

            return redirect()->route('dashboard')
                ->with('info', 'Your email is already verified.');
        }

        $user->email_verified_at = now();
        $user->save();

        event(new Verified($user));

        try {
            $this->webhookService->fire('user.verified', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to fire user.verified webhook: '.$e->getMessage());
        }

        $user->refresh();
        Auth::login($user, true);

        return redirect()->route('dashboard')
            ->with('success', 'Email verified successfully! Welcome to your dashboard.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/')->with('info', 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'A new verification link has been sent to your email.');
    }
}
