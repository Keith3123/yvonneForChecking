<?php

namespace App\DTO;

use Carbon\Carbon;

class CreateOrderDTO
{
    public int $customerID;
    public string $deliveryAddress;
    public string $remarks;
    public array $items;
    public Carbon $deliveryDate;
    public string $deliveryTime;
    public string $payment;
    public ?string $paymentProof;
    public string $paymentMode;       // 'full' | 'downpayment'
    public float $downpaymentAmount;  // 0 if full payment

    public function __construct(array $data)
    {
        $this->customerID       = $data['customerID'];
        $this->deliveryAddress  = $data['deliveryAddress'];
        $this->remarks          = $data['remarks'] ?? '';
        $this->items            = $data['items'];
        $this->deliveryDate     = $data['deliveryDate'];
        $this->deliveryTime     = $data['deliveryTime'];
        $this->payment          = strtoupper($data['payment']);
        $this->paymentProof     = $data['paymentProof'] ?? null;
        $this->paymentMode      = $data['paymentMode'] ?? 'full';
        $this->downpaymentAmount = (float)($data['downpaymentAmount'] ?? 0);
    }
}