<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaluwaganMonthAvailability extends Model
{
    protected $table = 'paluwagan_month_availability';
    protected $fillable = ['packageID','year','month','status'];

    // Link to package
    public function package()
    {
        return $this->belongsTo(PaluwaganPackage::class, 'packageID', 'packageID');
    }
}