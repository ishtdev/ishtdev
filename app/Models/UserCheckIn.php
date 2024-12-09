<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCheckIn extends Model
{
    use HasFactory;
    protected $table = "user_check_in";
    protected $fillables = [
        'user_id',
        'community_id',
        'check_in_count',
        'batch',
        'community_badge_id'
    ];
    
    protected $dates = ['created_at', 'updated_at'];

    // Override the created_at accessor to convert to IST
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('Y-m-d H:i:s');
    }

    // Override the updated_at accessor to convert to IST
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Kolkata')->format('Y-m-d H:i:s');
    }
     
    // public function badge_details()
    // {
    //     return $this->belongsTo(CommunityBadge::class, 'community_badge_id');
    // }

    public function badge_details()
{
    return $this->hasOne(CommunityBadge::class, 'badge_id', 'community_badge_id');
}

    public function community()
    {
        return $this->belongsTo(CommunityDetail::class, 'community_id');
    }
}