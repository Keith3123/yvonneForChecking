<?php

namespace App\Repositories;

use App\DTO\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(CreateOrderDTO $dto): Order
    {
        $order = Order::create([
            'customerID'      => $dto->customerID,
            'deliveryAddress' => $dto->deliveryAddress,
            'remarks'         => $dto->remarks,
            'totalAmount'     => 0, // will update after items
            'status'          => 'Pending',
            'orderDate'       => now(),
            'paymentStatus'   => 'Pending',
            'deliveryDate'    => $dto->deliveryDate,
            'deliveryTime'    => $dto->deliveryTime,
        ]);

        return $order;
    }

    public function addItems(int $orderID, array $items): void
{
    foreach ($items as $item) {
        $productID = $item['productID'] ?? $item['id'] ?? null;

        if (!is_numeric($productID)) {
            Log::error('Invalid productID for order item', $item);
            continue;
        }

        OrderItem::create([
            'orderID'       => $orderID,
            'productID'     => (int)$productID,
            'price'         => $item['price'],
            'qty'           => $item['qty'],
            'size'          => $item['size'] ?? null,
            'message'       => $item['message'] ?? null,
            'customization' => isset($item['customization']) ? $item['customization'] : null,
            'includes'      => isset($item['includes']) ? $item['includes'] : null,
        ]);
    }
}

    public function updateTotalAmount(int $orderID): void
    {
        $order = Order::find($orderID);
        if (!$order) return;

        $total = $order->orderItems()->sum('subtotal');
        $order->totalAmount = $total;
        $order->save();
    }

    public function addPayment(int $orderID, CreateOrderDTO $dto): void
{
    $total = collect($dto->items)->sum(fn($i) => $i['price'] * $i['qty']);

    $isGcash      = strtoupper($dto->payment) === 'GCASH';
    $isDownpayment = $isGcash && $dto->paymentMode === 'downpayment' && $dto->downpaymentAmount > 0;

    if ($isDownpayment) {
        $downAmt   = round($dto->downpaymentAmount, 2);
        $remaining = round($total - $downAmt, 2);

        // Record 1 — GCash downpayment (will be approved via PayMongo webhook)
        Payment::create([
            'orderID'     => $orderID,
            'contextType' => 'order',
            'paymentType' => 'downpayment',
            'amount'      => $downAmt,
            'paymentDate' => now(),
            'method'      => 'GCASH',
            'status'      => 'pending',
            'meta'        => json_encode(['stage' => 'downpayment']),
        ]);

        // Record 2 — Remaining balance on delivery (COD-style, admin marks paid)
        Payment::create([
            'orderID'     => $orderID,
            'contextType' => 'order',
            'paymentType' => 'remaining_balance',
            'amount'      => $remaining,
            'paymentDate' => now(),
            'method'      => 'COD',
            'status'      => 'pending',
            'meta'        => json_encode(['stage' => 'remaining_balance']),
        ]);

    } else {
        // Full payment — GCash or COD
        Payment::create([
            'orderID'     => $orderID,
            'contextType' => 'order',
            'paymentType' => 'fullpayment',
            'amount'      => $total,
            'paymentDate' => now(),
            'method'      => $isGcash ? 'GCASH' : 'COD',
            'status'      => 'pending',
            'meta'        => json_encode([]),
        ]);
    }
}

    public function getByCustomer(int $customerID): array
    {
        return Order::where('customerID', $customerID)
            ->with(['orderItems.product'])
            ->orderBy('orderDate', 'desc')
            ->get()
            ->toArray();
    }
}   