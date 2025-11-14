<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutPageController extends Controller
{
    public function index()
    {
        // Get cart from session (if empty, make it an empty array)
        $cart = session('cart', []);

        // Calculate subtotal
        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Downpayment = 50% of subtotal
        $downpayment = $subtotal * 0.5;

        // Balance = remaining 50%
        $balance = $subtotal - $downpayment;

        // Pass all data to the checkout view
        return view('user.CheckoutPage', compact('cart', 'subtotal', 'downpayment', 'balance'));
    }

    public function placeOrder(Request $request)
    {
        // For now, just clear the cart and redirect (frontend-only behavior)
        session()->forget('cart');

        return redirect()->route('catalog')->with('success', 'Your order has been placed successfully!');
    }
}
