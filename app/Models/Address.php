<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'address';

    protected $fillable = [
        'profile_id',
        'city',
        'state',
        'postal_code',
        'country',
        'street'
    ];
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
    public function communityDetail()
    {
        return $this->belongsTo(CommunityDetail::class, 'profile_id');
    }

}


