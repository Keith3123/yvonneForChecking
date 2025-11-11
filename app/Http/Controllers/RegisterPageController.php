<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegisterPageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname'  => 'required|string|max:255',
            'lastname'   => 'required|string|max:255',
            'midInitial' => 'nullable|string|max:10',
            'email'      => 'required|email',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        // Simulate logged-in user
        $userData = [
            'firstname'  => $validated['firstname'],
            'lastname'   => $validated['lastname'],
            'midInitial' => $validated['midInitial'] ?? null,
            'email'      => $validated['email'],
        ];

        session(['logged_in_user' => $userData]);

        return redirect()->route('catalog')->with('success', 'Account created successfully! Welcome!');
    }
}
