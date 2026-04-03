<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';         // Confirmed your table name is 'user'
    protected $primaryKey = 'userID';  // Primary key
    public $timestamps = true;         // Use timestamps

    protected $fillable = [
        'userID',
        'username',
        'password',
        'roleID',
        'status',
        'created_at',
        'updated_at',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleID', 'roleID');
    }

    public function getAuthPassword()
{
    return $this->password;
}
}