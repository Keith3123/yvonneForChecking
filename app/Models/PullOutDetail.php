<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PullOutDetail extends Model
{
    protected $table = 'pulloutdetails';
    protected $primaryKey = 'pullOutDetailsID';
    public $timestamps = false;

    protected $fillable = ['pullOutID', 'ingredientID', 'qtyPulled'];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredientID', 'ingredientID');
    }
}