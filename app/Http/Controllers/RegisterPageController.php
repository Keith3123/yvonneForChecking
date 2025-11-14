<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegisterPageController extends Controller
{
    public function show()
    {
        return view('user.RegisterPage');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname'  => 'required|string|max:255',
            'lastname'   => 'required|string|max:255',
            'midInitial' => 'nullable|string|max:10',
            'email'      => 'required|email',
            'username'   => 'required|string|max:255',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        $userData = [
            'firstname'  => $validated['firstname'],
            'lastname'   => $validated['lastname'],
            'midInitial' => $validated['midInitial'] ?? null,
            'email'      => $validated['email'],
            'username'   => $validated['username'],
        ];

        session(['logged_in_user' => $userData]);

        return redirect()->route('login')->with('success', 'Account created successfully! You can now log in.');
    }
}
