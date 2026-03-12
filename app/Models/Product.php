<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'productID';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'productTypeID',
        'description',
        'isAvailable',
        'imageURL'
    ];

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'productTypeID', 'productTypeID');
    }

    public function servings()
    {
        return $this->hasMany(\App\Models\Serving::class, 'productID', 'productID');
    }
}