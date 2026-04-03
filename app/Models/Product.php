<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'productID';
    public $timestamps = false;

    protected $fillable = [
        'name','productTypeID','description','isAvailable','promo','imageURL','flavor','icing_color','package_includes'
    ];

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'productTypeID', 'productTypeID');
    }

    public function servings()
    {
        return $this->hasMany(\App\Models\Serving::class, 'productID');
    }
}