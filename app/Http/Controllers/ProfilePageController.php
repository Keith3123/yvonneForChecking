<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


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

}
