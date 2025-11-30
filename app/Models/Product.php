<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'productID';
    public $timestamps = false;

    protected $fillable = [
        'productTypeID',
        'name',
        'imageURL',
        'description',
        'price',
        'stock',
        'isAvailable'
    ];

    // Relationships
    public function servings()
    {
        return $this->hasMany(Serving::class, 'productID', 'productID');
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class, 'productID', 'productID');
    }

      public function type()
    {
        return $this->belongsTo(ProductType::class, 'productTypeID');
    }
}
