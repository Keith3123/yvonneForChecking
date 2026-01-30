<?php

namespace App\Repositories;

use App\DTO\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(CreateOrderDTO $orderDTO): int
    {
        $order = Order::create([
            'customerID'      => $orderDTO->customerID,
            'deliveryAddress' => $orderDTO->deliveryAddress,
            'remarks'         => $orderDTO->remarks,
            'totalAmount'     => collect($orderDTO->items)->sum(fn($i) => $i['price'] * $i['qty']),
            'status'          => 'Pending',
            'orderDate'       => now(),
            'paymentStatus'   => 'Pending',
            'deliveryDate'    => $orderDTO->deliveryDate,
            'deliveryTime'    => $orderDTO->deliveryTime,
        ]);

        return $order->orderID;
    }

    public function addItems(int $orderID, array $items): void
    {
        foreach ($items as $item) {

            // Ensure productID exists and is numeric
            $productID = $item['productID'] ?? $item['id'] ?? null;

            if (!is_numeric($productID)) {
                \Log::error('Invalid productID for order item', $item);
                continue; // skip invalid product
            }

            OrderItem::create([
                'orderID'   => $orderID,
                'productID' => (int)$productID,
                'price'     => $item['price'],
                'qty'       => $item['qty'],
                // subtotal is auto-calculated in DB
            ]);
        }
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
