<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CommunityDetail;

class GotraChalisa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gotra_chalisa'; // Specify the table name if it's not the plural of the model name
    protected $dates = ['deleted_at'];
    
    protected $fillable = [
        'key',
        'value',
        'community_id',
    ];

    /**
     * Relationship with CommunityDetails
     */
    public function community()
    {
        return $this->belongsTo(CommunityDetail::class, 'community_id', 'id');
    }
}
