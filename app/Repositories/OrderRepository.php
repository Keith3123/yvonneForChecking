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
            OrderItem::create([
                'orderID'   => $orderID,
                'productID' => $item['productID'],
                'price'     => $item['price'],
                'qty'       => $item['qty'],
                'subtotal'  => $item['price'] * $item['qty'],
            ]);
        }
    }

    public function addPayment(int $orderID, CreateOrderDTO $dto): void
    {
        Payment::create([
            'orderID'      => $orderID,
            'paluwaganEntryID' => null,
            'contextType'  => 'order', 
            'paymentType'  => 'fullpayment', 
            'amount'       => collect($dto->items)->sum(fn($i) => $i['price'] * $i['qty']),
            'paymentDate' => now(),
            'method'       => $dto->payment === 'GCASH' ? 'GCash' : 'COD',
            'proofURL'     => $dto->paymentProof,
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
