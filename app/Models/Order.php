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
        'payment_reference', // For storing PayMongo source/payment ID
        'payment_provider',  // e.g. 'PayMongo'
    ];

    protected $casts = [
        'orderDate'    => 'datetime',
        'deliveryDate' => 'datetime',
        'totalAmount'  => 'float',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'orderID', 'orderID');
    }

    // Keep the existing hasOne for backwards compat (returns first payment)
public function payment()
{
    return $this->hasOne(Payment::class, 'orderID', 'orderID')
                ->where('contextType', 'order')
                ->withDefault(['method' => 'COD', 'status' => 'pending']);
}

// NEW — all payment records for this order
public function payments()
{
    return $this->hasMany(Payment::class, 'orderID', 'orderID')
                ->where('contextType', 'order');
}

// NEW — helper: is everything fully paid?
public function isFullyPaid(): bool
{
    return $this->payments()
                ->where('status', '!=', 'approved')
                ->doesntExist();
}

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}