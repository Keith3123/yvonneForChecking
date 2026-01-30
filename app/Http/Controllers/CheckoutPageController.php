<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\DTO\CreateOrderDTO;
use Carbon\Carbon;
use App\Models\Customer;

class CheckoutPageController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $cart = session('cart', []);
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $vatRate = 0.12; // 12% VAT
        $vatAmount = round($subtotal * $vatRate, 2);
        $total = $subtotal + $vatAmount;

        $sessionUser = session('logged_in_user');
        $customer = null;

        if ($sessionUser) {
            $customer = Customer::find($sessionUser['customerID']);
        }

        return view('user.CheckoutPage', compact('cart', 'subtotal', 'vatAmount', 'total', 'customer'));
    }

    public function placeOrder(Request $request)
    {
        $isAjax = $request->expectsJson() || $request->wantsJson();

        $customer = session('logged_in_user');
        $customerID = $customer['customerID'] ?? null;

        if (!$customerID) {
            return $isAjax
                ? response()->json(['error' => 'You must be logged in as a customer to place an order.'])
                : back()->with('error', 'You must be logged in as a customer to place an order.');
        }

        $cart = session('cart', []);
        if (empty($cart)) {
            return $isAjax
                ? response()->json(['error' => 'Your cart is empty.'])
                : redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        try {
            // Validation rules
            $rules = [
                'deliveryAddress' => 'required|string|max:255',
                'remarks' => 'nullable|string|max:200',
                'deliveryDate' => 'required|date',
                'deliveryTime' => 'required|string',
                'payment' => 'required|string|in:gcash,cod',
            ];

            if ($request->payment === 'gcash') {
                $rules['paymentProof'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:10240';
            } else {
                $rules['paymentProof'] = 'nullable';
            }

            $validated = $request->validate($rules);

            // ✅ UPDATE CUSTOMER ADDRESS
            Customer::where('customerID', $customerID)->update([
                'address' => $validated['deliveryAddress'],
            ]);

            $paymentProof = $request->hasFile('paymentProof')
                ? $request->file('paymentProof')->store('payment_proofs', 'public')
                : null;

            $items = array_map(fn($item) => [
                'productID' => $item['productID'] ?? $item['id'] ?? null,
                'qty'       => $item['quantity'],
                'price'     => $item['price'],
            ], $cart);

            $timeParts = explode('-', $validated['deliveryTime']);
            $startTime = trim($timeParts[0]);
            $deliveryDateTime = Carbon::parse($validated['deliveryDate'] . ' ' . $startTime);

            $orderDTO = new CreateOrderDTO([
                'customerID'      => $customerID,
                'deliveryAddress' => $validated['deliveryAddress'],
                'remarks'         => $validated['remarks'] ?? '',
                'items'           => $items,
                'deliveryDate'    => $deliveryDateTime->format('Y-m-d H:i:s'),
                'deliveryTime'    => $validated['deliveryTime'],
                'payment'         => $validated['payment'],
                'paymentProof'    => $paymentProof,
            ]);

            $this->orderService->createOrder($orderDTO);

            session()->forget('cart');

            return $isAjax
                ? response()->json(['success' => true, 'message' => 'Order placed successfully!', 'redirect' => route('orders.index')])
                : redirect()->route('catalog')->with('success', 'Your order has been placed successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isAjax) {
                return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()]);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json(['error' => 'Failed to place order. Please try again.', 'exception' => $e->getMessage()]);
            }
            return back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    // ✅ NEW: Save address from checkout map modal
    public function saveAddressFromCheckout(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $sessionUser = session('logged_in_user');
        $customerID = $sessionUser['customerID'] ?? null;

        if (!$customerID) {
            return response()->json(['status' => 'error', 'message' => 'User not logged in.'], 401);
        }

        $customer = Customer::find($customerID);
        $customer->address = $request->address;

        // Update lat/lng if exists
        if (isset($request->latitude)) $customer->latitude = $request->latitude;
        if (isset($request->longitude)) $customer->longitude = $request->longitude;

        $customer->save();

        // Update session
        session(['logged_in_user' => $customer->toArray()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Address saved to your profile.'
        ]);
    }
}
