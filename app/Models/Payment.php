<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'paymentID';
    public $timestamps = false;

    protected $fillable = [
        'orderID',
        'paluwaganEntryID',
        'contextType',
        'paymentType',
        'amount',
        'paymentDate',
        'method',
        'proofURL',
    ];

    public function order()
{
    return $this->belongsTo(Order::class, 'orderID', 'orderID');
}



}
