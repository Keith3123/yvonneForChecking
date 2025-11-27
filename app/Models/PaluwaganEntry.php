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
        'Status',
    ];


public function package()
{
    return $this->belongsTo(PaluwaganPackage::class, 'packageID');
}

public function schedules()
{
    return $this->hasMany(PaluwaganSchedule::class, 'paluwaganEntryID');
}

}
?>