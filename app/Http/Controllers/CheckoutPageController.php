<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\DTO\CreateOrderDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\DeliveryAddress;

class CheckoutPageController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        // ✅ FIXED (critical)
        $this->orderService = $orderService;
    }

    // ==============================
    // CHECKOUT PAGE
    // ==============================
    public function index()
    {
        $cart = session('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        $vatRate = 0.12;
        $subtotal = round($total / (1 + $vatRate), 2);
        $vatAmount = round($total - $subtotal, 2);

        $customer = null;
        if ($sessionUser = session('logged_in_user')) {
            $customer = Customer::find($sessionUser['customerID']);
        }

        return view('user.CheckoutPage', compact('cart', 'subtotal', 'vatAmount', 'total', 'customer'));
    }

    // ==============================
    // COD ORDER
    // ==============================
    public function placeOrder(Request $request)
    {
        $customer = session('logged_in_user');
        if (!$customer) return response()->json(['error' => 'Login required'], 401);

        $cart = session('cart', []);
        if (empty($cart)) return response()->json(['error' => 'Cart empty'], 400);

        $validated = $request->validate([
            'deliveryAddress' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:200',
            'deliveryDate' => 'required|date',
            'deliveryTime' => 'required|string',
            'payment' => 'required|string|in:cod,gcash', // ✅ add this
        ]);

        $items = array_map(fn($item) => [
            'productID' => $item['productID'] ?? $item['id'],
            'qty' => $item['quantity'],
            'price' => $item['price'],
        ], $cart);

        $dto = new CreateOrderDTO([
            'customerID' => $customer['customerID'],
            'deliveryAddress' => $validated['deliveryAddress'],
            'remarks' => $validated['remarks'] ?? '',
            'items' => $items,
            'deliveryDate' => Carbon::parse($validated['deliveryDate'].' '.$validated['deliveryTime']),
            'deliveryTime' => $validated['deliveryTime'],
            'payment' => $validated['payment'], // ✅ store selected payment
        ]);

        $this->orderService->createOrder($dto);

        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully'
        ]);
    }

    // ==============================
    // GCASH PAYMENT
    // ==============================
    // ==============================
public function payWithGcash(Request $request)
{
    try {
        $customer = session('logged_in_user');
        if (!$customer) return response()->json(['error' => 'Login required'], 401);

        $cart = session('cart', []);
        if (empty($cart)) return response()->json(['error' => 'Cart empty'], 400);

        $validated = $request->validate([
            'deliveryAddress' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:200',
            'deliveryDate' => 'required|date',
            'deliveryTime' => 'required|string',
        ]);

        // =========================
        // PREPARE ITEMS
        // =========================
        $items = array_map(fn($item) => [
            'productID' => $item['productID'] ?? $item['id'],
            'qty' => $item['quantity'],
            'price' => $item['price'],
        ], $cart);

        $dto = new CreateOrderDTO([
            'customerID' => $customer['customerID'],
            'deliveryAddress' => $validated['deliveryAddress'],
            'remarks' => $validated['remarks'] ?? '',
            'items' => $items,
            'deliveryDate' => Carbon::parse($validated['deliveryDate'].' '.$validated['deliveryTime']),
            'deliveryTime' => $validated['deliveryTime'],
            'payment' => 'gcash',
        ]);

        // ✅ CREATE ORDER
        $order = $this->orderService->createOrder($dto);

        // =========================
        // TOTAL
        // =========================
        $total = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);

        if ($total <= 0) {
            return response()->json(['error' => 'Invalid total'], 400);
        }

        // =========================
        // CREATE CHECKOUT SESSION
        // =========================
        $response = Http::withBasicAuth(config('services.paymongo.secret'), '')
            ->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [[
                            'name' => 'Order #' . $order->orderID,
                            'amount' => intval($total * 100),
                            'currency' => 'PHP',
                            'quantity' => 1,
                        ]],
                        'payment_method_types' => ['gcash'],
                        'success_url' => route('checkout.payment.success'),
                        'cancel_url' => route('checkout.payment.failed'),
                        'metadata' => [
                            'order_id' => (string)$order->orderID
                        ]
                    ]
                ]
            ]);

        if (!$response->successful()) {
            Log::error('PayMongo error', $response->json());
            return response()->json(['error' => 'PayMongo failed'], 500);
        }

$data = $response->json()['data'];

$checkoutId = $data['id'];
$checkoutUrl = $data['attributes']['checkout_url'] ?? null;

// ✅ SAVE IMMEDIATELY
Payment::where('orderID', $order->orderID)
->update([
    'checkout_session_id' => $checkoutId,
    'checkout_url'        => $checkoutUrl,
    'status'              => 'pending',
    'meta'                => json_encode([
        'stage' => 'checkout_created'
    ])
]);

        return response()->json([
            'checkout_url' => $data['attributes']['checkout_url']
        ]);

    } catch (\Throwable $e) {
        Log::error('💥 GCASH ERROR', [
            'message' => $e->getMessage(),
        ]);

        return response()->json(['error' => 'Server error'], 500);
    }
}

    // ==============================
    // PAYMENT RESULT PAGES
    // ==============================
    
    public function paymentSuccess()
{
    session()->forget('cart');
    return view('payment.success');
}


    public function paymentFailed()
    {
        return view('payment.failed');
    }


    // ==============================
    // ADDRESS BOOK
    // ==============================

    public function getSavedAddresses()
    {
        $user = session('logged_in_user');

        if (!$user) {
            return response()->json([]);
        }

        return DeliveryAddress::where('customerID', $user['customerID'])->get();
    }

    public function saveAddress(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:50',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = session('logged_in_user');

        DeliveryAddress::create([
            'customerID' => $user['customerID'],
            'label' => $request->label,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address saved!'
        ]);
    }
    
}