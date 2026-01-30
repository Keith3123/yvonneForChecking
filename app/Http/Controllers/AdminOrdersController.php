<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class AdminOrdersController extends Controller
{
    public function index()
    {
        $orders = Order::with('orderItems.product', 'customer')
            ->orderBy('orderDate', 'desc')
            ->get();

        return view('admin.orders', compact('orders'));
    }

    // VIEW ORDER DETAILS (AJAX)
    public function viewOrder($orderID)
    {
        $order = Order::with('orderItems.product', 'customer')->find($orderID);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found']);
        }

        return response()->json([
            'status' => 'success',
            'order' => $order
        ]);
    }

    // ACCEPT ORDER
    public function acceptOrder($orderID)
    {
        $order = Order::find($orderID);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found']);
        }

        $order->status = 'In Progress';
        $order->save();

        return response()->json(['status' => 'success', 'message' => 'Order accepted']);
    }

    // CANCEL ORDER
    public function cancel($id)
{
    $order = Order::find($id);
    if (!$order) {
        return response()->json(['status' => 'error', 'message' => 'Order not found']);
    }

    $order->status = 'Declined'; // Set status as declined
    $order->save();

    return response()->json(['status' => 'success', 'message' => 'Order declined']);
}

}
