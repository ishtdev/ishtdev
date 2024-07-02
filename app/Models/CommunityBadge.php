<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityBadge extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'community_badge';
    protected $fillable = [
        'badge_id',
        'title',
        'community_id',
        'check_in_count',
        'deleted_at'
    ];
     public function badge_type()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }
}
