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

        $orders = Order::with(['orderItems.product', 'customer', 'payment'])
            ->orderBy('orderDate', 'desc')
            ->get();

        $customers = Customer::whereHas('orders')->get();

        return view('admin.orders', compact('orders', 'customers'));
    }

    public function viewOrder($orderID)
    {
        $order = Order::with(['orderItems.product', 'customer', 'payment'])
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

        // Query directly — avoid withDefault() which returns a dummy model
        $payment = Payment::where('orderID', $orderID)
            ->where('contextType', 'order')
            ->first();

        // No payment record yet — create one (handles old COD orders that skipped payment insert)
        if (!$payment) {
            $payment = Payment::create([
                'orderID'          => $order->orderID,
                'paluwaganEntryID' => null,
                'contextType'      => 'order',
                'paymentType'      => 'fullpayment',
                'amount'           => $order->totalAmount,
                'paymentDate'      => now(),
                'method'           => 'COD',
                'status'           => 'pending',
                'meta'             => json_encode([]),
            ]);
        }

        if ($payment->method !== 'COD') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Only COD payments can be manually updated'
            ]);
        }

        if ($payment->status !== 'approved' && $order->status !== 'Done') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Payment can only be approved after the order is marked as Done'
            ]);
        }

        // Toggle
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