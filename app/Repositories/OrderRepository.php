<?php

namespace App\Repositories;

use App\DTO\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(CreateOrderDTO $dto): int
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

        return $order->orderID;
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
                'orderID'   => $orderID,
                'productID' => (int)$productID,
                'price'     => $item['price'],
                'qty'       => $item['qty'],
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
        Payment::create([
            'orderID'          => $orderID,
            'paluwaganEntryID' => null,
            'contextType'      => 'order',
            'paymentType'      => 'fullpayment',
            'amount'           => collect($dto->items)->sum(fn($i) => $i['price'] * $i['qty']),
            'paymentDate'      => now(),
            'method'           => strtolower($dto->payment) === 'gcash' ? 'GCash' : 'COD',
            'proofURL'         => $dto->paymentProof ?? '',
        ]);
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