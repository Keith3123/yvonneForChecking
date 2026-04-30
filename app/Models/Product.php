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
        'promo',
        'imageURL',
    ];

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'productTypeID', 'productTypeID');
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'productTypeID', 'productTypeID');
    }

    public function servings()
    {
        return $this->hasMany(Serving::class, 'productID', 'productID');
    }

    public function category()
    {
        return $this->belongsTo(ProductType::class, 'productTypeID', 'productTypeID');
    }
}