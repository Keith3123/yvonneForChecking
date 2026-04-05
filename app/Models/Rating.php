<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = 'ratings'; // optional if default

    protected $primaryKey = 'id'; // default, can omit

    protected $fillable = [
        'order_id',
        'rating',
        'comment',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'orderID');
    }
}