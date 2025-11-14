<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartPageController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        return view('user.CartPage', compact('cart'));
    }

    public function add(Request $request)
    {
        $cart = session('cart', []);

        $product = [
            'id' => $request->id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity ?? 1,
            'image' => $request->image,
        ];

        if (isset($cart[$product['id']])) {
            $cart[$product['id']]['quantity'] += $product['quantity'];
        } else {
            $cart[$product['id']] = $product;
        }

        session(['cart' => $cart]);

        // Total quantity instead of count
        $cartCount = array_sum(array_map(fn($item) => $item['quantity'], $cart));

        return response()->json(['success' => true, 'cartCount' => $cartCount]);
    }

    public function remove(Request $request)
    {
        $cart = session('cart', []);
        if (isset($cart[$request->id])) {
            unset($cart[$request->id]);
        }
        session(['cart' => $cart]);
        return redirect()->back();
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->back()->with('success', 'Cart cleared successfully!');
    }

}
