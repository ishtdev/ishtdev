<?php

namespace App\Models;

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
    public function badge_details()
    {
        return $this->belongsTo(CommunityBadge::class, 'community_badge_id');
    }
    public function community()
    {
        return $this->belongsTo(CommunityDetail::class, 'community_id');
    }
}
