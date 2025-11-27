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
        'monthlyPayment'
    ];

    public function entries()
    {
        return $this->hasMany(PaluwaganEntry::class, 'packageID');
    }
}
