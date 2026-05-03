<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';
    protected $primaryKey = 'customerID';
    public $timestamps = true;

    protected $fillable = [
        'firstName',
        'lastName',
        'mi',
        'phone',
        'phone_verified_at',
        'phone_otp',
        'phone_otp_expires_at',
        'email',
        'email_verified_at',
        'email_otp',
        'email_otp_expires_at',
        'address',
        'username',
        'password',
        'isActive',
    ];

    protected $hidden = ['password'];

    // ✅ ADD THIS (VERY IMPORTANT)
    public function orders()
    {
        return $this->hasMany(Order::class, 'customerID', 'customerID');
    }
}