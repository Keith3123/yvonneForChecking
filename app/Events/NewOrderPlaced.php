<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [new Channel('admin-orders')];
    }

    public function broadcastAs(): string
    {
        return 'new.order';
    }

    public function broadcastWith(): array
    {
        return [
            'orderID'      => $this->order->orderID,
            'customerName' => optional($this->order->customer)->firstName
                            . ' '
                            . optional($this->order->customer)->lastName,
            'totalAmount'  => number_format($this->order->totalAmount, 2),
            'time'         => $this->order->orderDate?->format('h:i A') ?? now()->format('h:i A'),
        ];
    }
}