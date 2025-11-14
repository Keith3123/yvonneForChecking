<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginPageController extends Controller
{
    // Show login page
    public function index()
    {
        // Clear any previous dummy session for clean frontend testing
        session()->forget('logged_in_user');

        return view('user.LoginPage');
    }

    // Handle login submission (frontend simulation only)
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Simulate a successful login without checking real password
        $dummyUser = [
            'firstname' => $request->username
        ];

        session(['logged_in_user' => $dummyUser]);

        return redirect()->route('catalog')->with('success', 'Welcome back, ' . $dummyUser['firstname'] . '!');
    }

    // Handle logout
    public function logout(Request $request)
    {
        session()->forget('logged_in_user');
        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
