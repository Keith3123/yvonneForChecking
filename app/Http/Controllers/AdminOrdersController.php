<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class AdminOrdersController extends Controller
{
    public function index()
    {
        $orders = Order::with('orderItems.product', 'customer')->orderBy('orderDate', 'asc')->get();
        return view('admin.orders', compact('orders'));
    }
}
