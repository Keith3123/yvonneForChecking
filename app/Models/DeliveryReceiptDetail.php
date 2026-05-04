<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeliveryReceiptDetail extends Model
{
    protected $table = 'deliveryreceiptdetails';
    protected $primaryKey = 'drDetailID';
    public $timestamps = false;

    protected $fillable = ['drID', 'ingredientID', 'qtyDelivered', 'unitCost', 'expiryDate'];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredientID', 'ingredientID');
    }
}