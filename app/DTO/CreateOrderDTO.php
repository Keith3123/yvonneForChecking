<?php

namespace App\DTO;

class CreateOrderDTO
{
    public int $customerID;
    public string $deliveryAddress;
    public string $remarks;
    public array $items; 
    public ?string $deliveryDate; 
    public string $deliveryTime;  
    public string $payment;        
    public ?string $paymentProof; 

    public function __construct(array $data)
    {
        $this->customerID      = $data['customerID'];
        $this->deliveryAddress = $data['deliveryAddress'];
        $this->remarks         = $data['remarks'] ?? '';
        $this->items           = $data['items'];
        $this->deliveryDate    = $data['deliveryDate'] ?? null;
        $this->deliveryTime    = $data['deliveryTime'] ?? '';
        $this->payment         = $data['payment'];
        $this->paymentProof    = $data['paymentProof'] ?? null;
    }
}
