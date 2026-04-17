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
        'startYear'
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

public function customer()
{
    return $this->belongsTo(Customer::class, 'customerID', 'customerID');
}
}