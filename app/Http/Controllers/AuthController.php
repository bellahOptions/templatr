<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\User;
use App\Services\Webhook\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private WebhookService $webhookService
    ) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // If admin, redirect to 2FA verification instead of dashboard
            if ($user->isAdmin()) {
                Auth::logout();
                $request->session()->put('admin_2fa_user_id', $user->id);

                return redirect()->route('admin.2fa.form')
                    ->with('info', 'A verification code has been sent to your email.');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        $referralCode = request('ref');

        return view('auth.register', compact('referralCode'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => 'nullable|string|exists:users,referral_code',
            'terms_accepted' => ['required', 'accepted'],
        ], [
            'terms_accepted.required' => 'You must accept the Terms of Service to create an account.',
            'terms_accepted.accepted' => 'You must accept the Terms of Service to create an account.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'terms_accepted_at' => now(),
        ]);

        // Handle referral
        if ($referralCode = $validated['referral_code'] ?? $request->query('ref')) {
            $referrer = User::where('referral_code', $referralCode)->first();
            if ($referrer && $referrer->id !== $user->id) {
                $user->update(['referred_by' => $referrer->id]);

                Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_user_id' => $user->id,
                    'email' => $user->email,
                    'code' => $referralCode,
                    'status' => 'joined',
                    'joined_at' => now(),
                ]);
            }
        }

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        Auth::login($user);

        // Fire webhook for user.registered event
        try {
            $this->webhookService->fire('user.registered', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to fire user.registered webhook: '.$e->getMessage());
        }

        return redirect()->route('verification.notice')
            ->with('success', 'Account created successfully! Please check your email to verify your account.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
