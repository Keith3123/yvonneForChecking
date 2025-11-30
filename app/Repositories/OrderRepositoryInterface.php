<?php

namespace App\Repositories;

use App\Models\Order;
use App\DTO\CreateOrderDTO;


interface OrderRepositoryInterface
{
    public function create(CreateOrderDTO $orderDTO): int;
    public function addItems(int $orderID, array $items): void;
    public function addPayment(int $orderID, CreateOrderDTO $orderDTO): void;
    public function getByCustomer(int $customerID);
    
}
