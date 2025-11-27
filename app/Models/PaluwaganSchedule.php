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
        'isPaid'
    ];
   
public function entry() 
{
    return $this->belongsTo(PaluwaganEntry::class, 'paluwaganEntryID');
}
}
?>