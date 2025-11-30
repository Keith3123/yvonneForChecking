<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrdersPageController extends Controller
{
    public function index()
    {
       $customerID = session('logged_in_user.customerID');

        if (!$customerID) {
        return redirect()->route('catalog')->with('error', 'Please log in to view your orders.');
    }
        $orders = Order::where('customerID', $customerID)
            ->with(['orderItems.product'])
            ->orderBy('orderDate', 'desc')
            ->get();

        return view('user.OrdersPage', compact('orders'));
    }
}
