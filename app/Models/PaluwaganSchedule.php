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
        'amountPaid'
    ];

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