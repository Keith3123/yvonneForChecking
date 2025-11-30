<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'orderitem';
    protected $primaryKey = 'orderItemID';
    public $timestamps = false;

    protected $fillable = [
        'orderID',
        'productID',
        'price',
        'qty',
        'subtotal'
    ];
    
    public function product()
{
    return $this->belongsTo(Product::class, 'productID', 'id');
}



}
