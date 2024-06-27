<?php
// app/Models/CommunityDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityDetail extends Model
{

    use HasFactory;
    protected $table = 'community_detail';
    public $primaryKey = 'id';

    protected $fillable = [
        'profile_id',
        'community_profile_id',
        'created_profile_id',
        'community_image',
        'community_image_background',
        'status',
        'rejection_reason',
        'name_of_community',
        'short_description',
        'long_description',
        'main_festival_community',
        'upload_qr',
        'upload_pdf',
        'upload_video',
        'upload_licence01',
        'upload_licence02',
        'community_lord_name',
        'schedual_visit',
        'location_of_community',
        'distance_from_main_city',
        'distance_from_airpot',
        'make_community_private',
        'latitude',
        'longitude',
        'location_address',
        'website_link',
        'city'
    ];

    protected $dates = ['delete_at'];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function communityArti()
    {
        return $this->hasOne(CommunityArti::class, 'community_detail_id');
    }
    public function communityDetail()
    {
        return $this->hasMany(CommunityDetail::class, 'profile_id');
    }
     public function addresses()
    {
        return $this->hasMany(Address::class, 'profile_id');
    }
}
