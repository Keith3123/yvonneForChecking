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
    'contextType',
    'paymentType',
    'amount',
    'paymentDate',
    'method',
    'scheduleID',
    'checkout_session_id',
    'checkout_url',            // ✅ NEW
    'reference_number',        // ✅ NEW
    'paymongo_source_id',      // ✅ NEW
    'status',
    'paymongo_payment_id',
    'meta',
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