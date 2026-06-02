<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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
            if ($user->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            }
            return redirect()->intended('/');
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
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        // Handle referral
        if ($referralCode = $validated['referral_code'] ?? $request->query('ref')) {
            $referrer = User::where('referral_code', $referralCode)->first();
            if ($referrer && $referrer->id !== $user->id) {
                // Link the user to the referrer
                $user->update(['referred_by' => $referrer->id]);

                // Create referral record
                \App\Models\Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_user_id' => $user->id,
                    'email' => $user->email,
                    'code' => $referralCode,
                    'status' => 'joined',
                    'joined_at' => now(),
                ]);
            }
        }

        Auth::login($user);
        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

