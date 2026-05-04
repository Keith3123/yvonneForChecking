<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PullOut extends Model
{
    protected $table = 'pullout';
    protected $primaryKey = 'pullOutID';
    public $timestamps = false;

    protected $fillable = ['prepID', 'pullOutBy', 'pullDate', 'pullType', 'remarks'];

    public function details()
    {
        return $this->hasMany(PullOutDetail::class, 'pullOutID', 'pullOutID');
    }
}