<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        // Validate profile fields
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        // Get the logged-in user from the session
        $sessionUser = session('logged_in_user');
        $customer = Customer::find($sessionUser['customerID']);

        if (!$customer) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Update user information
        $customer->firstName = $request->firstName;
        $customer->lastName = $request->lastName;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->address = $request->address;

        $customer->save();

        // Update session data so it reflects changes immediately
        session(['logged_in_user' => $customer->toArray()]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }


    // Update the user's password
    public function updatePassword(Request $request)
    {
        // Validate the password fields
        $request->validate([
            'current_password' => 'required', // Current password is required
            'new_password' => 'required|min:6|confirmed', // New password must be confirmed
        ], [
            'new_password.confirmed' => 'The new password and confirmation password do not match.', // Custom error message
        ]);

        // Get the logged-in user from the session
        $sessionUser = session('logged_in_user');
        $customer = Customer::find($sessionUser['customerID']);

        // Check if the current password matches the one in the database
        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Hash the new password and update it in the database
        $customer->password = Hash::make($request->new_password);
        $customer->save();

        // Log out the user to force them to log in again with the new password
        Auth::logout();

        // Redirect to the login page with a success message
        return redirect()->route('login')->with('status', 'Password updated successfully! Please log in again.');
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

}
