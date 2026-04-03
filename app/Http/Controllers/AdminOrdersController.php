<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;

class AdminOrdersController extends AdminBaseController
{
    public function index()
    {
        parent::__construct();
        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 3)) {
            abort(403, 'Unauthorized');
        }

        $orders = Order::with(['orderItems.product', 'customer', 'payment'])
            ->orderBy('orderDate', 'desc')
            ->get();

        // ✅ FIXED: use Customer model, not User
        $customers = Customer::whereHas('orders')->get();

        return view('admin.orders', compact('orders', 'customers'));
    }

    public function viewOrder($orderID)
    {
        $order = Order::with(['orderItems.product', 'customer', 'payment'])
            ->where('orderID', $orderID)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'order' => $order
        ]);
    }

    public function updateStatus(Request $request, $orderID)
    {
        $order = Order::where('orderID', $orderID)->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ]);
        }

        $validStatuses = [
            'Pending',
            'Confirmed',
            'Preparing',
            'Out for Delivery',
            'Done',
            'Cancelled'
        ];

        if (!in_array($request->status, $validStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid status'
            ]);
        }

        $order->status = $request->status;
        $order->is_read = false; 
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully'
        ]);
    }
}