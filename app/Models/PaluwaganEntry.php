<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    // Entry → Package
    public function package()
    {
        return $this->belongsTo(PaluwaganPackage::class, 'packageID', 'packageID');
    }

    // Entry → Schedules
    public function schedules()
    {
        return $this->hasMany(PaluwaganSchedule::class, 'paluwaganEntryID', 'paluwaganEntryID');
    }

    // Entry → Payments
    public function payments()
    {
        return $this->hasMany(Payment::class, 'paluwaganEntryID', 'paluwaganEntryID')
                    ->where('contextType', 'paluwagan');
    }
}