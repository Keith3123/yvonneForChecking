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
                    if (Customer::where('phone', $value)->count() >= 5) {
                        $fail('This phone number has reached the maximum of 5 accounts.');
                    }
                },
            ],
            'email'    => 'nullable|email|unique:customer,email',
            'address'  => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:customer,username',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ── OTP gate ──────────────────────────────────────────────────────────
        if (!session('phone_verified') || session('register_phone') !== $request->phone) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => ['phone' => ['Please verify your phone number first.']]
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['phone' => 'Please verify your phone number first.'])
                ->withInput();
        }
        // ─────────────────────────────────────────────────────────────────────

        $customerDTO = new CustomerDTO($request->all());
        $customer    = $this->customerService->register($customerDTO);

        // Stamp verification timestamp, clear OTP columns
        $customer->update([
            'phone_verified_at'      => now(),
            'phone_otp'              => null,
            'phone_otp_expires_at'   => null,
        ]);

        // Clear OTP session
        session()->forget([
            'register_phone',
            'register_phone_otp',
            'register_phone_otp_expires_at',
            'phone_verified',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully!'
            ]);
        }

        return redirect()->route('login')->with('success', 'Account created successfully!');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function checkUsername(Request $request)
    {
        return response()->json([
            'exists' => Customer::where('username', $request->username)->exists()
        ]);
    }

    public function checkEmail(Request $request)
    {
        return response()->json([
            'exists' => Customer::where('email', $request->email)->exists()
        ]);
    }

    // ── OTP ───────────────────────────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|digits:11']);

        if (Customer::where('phone', $request->phone)->count() >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'This phone number has reached the maximum of 5 accounts.'
            ], 422);
        }

        $otp = rand(100000, 999999);

        // Store in session
        session([
            'register_phone'                => $request->phone,
            'register_phone_otp'            => $otp,
            'register_phone_otp_expires_at' => now()->addMinutes(5),
        ]);

        // ── FREE delivery: just log it ────────────────────────────────────────
        // In production swap this with any free SMS gateway you choose later.
        // For dev/testing the OTP is visible in:
        //   • Laravel log  → storage/logs/laravel.log
        //   • JSON response (dev only — remove before going live)
        \Log::info("📱 OTP for {$request->phone}: {$otp}");

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            // ⚠️  REMOVE the line below before going live — for dev only
            'otp_dev' => app()->isLocal() ? $otp : null,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        if (!session('register_phone_otp')) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP session found. Please request a new OTP.'
            ], 422);
        }

        if (now()->gt(session('register_phone_otp_expires_at'))) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 422);
        }

        if ((string) $request->otp !== (string) session('register_phone_otp')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.'
            ], 422);
        }

        // Mark verified
        session([
            'phone_verified'  => true,
            'verified_phone'  => session('register_phone'),
            'verified_at'     => now(),
        ]);

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