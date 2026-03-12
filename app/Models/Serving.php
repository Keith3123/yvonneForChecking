<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serving extends Model
{
    protected $table = 'serving';
    protected $primaryKey = 'servingID';
    public $timestamps = false;

    protected $fillable = [
        'productID',
        'size',
        'servingSize',
        'unit',
        'price',
        'qtyNeeded'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }
}