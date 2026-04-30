<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartPageController extends Controller
{
    // Show cart page
    public function index()
    {
        $cart = session('cart', []);
        return view('user.CartPage', compact('cart'));
    }

    // Add product to cart
    public function add(Request $request)
{
    $cart = session('cart', []);

    $id = $request->input('id');
    $name = $request->input('name');
    $price = $request->input('price'); // ← This is already the DISCOUNTED price
    $quantity = $request->input('quantity', 1);
    $image = $request->input('image');
    $productType = $request->input('productType');

    $key = $id . '_' . $price; // unique key per product+price combo

    if (isset($cart[$key])) {
        $cart[$key]['quantity'] += $quantity;
    } else {
        $cart[$key] = [
            'id' => $id,
            'productID' => $id,
            'name' => $name,
            'price' => (float) $price, // Already discounted
            'quantity' => (int) $quantity,
            'image' => $image,
            'productType' => $productType,
        ];
    }

    session(['cart' => $cart]);

    return response()->json(['success' => true, 'cart' => $cart]);
}

    // Update quantity (+ / -)
    public function update(Request $request)
{
    $cart = session('cart', []);
    $id = $request->id;

    if (!isset($cart[$id])) {
        return view('partials.cart-sidebar')->render();
    }

    if ($request->action === 'increase') {
        $cart[$id]['quantity']++;
    } elseif ($request->action === 'decrease' && $cart[$id]['quantity'] > 1) {
        $cart[$id]['quantity']--;
    }

    session(['cart' => $cart]);

    return view('partials.cart-sidebar')->render();
}


    // Remove product
   public function remove(Request $request)
{
    $cart = session('cart', []);
    unset($cart[$request->id]);
    session(['cart' => $cart]);

    return view('partials.cart-sidebar')->render();
}


    // Clear cart
   public function clear(Request $request)
{
    session()->forget('cart');

    // ALWAYS return sidebar HTML
    return view('partials.cart-sidebar')->render();
}


}
