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
    return DB::transaction(function () use ($dto) {
        $orderID = $this->repo->create($dto);
        $this->repo->addItems($orderID, $dto->items);
        $this->repo->addPayment($orderID, $dto);
        return $orderID;
    });
}

    public function getCustomerOrders(int $customerID)
    {
        return $this->repo->getByCustomer($customerID);
    }
}
