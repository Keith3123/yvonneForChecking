<?php

namespace App\Services;

use App\DTO\CreateOrderDTO;
use App\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private OrderRepositoryInterface $repo;

    public function __construct(OrderRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function createOrder(CreateOrderDTO $dto)
{
    \Log::info('Starting createOrder');

    try {
        $orderID = $this->repo->create($dto);
        \Log::info("Order created: {$orderID}");
    } catch (\Exception $e) {
        \Log::error("Failed to create order: " . $e->getMessage());
        throw $e;
    }

    try {
        $this->repo->addItems($orderID, $dto->items);
        \Log::info("Order items added");
    } catch (\Exception $e) {
        \Log::error("Failed to add order items: " . $e->getMessage());
        throw $e;
    }

    try {
        $this->repo->addPayment($orderID, $dto);
        \Log::info("Payment added");
    } catch (\Exception $e) {
        \Log::error("Failed to add payment: " . $e->getMessage());
        throw $e;
    }

    return $orderID;
}


    public function getCustomerOrders(int $customerID)
    {
        return $this->repo->getByCustomer($customerID);
    }
}
