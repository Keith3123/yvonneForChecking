<?php

namespace App\Services;

use App\DTO\CreateOrderDTO;
use App\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    private OrderRepositoryInterface $repo;

    public function __construct(OrderRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function createOrder(CreateOrderDTO $dto)
    {
        Log::info('Starting createOrder');

        return DB::transaction(function () use ($dto) {
            // Create order
            $orderID = $this->repo->create($dto);
            Log::info("Order created: {$orderID}");

            // Add items
            $this->repo->addItems($orderID, $dto->items);
            Log::info("Order items added");

            // Update totalAmount after items
            $this->repo->updateTotalAmount($orderID);

            // Add payment
            $this->repo->addPayment($orderID, $dto);
            Log::info("Payment added");

            return $orderID;
        });
    }

    public function getCustomerOrders(int $customerID)
    {
        return $this->repo->getByCustomer($customerID);
    }
}