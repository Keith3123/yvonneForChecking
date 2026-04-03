<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\DTO\CreateOrderDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

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

        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        // ✅ Store payload in session
        session([
            'paymongo_payload' => [
                'customerID' => $customer['customerID'],
                'deliveryAddress' => $validated['deliveryAddress'],
                'remarks' => $validated['remarks'] ?? '',
                'deliveryDate' => $validated['deliveryDate'],
                'deliveryTime' => $validated['deliveryTime'],
                'cart' => $cart,
            ]
        ]);

        // ✅ Correct redirect object keys for PayMongo
        $response = Http::withBasicAuth(config('services.paymongo.secret'), '')
            ->post('https://api.paymongo.com/v1/sources', [
                'data' => [
                    'attributes' => [
                        'type' => 'gcash',
                        'amount' => intval($total * 100),
                        'currency' => 'PHP',
                        'redirect' => [
                            'success' => route('checkout.payment.processing'),
                            'failed' => route('checkout.payment.failed'),
                        ]
                    ]
                ]
            ]);

        if (!$response->successful()) {
            Log::error('PayMongo failed: ' . json_encode($response->json()));
            return response()->json(['error' => 'PayMongo failed'], 500);
        }

        $sourceId = $response->json()['data']['id'];
        session(['paymongo_source_id' => $sourceId]);

        return response()->json([
            'checkout_url' => $response->json()['data']['attributes']['redirect']['checkout_url']
        ]);

    } catch (\Throwable $e) {
        Log::error('PayMongo Exception: ' . $e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
}

    // ==============================
    // PROCESSING PAGE
    // ==============================
    public function processing()
    {
        return view('payment.processing', [
            'sourceId' => session('paymongo_source_id')
        ]);
    }

    // ==============================
    // CHECK STATUS (CORE LOGIC)
    // ==============================
    public function checkPaymentStatus($sourceId)
    {
        try {
            $response = Http::withBasicAuth(config('services.paymongo.secret'), '')
                ->get("https://api.paymongo.com/v1/sources/$sourceId");

            $status = $response->json()['data']['attributes']['status'];

            if ($status === 'chargeable') {

                // STEP 1: CREATE PAYMENT
                Http::withBasicAuth(config('services.paymongo.secret'), '')
                    ->post('https://api.paymongo.com/v1/payments', [
                        'data' => [
                            'attributes' => [
                                'amount' => $response->json()['data']['attributes']['amount'],
                                'currency' => 'PHP',
                                'source' => [
                                    'id' => $sourceId,
                                    'type' => 'source',
                                ],
                            ]
                        ]
                    ]);

                // STEP 2: CREATE ORDER
                $payload = session('paymongo_payload');

                $items = array_map(fn($item) => [
                    'productID' => $item['productID'] ?? $item['id'],
                    'qty' => $item['quantity'],
                    'price' => $item['price'],
                ], $payload['cart']);

                $dto = new CreateOrderDTO([
                    'customerID' => $payload['customerID'],
                    'deliveryAddress' => $payload['deliveryAddress'],
                    'remarks' => $payload['remarks'],
                    'items' => $items,
                    'deliveryDate' => Carbon::parse($payload['deliveryDate'].' '.$payload['deliveryTime']),
                    'deliveryTime' => $payload['deliveryTime'],
                    'payment' => 'gcash',
                ]);

                $this->orderService->createOrder($dto);

                session()->forget(['cart', 'paymongo_payload', 'paymongo_source_id']);

                return response()->json(['status' => 'paid']);
            }

            return response()->json(['status' => $status]);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error']);
        }
    }

    public function paymentSuccess()
    {
        return view('payment.success');
    }

    public function paymentFailed()
    {
        return view('payment.failed');
    }
    
}