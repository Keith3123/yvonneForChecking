<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrdersPageController extends Controller
{
    public function index()
    {
        $userID = Auth::id();

        $orders = Order::where('customerID', $userID)
            ->with(['orderItems.product'])
            ->orderBy('orderDate', 'desc')
            ->get();

        return view('user.OrdersPage', compact('orders'));
    }
}
