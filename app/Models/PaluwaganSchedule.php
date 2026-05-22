<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaluwaganSchedule extends Model
{
    protected $table = 'paluwaganschedule';
    protected $primaryKey = 'scheduleID';
    public $timestamps = false;

    protected $fillable = [
        'paluwaganEntryID',
        'dueDate',
        'status',
        'amountDue',
        'amountPaid',
        'penaltyAmount',   
        'gracePeriodEnd',
    ];

    protected $casts = [
    'dueDate'        => 'date',
    'gracePeriodEnd' => 'date',
];

/**
 * Total amount customer must pay including penalty.
 */
public function totalDue(): float
{
    return (float)$this->amountDue + (float)$this->penaltyAmount;
}

/**
 * Is this schedule currently in grace period?
 */
public function inGracePeriod(): bool
{
    if (!$this->gracePeriodEnd) return false;
    return now()->lessThanOrEqualTo($this->gracePeriodEnd);
}

/**
 * Is this overdue beyond grace period?
 */
public function isPastGrace(): bool
{
    if (!$this->gracePeriodEnd) return false;
    return now()->greaterThan($this->gracePeriodEnd);
}

    // Link to payment (single payment per schedule)
    public function payment()
    {
        return $this->hasOne(Payment::class, 'scheduleID', 'scheduleID');
    }

    // Schedule → Entry
    public function entry()
    {
        return $this->belongsTo(PaluwaganEntry::class, 'paluwaganEntryID', 'paluwaganEntryID');
    }

    // Schedule → Package through entry
    public function package()
    {
        return $this->belongsTo(PaluwaganPackage::class, 'paluwaganPackageID', 'packageID');
    }
}