<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Webhook\WebhookService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

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
     * Mark the authenticated user's email as verified.
     * Uses Laravel's signed URL for security.
     */
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if (!hash_equals((string) $request->route('id'), (string) $user->getKey())) {
            abort(403);
        }

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')
                ->with('info', 'Your email is already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

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

        // Log the user in and redirect to dashboard
        \Illuminate\Support\Facades\Auth::login($user);

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
