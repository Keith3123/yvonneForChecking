<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Mail\EmailOtpMail;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class ProfilePageController extends Controller
{
    // ─────────────────────────────────────────
    // DISPLAY PROFILE PAGE
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $sessionUser = $request->session()->get('logged_in_user');

        if (!$sessionUser) {
            return redirect()->route('login')->with('error', 'You must be logged in to access your profile.');
        }

        $user = Customer::find($sessionUser['customerID']);

        if (!$user) {
            return redirect()->route('login')->with('error', 'Your account could not be found.');
        }

        return view('user.ProfilePage', compact('user'));
    }

    // ─────────────────────────────────────────
    // UPDATE PROFILE INFO
    // ─────────────────────────────────────────
    public function update(Request $request)
    {
        $sessionUser = session('logged_in_user');
        $customer    = Customer::find($sessionUser['customerID']);

        if (!$customer) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $request->validate([
            'username'  => 'required|string|max:255|unique:customer,username,' . $customer->customerID . ',customerID',
            'firstName' => 'required|string|max:255',
            'lastName'  => 'required|string|max:255',
            'email'     => 'required|email',
            'phone'     => 'required|string|max:20',
            'address'   => 'required|string|max:255',
        ]);

        $customer->username  = $request->username;
        $customer->firstName = $request->firstName;
        $customer->lastName  = $request->lastName;
        $customer->email     = $request->email;
        $customer->phone     = $request->phone;
        $customer->address   = $request->address;
        $customer->save();

        session(['logged_in_user' => $customer->fresh()->toArray()]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    // ─────────────────────────────────────────
    // UPDATE PASSWORD
    // ─────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $sessionUser = session('logged_in_user');
        $customer    = Customer::find($sessionUser['customerID']);

        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $customer->password            = Hash::make($request->new_password);
        $customer->password_changed_at = now();
        $customer->save();

        session(['logged_in_user' => $customer->fresh()->toArray()]);

        return redirect()->back()->with('success', 'Your password has been changed successfully!');
    }

    // ─────────────────────────────────────────
    // SAVE ADDRESS
    // ─────────────────────────────────────────
    public function saveAddress(Request $request)
    {
        $request->validate([
            'address'   => 'required|string|max:255',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $sessionUser = session('logged_in_user');
        $customer    = Customer::find($sessionUser['customerID']);

        $customer->address = $request->address;

        if (Schema::hasColumn('customers', 'latitude')) {
            $customer->latitude = $request->latitude;
        }
        if (Schema::hasColumn('customers', 'longitude')) {
            $customer->longitude = $request->longitude;
        }

        $customer->save();
        session(['logged_in_user' => $customer->fresh()->toArray()]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Address saved to your profile.',
        ]);
    }

    // ─────────────────────────────────────────
    // GOOGLE OAUTH — REDIRECT (bind flow)
    // Persists customerID before the OAuth redirect
    // so the callback can always find the user.
    // ─────────────────────────────────────────
    public function redirectToGoogle()
    {
        $sessionUser = session('logged_in_user');

        if (!$sessionUser) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        session(['google_bind_customer_id' => $sessionUser['customerID']]);
        session()->save();

        return Socialite::driver('google')->redirect();
    }

    // ─────────────────────────────────────────
    // GOOGLE OAUTH — CALLBACK (bind)
    // Only stores google_id (no google_avatar).
    // Auto-verifies email if it matches Google's.
    // ─────────────────────────────────────────
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $customerId = session('google_bind_customer_id')
                ?? (session('logged_in_user')['customerID'] ?? null);

            if (!$customerId) {
                \Log::error('Google bind callback: no customer ID in session');
                return redirect()->route('profile')
                    ->with('error', 'Session expired. Please try linking your Google account again.');
            }

            $customer = Customer::find($customerId);

            if (!$customer) {
                return redirect()->route('profile')->with('error', 'Account not found.');
            }

            // Prevent binding a Google account already linked to another user
            $alreadyBound = Customer::where('google_id', $googleUser->getId())
                ->where('customerID', '!=', $customer->customerID)
                ->exists();

            if ($alreadyBound) {
                return redirect()->route('profile')
                    ->with('error', 'This Google account is already linked to another user.');
            }

            // Bind google_id and ALWAYS sync email + mark verified
            // regardless of whether it matches the old email or not
            $customer->google_id         = $googleUser->getId();
            $customer->email             = $googleUser->getEmail();
            $customer->email_verified_at = now();
            $customer->save();

            // Refresh session, clean up bind key
            session(['logged_in_user' => $customer->fresh()->toArray()]);
            session()->forget('google_bind_customer_id');
            session()->save();

            return redirect()->route('profile')->with('success', 'Google account linked successfully!');

        } catch (\Exception $e) {
            \Log::error('Google bind error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('profile')
                ->with('error', 'Failed to link Google account: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // GOOGLE OAUTH — UNLINK
    // ─────────────────────────────────────────
    public function unlinkGoogle()
    {
        $sessionUser = session('logged_in_user');
        $customer    = Customer::find($sessionUser['customerID']);

        $customer->google_id         = null;
        $customer->email_verified_at = null;
        $customer->save();

        session(['logged_in_user' => $customer->fresh()->toArray()]);

        return redirect()->route('profile')->with('success', 'Google account unlinked.');
    }
}