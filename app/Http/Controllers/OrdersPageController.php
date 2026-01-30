<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrdersPageController extends Controller
{
    // Show the orders list
    public function index()
    {
        $customer = session('logged_in_user');
        $customerID = $customer['customerID'] ?? null;

        if (!$customerID) {
            return redirect()->route('catalog')->with('error', 'Please log in to view your orders.');
        }

        $orders = Order::where('customerID', $customerID)
            ->with(['orderItems.product'])
            ->orderBy('orderDate', 'desc')
            ->get();

        $vatRate = 0.12;

        $orders->each(function ($order) use ($vatRate) {
            $order->computedSubtotal = $order->totalAmount;
            $order->computedVat = round($order->totalAmount * $vatRate, 2);
            $order->computedTotal = round(
                $order->totalAmount + $order->computedVat,
                2
            );
        });

        return view('user.OrdersPage', compact('orders'));
    }

    // Cancel Order logic
    public function cancelOrder(Request $request, $orderID)
    {
        $customer = session('logged_in_user');
        $customerID = $customer['customerID'] ?? null;

        if (!$customerID) {
            return response()->json(['status' => 'error', 'message' => 'Please log in first.']);
        }

        $order = Order::where('orderID', $orderID)
            ->where('customerID', $customerID)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.']);
        }

        if ($order->status === 'Cancelled') {
            return response()->json(['status' => 'error', 'message' => 'Order already cancelled.']);
        }

        if ($order->status === 'Done') {
            return response()->json(['status' => 'error', 'message' => 'Delivered orders cannot be cancelled.']);
        }

        $order->status = 'Cancelled';
        $order->save();

        return response()->json(['status' => 'success', 'message' => 'Order has been cancelled.']);
    }

    // For viewing the receipt (just returns data)
    public function viewReceipt($orderID)
    {
        $customer = session('logged_in_user');
        $customerID = $customer['customerID'] ?? null;

        if (!$customerID) {
            return response()->json(['status' => 'error', 'message' => 'Please log in first.']);
        }

        $order = Order::with(['orderItems.product'])
            ->where('orderID', $orderID)
            ->where('customerID', $customerID)
            ->first();

        if ($order) {
            return response()->json(['status' => 'success', 'order' => $order]);
        }

        return response()->json(['status' => 'error', 'message' => 'Order not found.']);
    }
}
