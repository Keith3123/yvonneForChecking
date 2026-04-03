<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\DTO\CustomerLoginDTO;
use App\Services\CustomerLoginService;
use App\Models\User;

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
        // Clear any existing sessions
        session()->forget('logged_in_user');
        session()->forget('admin_user');
        return view('user.LoginPage');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->username);
        $password = trim($request->password);

        // ===================== MASTER ADMIN =====================
        if ($username === $this->masterAdmin['username'] && $password === $this->masterAdmin['password']) {
            session(['admin_user' => [
                'username' => 'masteradmin',
                'roleID'   => 1
            ]]);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome Master Admin!');
        }

        // ===================== SYSTEM USERS =====================
        $user = User::where('username', $username)->first();

        // Log attempt for debugging
        Log::info('Login attempt', [
            'username'      => $username,
            'username_hex'  => bin2hex($username),
            'password_hex'  => bin2hex($password),
            'user_found'    => $user ? true : false,
            'user_password' => $user ? $user->password : null,
            'user_status'   => $user ? $user->status : null,
        ]);

        if ($user) {

            // 🚫 Block inactive
            if ($user->status == 0) {
                return back()->withErrors([
                    'loginError' => 'Your account has been deactivated. Please contact admin.'
                ]);
            }

            // ❌ Wrong password → STOP here
            if (!Hash::check($password, $user->password)) {
                return back()->withErrors([
                    'loginError' => 'Invalid credentials.'
                ]);
            }

            // ✅ Correct password → login
            session(['admin_user' => [
                'userID'   => $user->userID,
                'username' => $user->username,
                'roleID'   => $user->roleID
            ]]);


        // Role-based redirect
        switch ($user->roleID) {
            case 1: return redirect()->route('admin.dashboard')->with('success', 'Welcome ' . $user->username . '!');
            case 2: return redirect()->route('admin.inventory')->with('success', 'Welcome to Inventory Dashboard, ' . $user->username . '!');
            case 3: return redirect()->route('admin.orders')->with('success', 'Welcome to Orders Dashboard, ' . $user->username . '!');
            case 4: return redirect()->route('admin.paluwagan')->with('success', 'Welcome to Paluwagan Dashboard, ' . $user->username . '!');
            case 5: return redirect()->route('admin.salesreport')->with('success', 'Welcome to Sales Report Dashboard, ' . $user->username . '!');
            case 6: return redirect()->route('admin.products')->with('success', 'Welcome to Products Dashboard, ' . $user->username . '!');
            case 7: return redirect()->route('admin.users')->with('success', 'Welcome to Users Management Dashboard, ' . $user->username . '!');
            default: return redirect()->route('admin.dashboard')->with('success', 'Welcome ' . $user->username . '!');
        }
    }

        // ===================== CUSTOMER LOGIN =====================
        $dto = new CustomerLoginDTO($request->only('username', 'password'));
        $customer = $this->loginService->login($dto);

        if (!$customer) {
            return back()->withErrors(['loginError' => 'Invalid credentials or inactive account.']);
        }

        session(['logged_in_user' => [
            'customerID' => $customer->customerID,
            'firstname'  => $customer->firstName,
            'lastname'   => $customer->lastName,
            'username'   => $customer->username,
        ]]);

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo) {
            return redirect($redirectTo)->with('success', 'Welcome, ' . $customer->firstName . '!');
        }

        return redirect()->route('catalog')->with('success', 'Welcome back, ' . $customer->firstName . '!');
    }

    public function logout(Request $request)
    {
        session()->forget('logged_in_user');
        session()->forget('admin_user');
        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}