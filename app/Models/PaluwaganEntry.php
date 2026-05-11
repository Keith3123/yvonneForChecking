<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class PaluwaganEntry extends Model
{
    protected $table = 'paluwaganentry';
    protected $primaryKey = 'paluwaganEntryID';
    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'packageID',
        'joinDate',
        'status',
        'startMonth',
        'startYear',
        'releasedAt',          // ✅ NEW: when product was actually released
        'releaseRequestedAt',  // ✅ NEW: when customer requested early release
        'releaseNote',         // ✅ NEW: optional note from customer
    ];

    protected $casts = [
        'releasedAt'         => 'datetime',
        'releaseRequestedAt' => 'datetime',
    ];

    // =====================
    // RELATIONSHIPS
    // =====================

    public function package()
    {
        return $this->belongsTo(PaluwaganPackage::class, 'packageID', 'packageID');
    }

    public function schedules()
    {
        return $this->hasMany(PaluwaganSchedule::class, 'paluwaganEntryID', 'paluwaganEntryID');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'paluwaganEntryID', 'paluwaganEntryID')
                    ->where('contextType', 'paluwagan');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    // =====================
    // COMPUTED HELPERS
    // =====================

    /**
     * Has the product been physically released to the customer?
     */
    public function isReleased(): bool
    {
        return !is_null($this->releasedAt);
    }

    /**
     * Is a release pending admin approval?
     */
    public function hasPendingReleaseRequest(): bool
    {
        return $this->status === 'release_requested';
    }

    /**
     * How much has been paid so far?
     */
    public function totalPaid(): float
    {
        return (float) $this->schedules->sum('amountPaid');
    }

    /**
     * How much is still owed (even if released)?
     */
    public function totalRemaining(): float
    {
        return (float) ($this->package->totalAmount ?? 0) - $this->totalPaid();
    }
}