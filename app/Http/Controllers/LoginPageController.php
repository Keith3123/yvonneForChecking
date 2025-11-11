<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginPageController extends Controller
{
    /**
     * Show the login page.
     */
    public function index()
    {
        return view('user.LoginPage'); // matches your LoginPage.blade.php
    }

    /**
     * Handle login submission.
     */
    public function store(Request $request)
    {
        // ✅ Validate required fields
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
        ]);

        // ✅ Attempt to authenticate using Laravel Auth
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            // Regenerate session to prevent fixation
            $request->session()->regenerate();

            // Redirect to intended route or catalog
            return redirect()->intended(route('catalog'))
                ->with('success', 'Welcome back, ' . Auth::user()->username . '!');
        }

        // If login fails
        return back()->withErrors([
            'loginError' => 'Invalid username or password. Please try again.',
        ])->onlyInput('username');
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
