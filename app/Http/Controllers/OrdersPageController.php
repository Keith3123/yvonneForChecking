<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrdersPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
