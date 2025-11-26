<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order; 
use App\Models\OrderItem; 

class CheckoutPageController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $downpayment = $subtotal * 0.5;
        $balance = $subtotal - $downpayment;

        return view('user.CheckoutPage', compact('cart', 'subtotal', 'downpayment', 'balance'));
    }

    public function placeOrder(Request $request)
    {
       $user = session('logged_in_user');

$userID = $user['customerID'] ?? null;

if (!$userID) {
    return back()->with('error', 'You must be logged in to place an order.');
}


        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        // Validate delivery info
        $validated = $request->validate([
            'deliveryAddress' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:200',
            'deliveryDate' => 'nullable|date',
            'payment' => 'required|string|in:gcash,cod',
            'paymentProof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        DB::transaction(function () use ($userID, $cart, $validated) {
            $totalAmount = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

            $order = Order::create([
                'customerID' => $userID,
                'status' => 'PENDING',
                'orderDate' => now(),
                'totalAmount' => $totalAmount,
                'remarks' => $validated['remarks'] ?? '',
                'deliveryAddress' => $validated['deliveryAddress'],
                'paymentStatus' => 'PENDING',
                'deliveryDate' => $validated['deliveryDate'] ?? now(),
            ]);

            foreach ($cart as $item) {
    // Skip items without a valid product ID (customization items)
    $productID = isset($item['id']) && $item['id'] > 0 ? $item['id'] : null;

    OrderItem::create([
        'orderID' => $order->orderID,
        'productID' => $productID,
        'price' => $item['price'],
        'qty' => $item['quantity'],
        'subtotal' => $item['price'] * $item['quantity'],
    ]);
}

        });

        session()->forget('cart');

        return redirect()->route('catalog')->with('success', 'Your order has been placed successfully!');
    }
}
