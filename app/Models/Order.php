<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'orderID';
    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'status',
        'orderDate',
        'totalAmount',
        'remarks',
        'deliveryAddress',
        'paymentStatus',
        'deliveryDate',
        'deliveryTime',
    ];

    protected $casts = [
        'orderDate' => 'datetime',
        'deliveryDate' => 'datetime',
    ];

    // Order Items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'orderID', 'orderID');
    }

    // Payment relation
    public function payment()
    {
        return $this->hasOne(Payment::class, 'orderID', 'orderID');
    }

    // Correct Customer relation
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}