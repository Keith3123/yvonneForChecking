<?php

namespace App\Services;

use App\DTO\CreateOrderDTO;
use App\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\NewOrderPlaced;

class OrderService
{
    private OrderRepositoryInterface $repo;

    public function __construct(OrderRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

public function createOrder(CreateOrderDTO $dto)
{
    return DB::transaction(function () use ($dto) {

        // ✅ get FULL MODEL
        $order = $this->repo->create($dto);

        // use ID when needed
        $this->repo->addItems($order->orderID, $dto->items);

        $this->repo->updateTotalAmount($order->orderID);

        $this->repo->addPayment($order->orderID, $dto);

        $order->load('customer'); // make sure customer is loaded
        event(new NewOrderPlaced($order));

        return $order;
    });
}

    public function getCustomerOrders(int $customerID)
    {
        return $this->repo->getByCustomer($customerID);
    }
}