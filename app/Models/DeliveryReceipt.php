<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeliveryReceipt extends Model
{
    protected $table = 'deliveryreceipt';
    protected $primaryKey = 'drID';
    public $timestamps = false;

    protected $fillable = ['supplierID', 'receivedBy', 'drDate', 'remarks'];

    public function details()
    {
        return $this->hasMany(DeliveryReceiptDetail::class, 'drID', 'drID');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierID', 'supplierID');
    }
}