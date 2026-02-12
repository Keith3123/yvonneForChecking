<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DTO\CustomerLoginDTO;
use App\Services\CustomerLoginService;

class LoginPageController extends Controller
{
    // Master Admin Login credentials
    private $masterAdmin = [
        'username' => 'masteradmin',
        'password' => 'supersecret123',
    ];

    protected CustomerLoginService $loginService;

    public function __construct(CustomerLoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function index()
    {
        session()->forget('logged_in_user');
        return view('user.LoginPage');
    }

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // ---------------------------- ADMIN LOGIN CHECK ----------------------------
        if (
            $request->username === $this->masterAdmin['username'] &&
            $request->password === $this->masterAdmin['password']
        ) {
            session(['admin_logged_in' => true]);
            return redirect()->route('admin.dashboard')->with('success', 'Welcome Master Admin!');
        }

        // ---------------------------- CUSTOMER LOGIN ----------------------------
        $dto = new CustomerLoginDTO($request->only('username', 'password'));
        $customer = $this->loginService->login($dto);

        if (!$customer) {
            return back()->withErrors(['loginError' => 'Invalid credentials or inactive account.']);
        }

        session(['logged_in_user' => [
            'customerID' => $customer->customerID,
            'firstname' => $customer->firstName,
            'lastname'  => $customer->lastName,
            'username'  => $customer->username,
        ]]);
        


        return redirect()->route('catalog')->with('success', 'Welcome back, ' . $customer->firstName . '!');
    }

    // Handle logout
    public function logout(Request $request)
    {
        session()->forget('logged_in_user');
        session()->forget('admin_logged_in');

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
