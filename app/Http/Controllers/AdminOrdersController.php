<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
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

        $orders = Order::with(['orderItems.product', 'customer', 'payment', 'payments'])  
            ->orderBy('orderDate', 'desc')
            ->get();

        $customers = Customer::whereHas('orders')->get();

        // ✅ Grab unread IDs BEFORE marking them read (for highlight)
        $newOrderIds = Order::where('is_admin_read', false)
            ->pluck('orderID')
            ->toArray();

        // ✅ Now mark all as read
        Order::where('is_admin_read', false)
            ->update(['is_admin_read' => true]);

        return view('admin.orders', compact('orders', 'customers', 'newOrderIds'));
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => Order::where('is_admin_read', false)->count(),
        ]);
    }

    public function markAllRead()
    {
        Order::where('is_admin_read', false)->update(['is_admin_read' => true]);
        return response()->json(['status' => 'success']);
    }


    public function viewOrder($orderID)
    {
        $order = Order::with(['orderItems.product', 'customer', 'payments'])
            ->where('orderID', $orderID)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found']);
        }

        return response()->json(['status' => 'success', 'order' => $order]);
    }

    public function updateStatus(Request $request, $orderID)
{
    $order = Order::where('orderID', $orderID)->first();

    if (!$order) {
        return response()->json(['status' => 'error', 'message' => 'Order not found']);
    }

    $validStatuses = ['Pending','Confirmed','Preparing','Out for Delivery','Done','Cancelled'];

    if (!in_array($request->status, $validStatuses)) {
        return response()->json(['status' => 'error', 'message' => 'Invalid status']);
    }

    $order->status  = $request->status;
    $order->is_read = false;
    $order->save();

    // ✅ Auto-manage COD payment status based on order status
    $newPayStatus = null;
    $payment = Payment::where('orderID', $orderID)
    ->where('contextType', 'order')
    ->where('method', 'COD')
    ->first();

    if ($payment && $payment->method === 'COD') {
        if ($request->status === 'Done') {
            // Auto-approve when Done
            $payment->status = 'approved';
        } else {
            // Revert to pending for any other status
            $payment->status = 'pending';
        }
        $payment->save();
        $newPayStatus = $payment->status;
    }

    return response()->json([
        'status'          => 'success',
        'message'         => 'Status updated successfully',
        'new_pay_status'  => $newPayStatus, // null if not COD
    ]);
}
    /**
     * Toggle COD payment status: pending -> approved -> pending
     * Creates a payment record if none exists yet (older COD orders).
     */
    public function updatePaymentStatus(Request $request, $orderID)
{
    $order = Order::where('orderID', $orderID)->first();
    if (!$order) {
        return response()->json(['status' => 'error', 'message' => 'Order not found']);
    }

    // ✅ For downpayment orders, toggle the remaining_balance (COD) record
    $payment = Payment::where('orderID', $orderID)
        ->where('contextType', 'order')
        ->where('method', 'COD') // targets remaining_balance or pure COD
        ->first();

    if (!$payment) {
        $payment = Payment::create([
            'orderID'     => $order->orderID,
            'contextType' => 'order',
            'paymentType' => 'fullpayment',
            'amount'      => $order->totalAmount,
            'paymentDate' => now(),
            'method'      => 'COD',
            'status'      => 'pending',
            'meta'        => json_encode([]),
        ]);
    }

    if ($payment->status !== 'approved' && $order->status !== 'Done') {
        return response()->json([
            'status'  => 'error',
            'message' => 'Payment can only be approved after the order is marked as Done'
        ]);
    }

    $newStatus       = $payment->status === 'approved' ? 'pending' : 'approved';
    $payment->status = $newStatus;
    $payment->save();

    return response()->json([
        'status'     => 'success',
        'new_status' => $newStatus,
        'message'    => "Payment marked as {$newStatus}"
    ]);
}
}