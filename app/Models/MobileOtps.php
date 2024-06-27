<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileOtps extends Model
{
    protected $fillable = [
        'mobile_number',
        'username',
        'verification_code',
        'expire_time',
        'verified',
    ];
    protected $table = 'mobile_otps';

}
