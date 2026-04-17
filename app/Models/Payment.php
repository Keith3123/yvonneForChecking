<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\PaluwaganEntry;


class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'paymentID';
    public $timestamps = false;

    protected $fillable = [
        'orderID',
        'paluwaganEntryID',
        'contextType',   // 'order' | 'paluwagan'
        'paymentType',   // 'fullpayment' | 'partial'
        'amount',
        'paymentDate',
        'method',        // 'GCash' | 'COD' | etc
        'proofURL',
        'source_id'     // For PayMongo payments
    ];

    protected $casts = [
        'amount'      => 'float',
        'paymentDate' => 'datetime',
    ];

    // Order relation
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }

    // Paluwagan entry relation
    public function paluwaganEntry()
    {
        return $this->belongsTo(PaluwaganEntry::class, 'paluwaganEntryID')
                    ->where('contextType', 'paluwagan');
    }

    // Check if payment is full or partial
    public function isFullPayment(): bool
    {
        return $this->paymentType === 'fullpayment';
    }

    public function isPartialPayment(): bool
    {
        return $this->paymentType === 'partial';
    }
}