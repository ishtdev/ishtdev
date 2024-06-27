<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;

    protected $table = 'user_detail';

    protected $fillable = [
        'profile_id',
        'full_name',
        'email',
        'dob',
        'religion',
        'varna',
        'gotra',
        'ishtdev',
        'kul_devta_devi',
        'profile_picture',
        'bio',
        'poojatype_online',
        'poojatype_offline',
        'speciality_pooja',
        'pravara',
        'ved',
        'upved',
        'mukha',
        'charanas',
        'kyc_details_doc01',
        'kyc_details_doc02',
        'become_pandit',
        'make_profile_private',
        'verified',
        'doc_name',
        'doc_front',
        'doc_back',
        'verification_status',
        'invalidate_reason',
        'business_city',
        'business_state',
        'business_pincode',
        'is_business_profile',
        'business_type',
        'business_name',
        'business_doc',
        'gst_number',
        'business_address',
        'business_invalidate_reason',
        'business_verification_status',
        'register_business_name'
    ];
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'profile_id','profile_id');
    }
}


