<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
     protected $table = 'ingredient';
    protected $primaryKey = 'ingredientID';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'unit',
        'minStockLevel',
        'currentStock'
    ];
    
}
