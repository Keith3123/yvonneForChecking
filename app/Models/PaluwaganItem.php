<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaluwaganItem extends Model
{
    protected $table      = 'paluwaganitems';
    protected $primaryKey = 'itemID';

    protected $fillable = ['name', 'category', 'isActive'];
}