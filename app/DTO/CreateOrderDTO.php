<?php

namespace App\DTO;

class CreateOrderDTO
{
    public int $customerID;
    public string $deliveryAddress;
    public string $remarks;
    public array $items; // each item: productID, qty, price
    public ?string $deliveryDate; // Y-m-d H:i:s
    public string $deliveryTime;  // varchar in DB
    public string $payment;        // gcash/cod
    public ?string $paymentProof; // optional file

    public function __construct(array $data)
    {
        $this->customerID      = $data['customerID'];
        $this->deliveryAddress = $data['deliveryAddress'];
        $this->remarks         = $data['remarks'] ?? '';
        $this->items           = $data['items'];
        $this->deliveryDate    = $data['deliveryDate'] ?? null;
        $this->deliveryTime    = $data['deliveryTime'] ?? '';
        $this->payment         = $data['payment'];
        $this->paymentProof    = $data['paymentProof'] ?? '';
    }
}
