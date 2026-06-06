<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsOfService;
use App\Models\User;
use App\Notifications\TermsUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TermsController extends Controller
{
    public function edit()
    {
        $terms = TermsOfService::latest()->first();

        return view('admin.terms.edit', compact('terms'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:10'],
        ]);

        $terms = TermsOfService::create([
            'content' => $validated['content'],
            'updated_by' => Auth::id(),
        ]);

        User::chunk(100, function ($users) use ($terms) {
            foreach ($users as $user) {
                $user->notify(new TermsUpdatedNotification($terms));
            }
        });

        return redirect()->route('admin.terms.edit')
            ->with('success', 'Terms of Service updated. All users will be notified by email.');
    }
}
