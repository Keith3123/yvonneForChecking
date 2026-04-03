<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $table = 'producttype'; // match your DB table name
    protected $primaryKey = 'productTypeID';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'productType',
        'created_at',
        'updated_by'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'productTypeID', 'productTypeID');
    }

        public function ingredients()
    {
        return $this->hasMany(Ingredient::class, 'listID', 'listID');
    }
}
