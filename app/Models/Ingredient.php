<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;
    
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
