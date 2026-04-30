<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListOfIngredient extends Model
{
    protected $table = 'listofingredients';
    protected $primaryKey = 'listID';
    public $timestamps = false;

    protected $fillable = [
        'servingID',
        'prepID',
        'ingredientID',
        'qtyUsed',
    ];

    public function serving()
    {
        return $this->belongsTo(Serving::class, 'servingID', 'servingID');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredientID', 'ingredientID');
    }
}