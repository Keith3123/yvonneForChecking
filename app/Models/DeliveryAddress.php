<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAddress extends Model
{
    use HasFactory;

    // Table name (since Laravel expects plural but yours is custom)
    protected $table = 'delivery_addresses';

    // Primary key
    protected $primaryKey = 'id';

    // Allow mass assignment
    protected $fillable = [
        'customerID',
        'label',
        'address',
        'latitude',
        'longitude'
    ];

    // Timestamps (you already have created_at, updated_at)
    public $timestamps = true;

    /**
     * Relationship: Address belongs to a customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}