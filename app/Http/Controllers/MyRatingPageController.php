<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Order;
use Illuminate\Http\Request;

class MyRatingPageController extends Controller
{
    /**
     * Display a listing of the logged-in user's ratings.
     */
    public function index()
    {
        // 1. Retrieve the logged-in customer info from your custom session
        $customer = session('logged_in_user');
        $customerID = $customer['customerID'] ?? null;

        // 2. Safety check: Redirect to login if the session expired or user isn't logged in
        if (!$customerID) {
            return redirect()->route('login')->with('error', 'Please log in to view your ratings.');
        }

        // 3. Fetch the ratings
        // We use whereHas to ensure the rating's order belongs to this customer
        $ratings = Rating::whereHas('order', function ($query) use ($customerID) {
            $query->where('customerID', $customerID);
        })
        ->with('order') // Eager load the order info to prevent "N+1" performance issues
        ->latest()      // Show the newest ratings first
        ->get();

        // 4. Return the view and pass the data
        return view('user.MyRatings', [
            'ratings' => $ratings,
            'customerName' => $customer['name'] ?? 'User'
        ]);
    }
}