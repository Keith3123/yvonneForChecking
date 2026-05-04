<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Mail\EmailOtpMail;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;


class ProfilePageController extends Controller
{
    // Display the user's profile page
    public function index(Request $request)
    {
        // Check if the user is logged in via session
        $sessionUser = $request->session()->get('logged_in_user');

        if (!$sessionUser) {
            return redirect()->route('login')->with('error', 'You must be logged in to access your profile.');
        }

        // Retrieve the full customer data from the database
        $user = Customer::find($sessionUser['customerID']);

        // If customer doesn't exist, redirect to login page
        if (!$user) {
            return redirect()->route('login')->with('error', 'Your account could not be found.');
        }

        // Return the profile page view with user data
        return view('user.ProfilePage', compact('user'));
    }

    // Update the user's profile information
    public function update(Request $request)
{
    // Get the logged-in user from the session
    $sessionUser = session('logged_in_user');
    $customer = Customer::find($sessionUser['customerID']);

    if (!$customer) {
        return redirect()->back()->with('error', 'User not found.');
    }

    // Validate profile fields, allow current username
    $request->validate([
        'username'  => 'required|string|max:255|unique:customer,username,' . $customer->customerID . ',customerID',
        'firstName' => 'required|string|max:255',
        'lastName'  => 'required|string|max:255',
        'email'     => 'required|email',
        'phone'     => 'required|string|max:20',
        'address'   => 'required|string|max:255',
    ]);

    // Update user info including username
    $customer->username  = $request->username;
    $customer->firstName = $request->firstName;
    $customer->lastName  = $request->lastName;
    $customer->email     = $request->email;
    $customer->phone     = $request->phone;
    $customer->address   = $request->address;

    $customer->save();

    // Update session immediately
    session(['logged_in_user' => $customer->toArray()]);

    return redirect()->back()->with('success', 'Profile updated successfully!');
}


    // Update the user's password
    public function updatePassword(Request $request)
    {
         $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:6|confirmed',
    ]);

    $sessionUser = session('logged_in_user');
    $customer = Customer::find($sessionUser['customerID']);

    if (!Hash::check($request->current_password, $customer->password)) {
        return back()->withErrors(['current_password' => 'The current password is incorrect.']);
    }

    $customer->password = Hash::make($request->new_password);
    $customer->password_changed_at = now(); 
    $customer->save();

    // Re-sync session and redirect back with a success message
    session(['logged_in_user' => $customer->toArray()]);

    return redirect()->back()->with('success', 'Your password has been changed successfully!');
}

    public function saveAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $sessionUser = session('logged_in_user');
        $customer = Customer::find($sessionUser['customerID']);

        $customer->address = $request->address;

        // optional if you have lat/lng columns
        if (Schema::hasColumn('customers', 'latitude')) {
            $customer->latitude = $request->latitude;
        }
        if (Schema::hasColumn('customers', 'longitude')) {
            $customer->longitude = $request->longitude;
        }

        $customer->save();

        // UPDATE SESSION DATA
        session(['logged_in_user' => $customer->toArray()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Address saved to your profile.'
        ]);
    }

    // ─────────────────────────────────────────
    // EMAIL OTP — SEND
    // ─────────────────────────────────────────
    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
 
        $newEmail    = $request->email;
        $sessionUser = session('logged_in_user');
        $customer    = Customer::find($sessionUser['customerID']);
 
        // Check if email already taken by another customer
        $exists = Customer::where('email', $newEmail)
            ->where('customerID', '!=', $customer->customerID)
            ->exists();
 
        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This email is already used by another account.',
            ], 422);
        }
 
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
 
        // Save OTP temporarily on customer record
        $customer->email_otp            = $otp;
        $customer->email_otp_expires_at = now()->addMinutes(10);
        $customer->save();
 
        // Send the email
        Mail::to($newEmail)->send(new EmailOtpMail($otp, $newEmail));
 
        // Store pending email in session for verification step
        session(['pending_email' => $newEmail]);
 
        return response()->json([
            'status'  => 'success',
            'message' => 'OTP sent to ' . $newEmail,
        ]);
    }
 
    // ─────────────────────────────────────────
    // EMAIL OTP — VERIFY & UPDATE
    // ─────────────────────────────────────────
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);
 
        $sessionUser = session('logged_in_user');
        $customer    = Customer::find($sessionUser['customerID']);
        $pendingEmail = session('pending_email');
 
        if (!$pendingEmail) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Session expired. Please request a new OTP.',
            ], 422);
        }
 
        // Check OTP match and expiry
        if ($customer->email_otp !== $request->otp) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }
 
        if (now()->isAfter($customer->email_otp_expires_at)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'OTP has expired. Please request a new one.',
            ], 422);
        }
 
        // Update email and mark as verified
        $customer->email               = $pendingEmail;
        $customer->email_verified_at   = now();
        $customer->email_otp           = null;
        $customer->email_otp_expires_at = null;
        $customer->save();
 
        // Sync session
        session(['logged_in_user' => $customer->toArray()]);
        session()->forget('pending_email');
 
        return response()->json([
            'status'  => 'success',
            'message' => 'Email verified and updated successfully!',
            'email'   => $customer->email,
        ]);
    }
 
    // ─────────────────────────────────────────
    // GOOGLE OAUTH — REDIRECT
    // ─────────────────────────────────────────
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
 
    // ─────────────────────────────────────────
    // GOOGLE OAUTH — CALLBACK (BIND)
    // ─────────────────────────────────────────
    public function handleGoogleCallback()
    {
        try {
            $googleUser  = Socialite::driver('google')->user();
            $sessionUser = session('logged_in_user');
            $customer    = Customer::find($sessionUser['customerID']);
 
            if (!$customer) {
                return redirect()->route('profile')->with('error', 'Account not found.');
            }
 
            // Prevent binding a Google account already used by someone else
            $alreadyBound = Customer::where('google_id', $googleUser->getId())
                ->where('customerID', '!=', $customer->customerID)
                ->exists();
 
            if ($alreadyBound) {
                return redirect()->route('profile')
                    ->with('error', 'This Google account is already linked to another user.');
            }
 
            // Bind Google account
            $customer->google_id     = $googleUser->getId();
            $customer->google_avatar = $googleUser->getAvatar();
 
            // Also auto-verify email if it matches Google email
            if ($customer->email === $googleUser->getEmail() && !$customer->email_verified_at) {
                $customer->email_verified_at = now();
            }
 
            $customer->save();
            session(['logged_in_user' => $customer->toArray()]);
 
            return redirect()->route('profile')->with('success', 'Google account linked successfully!');
 
        } catch (\Exception $e) {
            return redirect()->route('profile')->with('error', 'Failed to link Google account. Please try again.');
        }
    }
 
    // ─────────────────────────────────────────
    // GOOGLE OAUTH — UNLINK
    // ─────────────────────────────────────────
    public function unlinkGoogle()
    {
        $sessionUser = session('logged_in_user');
        $customer    = Customer::find($sessionUser['customerID']);
 
        $customer->google_id     = null;
        $customer->google_avatar = null;
        $customer->save();
 
        session(['logged_in_user' => $customer->toArray()]);
 
        return redirect()->route('profile')->with('success', 'Google account unlinked.');
    }
}
