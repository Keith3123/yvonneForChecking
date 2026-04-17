<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Rating;
use Barryvdh\DomPDF\Facade\Pdf;

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

         Order::where('customerID', $customerID)
        ->whereIn('status', ['Confirmed', 'Preparing', 'Out for Delivery', 'Done', 'Cancelled'])
        ->where('is_read', false)
        ->update(['is_read' => true]);
        
        $orders = Order::where('customerID', $customerID)
            ->with(['orderItems.product'])
            ->orderByRaw("CASE 
        WHEN status IN ('Confirmed', 'Preparing', 'Out for Delivery', 'Pending') THEN 0 
        WHEN status IN ('Done', 'Cancelled') THEN 1 
        ELSE 2 
    END ASC")
            ->orderBy('updated_at', 'desc')
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

    public function rate(Request $request)
    {
        $request->validate([
            'orderID' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $customer = session('logged_in_user');
        $customerID = $customer['customerID'] ?? null;

        $order = Order::where('orderID', $request->orderID)
            ->where('customerID', $customerID)
            ->first();

        if (!$order) {
 return response()->json(['status' => 'error', 'message' => 'Invalid order.']);        }

        if ($order->status !== 'Done') {
        return response()->json(['status' => 'error', 'message' => 'You can only rate completed orders.']);
        }

        Rating::updateOrCreate(
            ['order_id' => $request->orderID],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

          return response()->json([
        'status' => 'success',
        'message' => 'Thank you for your feedback!'
    ]);
}

    public function exportReceiptPDF($orderID)
    {
        $customer = session('logged_in_user');
        $customerID = $customer['customerID'] ?? null;

        if (!$customerID) {
            return redirect()->back()->with('error', 'Please log in first.');
        }

        $order = Order::with(['orderItems.product', 'payment'])
            ->where('orderID', $orderID)
            ->where('customerID', $customerID)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        $vatRate = 0.12;
        $totalAmount = $order->orderItems->sum('subtotal');
        $vatableSales = round($totalAmount / (1 + $vatRate), 2);
        $vatAmount = round($totalAmount - $vatableSales, 2);

        $pdf = Pdf::loadView('user.ReceiptPDF', compact('order', 'vatableSales', 'vatAmount', 'totalAmount'));

        return $pdf->download("Receipt-Order-{$order->orderID}.pdf");
    }

}

