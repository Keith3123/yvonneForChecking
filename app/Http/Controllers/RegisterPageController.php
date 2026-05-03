<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DTO\CustomerDTO;
use App\Services\CustomerService;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class RegisterPageController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function show()
    {
        return view('user.RegisterPage');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName'  => 'required|string|max:255',
            'mi'        => 'nullable|string|max:1',
            'phone' => [
                        'required',
                        'digits:11',
                        function ($attribute, $value, $fail) {
                        if (\App\Models\Customer::where('phone', $value)->count() >= 5) {
                        $fail('This phone number has reached the maximum of 5 accounts.');
                    }
                },
            ],
            'email'     => 'nullable|email|unique:customer,email',
            'address'   => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:customer,username',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (!session('phone_verified') || session('register_phone') !== $request->phone) {
        return redirect()->back()
        ->withErrors(['phone' => 'Please verify your phone number first.'])
        ->withInput();
        }

        $customerDTO = new CustomerDTO($request->all());
        $this->customerService->register($customerDTO);

        session()->forget([
        'register_phone',
        'register_phone_otp',
        'register_phone_otp_expires_at',
        'phone_verified'
]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully!'
            ]);
        }
        
        return redirect()->route('login')->with('success', 'Account created successfully!');
    }

    public function checkUsername(Request $request)
    {
        $exists = Customer::where('username', $request->username)->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function checkEmail(Request $request)
    {
        $exists = Customer::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
        'phone' => 'required|digits:11',
    ]);

    $count = Customer::where('phone', $request->phone)->count();

        if ($count >= 5) {
            return response()->json([
            'success' => false,
            'message' => 'This phone number has reached the maximum of 5 accounts.'
        ], 422);
    }

        $otp = rand(100000, 999999);

        session([
        'register_phone' => $request->phone,
        'register_phone_otp' => $otp,
        'register_phone_otp_expires_at' => now()->addMinutes(5),
        ]);

        \Log::info("Phone OTP for {$request->phone}: {$otp}");

        return response()->json([
        'success' => true,
        'message' => 'OTP sent successfully.'
        ]);
    }
    
    public function verifyOtp(Request $request)
    {
        $request->validate([
        'otp' => 'required|digits:6'
    ]);

        if (!session('register_phone_otp')) {
        return response()->json([
            'success' => false,
            'message' => 'No OTP session found.'
        ], 422);
    }

        if (now()->gt(session('register_phone_otp_expires_at'))) {
        return response()->json([
            'success' => false,
            'message' => 'OTP expired.'
        ], 422);
    }

        if ($request->otp != session('register_phone_otp')) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP.'
        ], 422);
    }

        session(['phone_verified' => true,  'verified_phone' => session('register_phone')]);

        session()->forget([
        'register_phone_otp',
        'register_phone_otp_expires_at',
        ]);

        return response()->json([
        'success' => true,
        'message' => 'Phone verified successfully.'
        ]);
    }
}