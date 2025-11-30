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


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'orderID', 'orderID');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'orderID', 'orderID');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
