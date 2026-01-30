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
        $id = $request->id;

        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid product ID.'], 400);
        }

        $product = [
            'id' => (int)$id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity ?? 1,
            'image' => $request->image,
            'customization' => $request->customization ?? null,
            'productType' => $request->productType ?? '',
        ];

        // Unique key if customization exists
        $key = $product['id'];
        if (!empty($product['customization'])) {
            $key = $product['id'] . '-' . md5(json_encode($product['customization']));
        }

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $product['quantity'];
        } else {
            $cart[$key] = $product;
        }

        session(['cart' => $cart]);

        if ($request->ajax()) {
            return view('partials.cart-sidebar')->render();
        }

        return response()->json([
            'success' => true,
            'cartCount' => array_sum(array_column($cart, 'quantity'))
        ]);
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
