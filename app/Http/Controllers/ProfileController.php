<?php

namespace App\Http\Controllers;

use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = $user->orders()->with('items.product')->latest()->take(5)->get();
        $products = $user->products()->with('category')->latest()->take(5)->get();

        return view('profile.index', compact('user', 'orders', 'products'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'paypal_email' => 'nullable|email|max:255',
        ]);

        // Email change is NOT allowed — remove from validated data
        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Notify user about password change
        Auth::user()->notify(new PasswordChangedNotification());

        return back()->with('success', 'Password updated successfully.');
    }
}

