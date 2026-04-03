<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaluwaganPackage extends Model
{
    protected $table = 'paluwaganpackage';
    protected $primaryKey = 'packageID';
    public $timestamps = false;

    protected $fillable = [
    'packageName',
    'description',
    'totalAmount',
    'durationMonths',
    'image',
    'monthlyPayment',
];

    // A package has many entries (customers who joined this package)
    public function entries()
    {
        return $this->hasMany(PaluwaganEntry::class, 'packageID', 'packageID');
    }

    // A package has many schedules through entries
    public function schedules()
    {
        return $this->hasManyThrough(
            PaluwaganSchedule::class,
            PaluwaganEntry::class,
            'packageID',       // Foreign key on entries table
            'paluwaganEntryID', // Foreign key on schedules table
            'packageID',       // Local key on package table
            'paluwaganEntryID' // Local key on entries table
        );
    }
}